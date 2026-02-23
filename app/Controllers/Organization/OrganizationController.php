<?php
namespace App\Controllers\Organization;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use finfo;
use App\Models\OrganizationModel;
class OrganizationController extends BaseController
{

  protected $event;
  protected $event_history;
  protected $event_budget;
  protected $collab;
  protected $budget;
  protected $org;
  protected $uploads;

  public function __construct()
  {
    $this->event = model("EventsModel");
    $this->event_history = model("EventsHistoryModel");
    $this->event_budget = model("BudgetBreakdownModel");
    $this->collab = model("CollaborationModel");
    $this->budget = model("BudgetBreakdownModel");
    $this->org = model("OrganizationModel");
    $this->uploads = model("EventUploadsModel");
  }
  public function login()
  {
    return view('login');
  }
  public function homepage()
  {
    if ($this->request->isAJAX()) {
      return view('/organization/pages/homepage');
    }
    return view('main');
  }
  public function leaderboards()
  {
    if ($this->request->isAJAX()) {
      $session = session();
      $org_id = $session->get('org_id');

      $org = null;
      if ($org_id) {
        $org = $this->org->getOrganizationById($org_id);
      }

      return view('/organization/pages/leaderboards', ['org' => $org]);
    }
    return view('main');
  }
  public function searchOrganizations()
  {
    $organization = new OrganizationModel();

    $searchTerm = $this->request->getGet('term');

    if (empty($searchTerm) || strlen($searchTerm) < 2) {
      return $this->response->setJSON([
        'status' => 'success',
        'data' => []
      ]);
    }

    $results = $organization->searchOrganization($searchTerm);

    return $this->response->setJSON([
      'status' => 'success',
      'data' => $results
    ]);
  }
  public function hostEvent()
  {
    if ($this->request->isAJAX()) {
      return view('/organization/pages/hostEvent');
    }
    return view('main');
  }
  public function editEventForm($eventId)
  {
    $session = session();
    $userId = $session->get('user_id');
    $orgId = $session->get('org_id');

    if (!$userId || !$orgId) {
      return redirect()->to(base_url('organization/login'));
    }

    $event = $this->event->find($eventId);

    if (!$event) {
      throw new PageNotFoundException("Event not found");
    }

    // Verify the event belongs to the user's organization
    if ($event['org_id'] != $orgId) {
      throw new PageNotFoundException("Unauthorized access");
    }

    // Only allow editing if event status is "Returned For Revision" (status_id = 7) or "Rejected" (status_id = 6)
    if ($event['status_id'] != 6 && $event['status_id'] != 7) {
      throw new PageNotFoundException("Event can only be edited when status is 'Returned For Revision' or 'Rejected'");
    }

    // Get related data using existing model properties
    $data = [
      "event" => $event,
      "budget_breakdown" => $this->event_budget->getBudgetById($eventId) ?? [],
      "event_collaborators" => $this->collab->getCollabById($eventId) ?? [],
      "event_uploads" => $this->uploads->getUploadsByEvent($eventId) ?? []
    ];

    if ($this->request->isAJAX()) {
      return view('/organization/pages/hostEvent', $data);
    }
    return view('main', $data);
  }
  public function logEvent()
  {
    if ($this->request->isAJAX()) {
      return view('/organization/pages/logEvent');
    }
    return view('main');
  }
  public function getPendingLogEvents()
  {
    $session = session();
    $userId = $session->get('user_id');
    $orgId = $session->get('org_id');

    if (!$userId || !$orgId) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Unauthorized access.'
      ])->setStatusCode(401);
    }

    $events = $this->event->getAwaitingDocumentationByOrgId($orgId);

    return $this->response->setJSON([
      'status' => 'success',
      'data' => $events ?? []
    ]);
  }
  public function submitLogEvent()
  {
    $session = session();
    $userId = $session->get('user_id');
    $orgId = $session->get('org_id');

    if (!$userId || !$orgId) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Unauthorized access.'
      ])->setStatusCode(401);
    }

    $eventId = (int) $this->request->getPost('event_id');
    $documentationLink = trim((string) $this->request->getPost('documentation_link'));
    $financialReportLink = trim((string) $this->request->getPost('financial_report_link'));
    $additionalNotes = trim((string) $this->request->getPost('additional_notes'));

    if (!$eventId) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Event is required.'
      ])->setStatusCode(400);
    }

    if (empty($documentationLink) || !filter_var($documentationLink, FILTER_VALIDATE_URL)) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'A valid documentation link is required.'
      ])->setStatusCode(400);
    }

    if (empty($financialReportLink) || !filter_var($financialReportLink, FILTER_VALIDATE_URL)) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'A valid financial report link is required.'
      ])->setStatusCode(400);
    }

    $event = $this->event->getEventByOrgAndStatus($eventId, $orgId, 3);
    if (!$event) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Event is not eligible for logging. It must be in Awaiting Documentation status.'
      ])->setStatusCode(404);
    }

    $db = \Config\Database::connect();
    $db->transBegin();

    $documentationSaved = $this->uploads->upsertUploadLink(
      $eventId,
      $orgId,
      'Documentation',
      $documentationLink,
      'Event Documentation'
    );

    $financialSaved = $this->uploads->upsertUploadLink(
      $eventId,
      $orgId,
      'Financial Report',
      $financialReportLink,
      'Financial Report'
    );

    $eventUpdated = $this->event->update($eventId, ['status_id' => 4]);

    $remarks = !empty($additionalNotes)
      ? "Documentation submitted. Notes: {$additionalNotes}"
      : 'Documentation and financial report submitted.';

    $historySaved = (bool) $this->event_history->insert([
      'user_id' => $userId,
      'event_id' => $eventId,
      'remarks' => $remarks,
      'status_id' => 4
    ]);

    if (!$documentationSaved || !$financialSaved || !$eventUpdated || !$historySaved) {
      $db->transRollback();
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Failed to submit event log. Please try again.'
      ])->setStatusCode(500);
    }

    if ($db->transStatus() === false) {
      $db->transRollback();
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Failed to submit event log due to a transaction error.'
      ])->setStatusCode(500);
    }

    $db->transCommit();

    return $this->response->setJSON([
      'status' => 'success',
      'message' => 'Event logged successfully. Status updated to For Verification.'
    ]);
  }
  public function trackEvent()
  {
    if ($this->request->isAJAX()) {
      return view('/organization/pages/trackEvent');
    }
    return view('main');
  }
  public function getEvents()
  {
    $org_id = $this->request->getGet("org_id");
    $start_date = $this->request->getGet("start_date");
    $end_date = $this->request->getGet("end_date");

    if (!$org_id) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Organization ID is required."
      ]);
    }

    $data = [];
    // If date range is provided, filter by both org_id and date range
    if ($start_date && $end_date) {
      $data = $this->event->getEventsByOrgIdAndDateRange($org_id, $start_date, $end_date);
    } else {
      // Otherwise, just get events by org_id
      $data = $this->event->getEventsByOrgId($org_id);
    }

    // Always return success, even if no data is found, but with an empty array
    return $this->response->setJSON([
      "status" => "success",
      "data" => $data
    ]);
  }


  public function searchOrg()
  {
    $search_text = $this->request->getGet("search");
    $search_result = $this->org->searchOrganization($search_text);
    if ($search_result) {
      return $this->response->setJSON([
        "status" => "success",
        "message" => $search_result
      ]);
    } else {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "No similar Organizations found."
      ]);
    }

  }
  public function profile($id)
  {
    if ($this->request->isAJAX()) {
      $org = $this->org->getOrganizationById($id);

      if (!$org) {
        return $this->response->setJSON([
          'status' => 'error',
          'message' => "Organization Not Found"
        ]);
      }
      return view('/organization/pages/profile', ['org' => $org]);
    }
    return view('main');
  }
  public function settings()
  {
    return view('/organization/pages/settings');
  }
  public function createOrganization()
  {
    $organization = new OrganizationModel();

    $org_name = $this->request->getPost('organization-name');
    $org_fb = $this->request->getPost('organization-fb-link');
    $org_adviser = $this->request->getPost('organization-adviser');
    $org_type = $this->request->getPost('organization-type');
    $org_logo = $this->request->getFile('organization-logo');
    $org_num_members = $this->request->getPost('organization-count-members');
    $org_overview = $this->request->getPost('organization-overview');
    $org_drive_link = $this->request->getPost('organization-drive-link');

    if (!$org_logo || !$org_logo->isValid() || $org_logo->hasMoved()) {
      log_message('debug', 'No file uploaded or invalid file.');
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'No file uploaded or invalid file.'
      ]);
    }

    $extension = $org_logo->getExtension();
    $connect_name = str_replace(" ", "_", $org_name);
    $unique_suffix = uniqid();
    $new_name = strtolower("{$connect_name}_{$unique_suffix}.{$extension}");
    $org_logo->move(FCPATH . 'uploads/org-logos', $new_name);
    log_message('debug', 'success');
    $success = $organization->uploadOrganization(
      $org_name,
      $org_fb,
      $org_adviser,
      $org_type,
      $new_name,
      $org_num_members,
      $org_overview,
      $org_drive_link
    );

    return $this->response->setJSON([
      'status' => $success ? 'success' : 'error'
    ]);
  }
  public function updateOrganization()
  {
    $organization = new OrganizationModel();

    $org_name = $this->request->getPost('organization-name');
    $org_fb = $this->request->getPost('organization-fb-link');
    $org_adviser = $this->request->getPost('organization-adviser');
    $org_type = $this->request->getPost('organization-type');
    $org_logo_old = $this->request->getPost('existing-organization-logo');
    $org_logo_new = $this->request->getFile('new-organization-logo');
    $org_num_members = $this->request->getPost('organization-count-members');
    $org_overview = $this->request->getPost('organization-overview');
    $org_id = $this->request->getPost('org-id');
    $org = $organization->where("org_id", $org_id)->first();

    $mapping = [
      'organization-name' => 'Org_Name',
      'organization-fb-link' => 'facebook_link',
      'organization-adviser' => 'Adviser',
      'organization-type' => 'org_type_id',
      'organization-count-members' => 'org_num_members',
      'organization-overview' => 'Description'
    ];

    $changed = false;

    foreach ($mapping as $postField => $dbField) {
      $postValue = $this->request->getPost($postField);
      $dbValue = $org[$dbField];

      if ($postValue != $dbValue) {
        log_message('debug', 'POST VALUE : ' . $postValue . ' & DB VALUE:' . $dbValue);
        $changed = true;
        break;
      }

    }

    $newLogo = $this->request->getFile('new-organization-logo');
    if ($newLogo && $newLogo->isValid() && !$newLogo->hasMoved()) {
      $changed = true;
    }

    if (!$changed) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Please edit at least one field before saving.']);
    }

    if ($org_logo_new && $org_logo_new->isValid() && !$org_logo_new->hasMoved()) {
      $extension = $org_logo_new->getExtension();
      $connect_name = str_replace(" ", "_", $org_name);
      $unique_suffix = uniqid();
      $new_name = strtolower("{$connect_name}_{$unique_suffix}.{$extension}");
      $org_logo = $new_name;
      $org_logo_new->move(FCPATH . 'uploads/org-logos', $new_name);

      if (!empty($org_logo_old) && file_exists(FCPATH . 'uploads/org-logos/' . $org_logo_old)) {
        unlink(FCPATH . 'uploads/org-logos/' . $org_logo_old);
      }
    } else {
      $org_logo = $org_logo_old;
    }

    log_message('debug', 'success');
    $success = $organization->editOrganization(
      $org_id,
      $org_name,
      $org_fb,
      $org_adviser,
      $org_type,
      $org_logo,
      $org_num_members,
      $org_overview
    );

    return $this->response->setJSON([
      'status' => $success ? 'success' : 'error'
    ]);
  }

  public function getOrganizations()
  {
    $organization = new OrganizationModel();

    // Get date range parameters if provided
    $dateRange = null;
    $startDate = $this->request->getGet('start_date');
    $endDate = $this->request->getGet('end_date');

    if ($startDate && $endDate) {
      $dateRange = [
        'start_date' => $startDate,
        'end_date' => $endDate
      ];
    }

    $response = $organization->getAllOrganizations($dateRange);

    // Log for debugging
    if ($response) {
      log_message('debug', 'getOrganizations - Date range: ' . json_encode($dateRange));
      log_message('debug', 'getOrganizations - Response count: ' . count($response));
    }

    if ($response) {
      return $this->response->setJSON([
        'status' => 'success',
        'data' => $response
      ]);
    } else {
      return $this->response->setJSON([
        'status' => 'error'
      ]);
    }
  }

  public function getCurrentRanking()
  {
    $session = session();
    $org_id = $session->get('org_id');

    if (!$org_id) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Organization ID not found'
      ]);
    }

    // Get date range parameters if provided
    $dateRange = null;
    $startDate = $this->request->getGet('start_date');
    $endDate = $this->request->getGet('end_date');

    if ($startDate && $endDate) {
      $dateRange = [
        'start_date' => $startDate,
        'end_date' => $endDate
      ];
    }

    $organization = new OrganizationModel();
    $organizations = $organization->getAllOrganizations($dateRange);

    if ($organizations) {
      // Find the current organization's rank
      $rank = null;
      $totalPoints = 0;

      foreach ($organizations as $index => $org) {
        if (isset($org['org_id']) && $org['org_id'] == $org_id) {
          $rank = $index + 1; // Rank is 1-based
          $totalPoints = $org['total_points'] ?? 0;
          break;
        }
      }

      return $this->response->setJSON([
        'status' => 'success',
        'rank' => $rank,
        'total_points' => $totalPoints
      ]);
    }

    return $this->response->setJSON([
      'status' => 'error',
      'message' => 'Could not retrieve ranking'
    ]);
  }

  public function deleteOrganization()
  {
    $organization = new OrganizationModel();

    $org_id = $this->request->getPost('org-id');

    if ($organization->delete($org_id)) {
      return $this->response->setJSON([
        'status' => 'success',
        'data' => "Organization Successfully Deleted"
      ]);
    }

    return $this->response->setJSON([
      'status' => 'error',
      'message' => 'Failed to delete'
    ]);
  }


}
?>
