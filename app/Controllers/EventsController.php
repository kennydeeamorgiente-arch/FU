<?php

namespace App\Controllers;

use App\Models\CollaborationModel;
use App\Models\EventUploadsModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class EventsController extends BaseController
{
  protected $events;
  protected $event_history;
  protected $event_budget;
  protected $collab;
  protected $activities;
  protected $budget;
  protected $uploads;
  protected $notifications;
  protected $users;
  public function __construct()
  {
    $this->events = model("EventsModel");
    $this->event_history = model("EventsHistoryModel");
    $this->event_budget = model("BudgetBreakdownModel");
    $this->collab = model("CollaborationModel");
    $this->budget = model("BudgetBreakdownModel");
    $this->uploads = model("EventUploadsModel");
    $this->notifications = model("NotificationModel");
    $this->users = model("UserModel");
  }

  /**
   * Helper method to send notifications to users
   * Note: Notifications are now generated dynamically from events table, so this is a no-op
   */
  private function sendNotifications($userIds, $eventId, $message, $type = 'event')
  {
    // Notifications are generated dynamically from events table based on current_access_id and status_id
    // No need to store them - they're generated on-demand when users check notifications
    return true;
  }
  public function viewEvent($id)
  {
    $session = session();
    $userId = $session->get('user_id');
    $userAccessId = $session->get('access_id');

    $pointsModel = model("PointsModel");
    $event = $this->events->getEventById($id);
    $event_history = $this->event_history->getHistoryById($id);

    // Check if current user has already approved this event at their access level
    $userHasApproved = false;
    if ($userId && $userAccessId) {
      foreach ($event_history as $history) {
        // Check if this user approved (status_id = 8) at their access level
        if (
          $history['user_id'] == $userId &&
          $history['status_id'] == 8 &&
          $history['access_id'] == $userAccessId
        ) {
          $userHasApproved = true;
          break;
        }
      }
    }

    $data = [
      "event" => $event,
      "event_history" => $event_history,
      "budget_breakdown" => $this->event_budget->getBudgetById($id),
      "event_collaborators" => $this->collab->getCollabById($id),
      "event_uploads" => $this->uploads->getUploadsByEvent($id),
      "points_system" => $pointsModel->getAllPoints(),
      "user_has_approved" => $userHasApproved
    ];
    return view('admin/pages/activities/events-view.php', $data);
  }
  public function viewEventPartial($id)
  {
    $session = session();
    $userId = $session->get('user_id');
    $userAccessId = $session->get('access_id');
    $orgId = $session->get('org_id');

    // Check if user has already approved this event
    $userHasApproved = $this->event_history->checkUserApproval($id, $userId);

    $data = [
      "event" => $this->events->getEventById($id),
      "event_history" => $this->event_history->getHistoryById($id),
      "budget_breakdown" => $this->event_budget->getBudgetById($id),
      "event_collaborators" => $this->collab->getCollabById($id),
      "event_uploads" => $this->uploads->getUploadsByEvent($id),
      "userId" => $userId,
      "userAccessId" => $userAccessId,
      "user_has_approved" => $userHasApproved
    ];
    return view('admin/pages/activities/events-view-partial.php', $data);
  }


  public function getEvents()
  {
    $session = session();
    $accessId = $session->get('access_id');
    $orgId = $session->get('org_id');
    log_message("debug", "access_id for get events: " . $accessId);
    // If user has an access level (1-5), filter events by their access level
    // This ensures hierarchical approval: level 1 sees new submissions, level 2 sees level 1 approvals, etc.
    if ($accessId && $accessId > 0) {
      // For advisers (access_id = 1), also filter by their organization
      // This ensures advisers only see events from their own organization
      if ($accessId == 1 && $orgId) {
        $data = $this->events->getEventsByAccessLevelAndOrg($accessId, $orgId);
      } elseif ($accessId == 3 || $accessId == 5) {
        $data = $this->events->getAllEvents();
      } else {
        $data = $this->events->getEventsByAccessLevel($accessId);
      }
    } else {
      // For users without access level (students, etc.), return all events
      $data = $this->events->getAllEvents();
    }

    if ($data) {
      return $this->response->setJSON([
        'status' => 'success',
        'data' => $data,
      ]);
    } else {
      return $this->response->setJSON([
        'status' => 'success, access id: ',
        $accessId,
        'data' => [], // Return empty array instead of error
        'message' => 'No Events Found'
      ]);
    }
  }

  public function getEventsByDateRange()
  {
    $session = session();
    $accessId = $session->get('access_id');
    $orgId = $session->get('org_id');

    $startDate = $this->request->getGet('start_date');
    $endDate = $this->request->getGet('end_date');

    if (!$startDate || !$endDate) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Missing start or end date."
      ], 400);
    }

    // For advisers (access_id = 1), filter by their organization
    if ($accessId == 1 && $orgId) {
      $data = $this->events->getEventsByDatesAndOrg($startDate, $endDate, $orgId);
    } else {
      $data = $this->events->getEventsByDates($startDate, $endDate);
    }

    if ($data === false) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Database query failed."
      ], 500);
    }

    return $this->response->setJSON([
      "status" => "success",
      "data" => $data
    ]);
  }

  public function download($filename)
  {
    // Build the full file path
    $filePath = WRITEPATH . "uploads/events/" . $filename;

    // Check if file exists
    if (!file_exists($filePath)) {
      throw PageNotFoundException::forPageNotFound("File not found: " . $filename);
    }

    // // Extract just the filename for download
    // $downloadName = basename($filePath);

    // Return the file for download
    return $this->response->download($filePath, null);
  }
  public function manageEventById($segment, $id)
  {
    $valid = ['view-event', 'edit-event'];

    if (!in_array($segment, $valid)) {
      if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
        return $this->response->setJSON([
          'status' => 'error',
          'message' => 'Invalid segment'
        ])->setStatusCode(404);
      }
      throw new PageNotFoundException();
    }

    // For view-event: NO session checks, NO redirects - just return the view (like admin viewEvent)
    if ($segment == "view-event") {
      // IMPORTANT: For view-event, we NEVER check sessions or redirect
      // This allows viewing events via AJAX without authentication issues

      // Get event data
      $event = $this->events->getEventById($id);

      if (!$event) {
        if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
          return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Event not found'
          ])->setStatusCode(404);
        }
        throw new PageNotFoundException("Event not found");
      }

      // Get event history
      $event_history = $this->event_history->getHistoryById($id) ?? [];

      $data = [
        "event" => $event,
        "event_history" => $event_history,
        "budget_breakdown" => $this->event_budget->getBudgetById($id) ?? [],
        "event_collaborators" => $this->collab->getCollabById($id) ?? [],
        "event_uploads" => $this->uploads->getUploadsByEvent($id) ?? []
      ];

      // Return ONLY the view-event partial view - NO redirects, NO session checks, NO full page layout
      // This is a partial view that will be loaded into a modal
      $viewContent = view('organization/manage-events/view-event', $data);

      // Explicitly set status code to 200 and return content directly
      // This prevents any redirects from happening
      return $this->response
        ->setStatusCode(200)
        ->setBody($viewContent)
        ->setHeader('Content-Type', 'text/html; charset=UTF-8')
        ->setHeader('X-Content-Type-Options', 'nosniff')
        ->noCache();
    }

    // For edit-event: Check session and authorization
    if ($segment == "edit-event") {
      $session = session();
      $userId = $session->get('user_id');
      $orgId = $session->get('org_id');

      // Check if this is an AJAX request
      $isAjax = $this->request->isAJAX() ||
        $this->request->hasHeader('X-Requested-With') ||
        strtolower($this->request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';

      // Get event data
      $event = $this->events->getEventById($id);

      if (!$event) {
        if ($isAjax) {
          return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Event not found'
          ])->setStatusCode(404);
        }
        throw new PageNotFoundException("Event not found");
      }

      // Check authentication for editing
      if (!$userId || !$orgId) {
        if ($isAjax) {
          return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Session expired. Please refresh the page.'
          ])->setStatusCode(401);
        }
        return redirect()->to(base_url('organization/login'));
      }

      // Verify the event belongs to the user's organization
      if ($event['org_id'] != $orgId) {
        if ($isAjax) {
          return $this->response->setJSON([
            'status' => 'error',
            'message' => 'You can only edit events from your organization'
          ])->setStatusCode(403);
        }
        throw new PageNotFoundException("Unauthorized access");
      }

      // Only allow editing if event status is "Returned For Revision" (status_id = 7)
      if (!isset($event['status_id']) || $event['status_id'] != 7) {
        if ($isAjax) {
          return $this->response->setJSON([
            'status' => 'error',
            'message' => "Event can only be edited when status is 'Returned For Revision'"
          ])->setStatusCode(403);
        }
        throw new PageNotFoundException("Event can only be edited when status is 'Returned For Revision'");
      }

      // Get event history
      $event_history = $this->event_history->getHistoryById($id) ?? [];

      $data = [
        "event" => $event,
        "event_history" => $event_history,
        "budget_breakdown" => $this->event_budget->getBudgetById($id) ?? [],
        "event_collaborators" => $this->collab->getCollabById($id) ?? [],
        "event_uploads" => $this->uploads->getUploadsByEvent($id) ?? []
      ];

      return view('/organization/pages/hostEvent', $data);
    }
  }
  public function addEvent()
  {
    $eventStartDate = $this->request->getPost("start-date");
    $startDate = new \DateTime($eventStartDate);

    $month = (int) $startDate->format('n'); // 1–12
    $year = (int) $startDate->format('Y');

    if ($month >= 8 && $month <= 12) {
      $semester = "First Semester";
      $academicYearStart = $year;
      $academicYearEnd = $year + 1;
    } elseif ($month >= 1 && $month <= 5) {
      $semester = "Second Semester";
      $academicYearStart = $year - 1;
      $academicYearEnd = $year;
    } else { // June–July
      $semester = "Summer";
      $academicYearStart = $year - 1;
      $academicYearEnd = $year;
    }

    $semesterAcademicYear = $semester . ", " . $academicYearStart . "-" . $academicYearEnd;


    $eventData = [
      "event_name" => $this->request->getPost("event-name"),
      "org_id" => $this->request->getPost("org"),
      "event_start_date" => $this->request->getPost("start-date"),
      "event_end_date" => $this->request->getPost("end-date"),
      "event_start_time" => $this->request->getPost("event-time-start"),
      "event_end_time" => $this->request->getPost("event-time-end"),
      "event_venue" => $this->request->getPost("event-venue"),
      "event_is_in_campus" => $this->request->getPost("event-venue-bool") == "yes" ? 0 : 1,
      "with_collaborators" => $this->request->getPost("event-collab-bool") == "yes" ? 0 : 1,
      "number_of_participants" => $this->request->getPost("event-participants"),
      "event_desc" => $this->request->getPost("event-description"),
      "event_purpose" => $this->request->getPost("event-purpose"),
      "event_uni_objectives" => $this->request->getPost("event-alignment"),
      "has_invited_speaker" => $this->request->getPost("event-speaker") != "" ? 1 : 0,
      "name_of_invited_resource_speaker" => $this->request->getPost("event-speaker"),
      "invited_speaker_description" => $this->request->getPost("event-speaker-desc") ?? "N/A",
      "event_budget" => $this->request->getPost("estimated-budget"),
      "source_of_funds" => $this->request->getPost("budget-source"),
      "status_id" => 1,
      "current_access_id" => 1,
      "highest_access_level" => ($this->request->getPost("event-venue-bool") == "yes" &&
        strtolower($this->request->getPost("budget-source")) == "department") ? 3 : 5, // Events need approval from all levels: Adviser(1), SAO(2), Dean(3), VPAA(4), President(5)
      "activity_initiator" => $this->request->getPost("activity-initiator"),
      "semester_academic_year" => $semesterAcademicYear,
      "nature_activity" => implode(', ', $this->request->getPost("nature-activity") ?? []),
      "type_activity" => $this->request->getPost("type-activity")
    ];

    $eventId = $this->events->insertEvent($eventData);

    if (!$eventId) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Insert unsuccessful'
      ]);
    }
    $activity_nature = $this->request->getPost("nature-of-activity");
    $activity_type = $this->request->getPost("event-type");
    $orgId = $this->request->getPost("org");

    // Always insert primary org first
    $this->collab->insertCollab([
      "org_id" => $orgId,
      "event_id" => $eventId,
      "is_primary_organizer" => 1,
      "collab_type" => 'internal',
      "external_org_name" => null,
      "description" => ''
    ]);

    if ($this->request->getPost("event-collab-bool") == "yes") {
      $collabs = $this->request->getPost("event-collab");
      $collabDescs = $this->request->getPost("event-collab-desc");
      $collabTypes = $this->request->getPost("event-collab-type"); // 'internal' or 'external'

      if ($collabs && is_array($collabs)) {
        foreach ($collabs as $index => $col) {
          // Trim whitespace and check if not empty
          $col = trim($col);
          if (!empty($col)) {
            $collabData = [
              "event_id" => $eventId,
              "is_primary_organizer" => 0,
              "description" => isset($collabDescs[$index]) ? trim($collabDescs[$index]) : ''
            ];

            // Check if this is internal (org_id) or external (org name)
            if (isset($collabTypes[$index]) && $collabTypes[$index] === 'internal') {
              // For internal, $col should be the org_id
              $collabData['org_id'] = $col;
              $collabData['collab_type'] = 'internal';
              $collabData['external_org_name'] = null;
            } else {
              // For external, $col is the organization name
              $collabData['org_id'] = null;
              $collabData['collab_type'] = 'external';
              $collabData['external_org_name'] = $col;
            }

            $this->collab->insertCollab($collabData);
          }
        }
      }
    }

    $desc = $this->request->getPost("desc");
    $qty = $this->request->getPost("qty");
    $unit = $this->request->getPost("unit");
    $purpose = $this->request->getPost("purpose");
    $unit_price = $this->request->getPost("unit-price");
    $amount = $this->request->getPost("amount");

    if ($desc && is_array($desc)) {
      foreach ($desc as $i => $d) {
        $this->budget->insertBudget([
          "event_id" => $eventId,
          "description" => $d,
          "quantity" => $qty[$i],
          "unit" => $unit[$i],
          "purpose" => $purpose[$i],
          "unit_price" => $unit_price[$i],
          "amount" => $amount[$i],
        ]);
      }
    }

    $uploadModel = new EventUploadsModel();

    $linkFields = [
      "program-paper" => "Program Paper",
      "communication-letter" => "Communication Letter",
      "proposal-paper" => "Proposal",
    ];

    foreach ($linkFields as $field => $type) {
      $driveLink = $this->request->getPost($field);

      if (!empty($driveLink) && filter_var($driveLink, FILTER_VALIDATE_URL)) {
        $uploadModel->insertUpload([
          "event_id" => $eventId,
          "org_id" => $orgId,
          "file_name" => $type,
          "file_path" => $driveLink,
          "file_type" => $type
        ]);
      }
    }

    // --- NOTIFICATION: Notify the Club Adviser of the submitting organization ---
    $event = $this->events->find($eventId);
    if ($event && $orgId) {
      // Get advisers (access_id = 1) for THIS organization only
      $advisers = $this->users->where('access_id', 1)
        ->where('org_id', $orgId)
        ->findAll();

      if (!empty($advisers)) {
        $adviserUserIds = array_column($advisers, 'user_id');
        $eventName = $event['event_name'] ?? 'New Event';
        $message = "New event '{$eventName}' has been submitted and requires your approval.";
        $this->sendNotifications($adviserUserIds, $eventId, $message, 'event_submission');
      }
    }

    return $this->response->setJSON(["status" => "success", "event_id" => $eventId]);
  }
  public function editEvent()
  {
    $session = session();
    $userId = $session->get('user_id');
    $orgId = $session->get('org_id');

    $eventId = $this->request->getPost("event");

    // Validate user is logged in
    if (!$userId || !$orgId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Unauthorized access"
      ])->setStatusCode(401);
    }

    if (!$eventId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event ID is required"
      ])->setStatusCode(400);
    }

    // Get current event to check status and ownership
    $currentEvent = $this->events->find($eventId);

    if (!$currentEvent) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event not found"
      ])->setStatusCode(404);
    }

    // Only allow editing if event status is "Returned For Revision" (status_id = 7)
    if (!isset($currentEvent['status_id']) || $currentEvent['status_id'] != 7) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event can only be edited when status is 'Returned For Revision'"
      ])->setStatusCode(403);
    }

    // Verify the event belongs to the user's organization
    if ($currentEvent['org_id'] != $orgId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "You can only edit events from your organization"
      ])->setStatusCode(403);
    }

    // Only allow editing for rejected (6) or returned for revision (7) events
    if ($currentEvent['status_id'] != 6 && $currentEvent['status_id'] != 7) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "You can only edit events that are rejected or returned for revision"
      ])->setStatusCode(403);
    }

    // Use session org_id instead of form data for security (already set above)

    $eventStartDate = $this->request->getPost("start-date");
    $startDate = new \DateTime($eventStartDate);

    $month = (int) $startDate->format('n'); // 1–12
    $year = (int) $startDate->format('Y');

    if ($month >= 8 && $month <= 12) {
      $semester = "First Semester";
      $academicYearStart = $year;
      $academicYearEnd = $year + 1;
    } elseif ($month >= 1 && $month <= 5) {
      $semester = "Second Semester";
      $academicYearStart = $year - 1;
      $academicYearEnd = $year;
    } else { // June–July
      $semester = "Summer";
      $academicYearStart = $year - 1;
      $academicYearEnd = $year;
    }

    $semesterAcademicYear = $semester . ", " . $academicYearStart . "-" . $academicYearEnd;
    // --- EVENT MAIN DATA ---
    $eventData = [
      "event_name" => $this->request->getPost("event-name"),
      "event_start_date" => $this->request->getPost("start-date"),
      "event_end_date" => $this->request->getPost("end-date"),
      "event_start_time" => $this->request->getPost("event-time-start"),
      "event_end_time" => $this->request->getPost("event-time-end"),
      "event_venue" => $this->request->getPost("event-venue"),
      "event_is_in_campus" => $this->request->getPost("event-venue-bool") == "yes" ? 0 : 1,
      "with_collaborators" => $this->request->getPost("event-collab-bool") == "yes" ? 1 : 0,
      "number_of_participants" => $this->request->getPost("event-participants"),
      "event_desc" => $this->request->getPost("event-description"),
      "event_purpose" => $this->request->getPost("event-purpose"),
      "event_uni_objectives" => $this->request->getPost("event-alignment"),
      "has_invited_speaker" => $this->request->getPost("event-speaker") != "" ? 1 : 0,
      "name_of_invited_resource_speaker" => $this->request->getPost("event-speaker"),
      "invited_speaker_description" => $this->request->getPost("event-speaker-desc") ?? "N/A",
      "event_budget" => $this->request->getPost("estimated-budget"),
      "source_of_funds" => $this->request->getPost("budget-source"),
      "highest_access_level" => ($this->request->getPost("event-venue-bool") == "yes" &&
        strtolower($this->request->getPost("budget-source")) == "department") ? 3 : 5, // Events need approval from all levels: Adviser(1), SAO(2), Dean(3), VPAA(4), President(5)
      "activity_initiator" => $this->request->getPost("activity-initiator"),
      "semester_academic_year" => $semesterAcademicYear,
      "nature_activity" => implode(', ', $this->request->getPost("nature-activity") ?? []),
      "type_activity" => $this->request->getPost("type-activity")
    ];

    // When student resubmits after revision/rejection
    if ($currentEvent && ($currentEvent['status_id'] == 7 || $currentEvent['status_id'] == 6)) {
      // Reset to pending and restart approval sequence
      $eventData['status_id'] = 1; // Pending
      $eventData['current_access_id'] = 1; // Start at Adviser level
    } else {
      // Keep existing status and access_id if not resubmission
      $eventData['status_id'] = $currentEvent['status_id'] ?? 1;
      $eventData['current_access_id'] = $currentEvent['current_access_id'] ?? 1;
    }

    // UPDATE event
    $updated = $this->events->updateEvent($eventId, $eventData);

    if (!$updated) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Update unsuccessful"
      ]);
    }

    // --- COLLABORATORS ---
    // Clear previous collaborators
    $this->collab->deleteByEvent($eventId);

    if ($this->request->getPost("event-collab-bool") == "yes") {
      $collabs = $this->request->getPost("event-collab");
      $collabDescs = $this->request->getPost("event-collab-desc");
      $collabTypes = $this->request->getPost("event-collab-type"); // 'internal' or 'external'

      if ($collabs && is_array($collabs)) {
        foreach ($collabs as $index => $col) {
          // Trim whitespace and check if not empty
          $col = trim($col);
          if (!empty($col)) {
            $collabData = [
              "event_id" => $eventId,
              "is_primary_organizer" => 0,
              "description" => isset($collabDescs[$index]) ? trim($collabDescs[$index]) : ''
            ];

            // Check if this is internal (org_id) or external (org name)
            if (isset($collabTypes[$index]) && $collabTypes[$index] === 'internal') {
              // For internal, $col should be the org_id
              $collabData['org_id'] = $col;
              $collabData['collab_type'] = 'internal';
              $collabData['external_org_name'] = null;
            } else {
              // For external, $col is the organization name
              $collabData['org_id'] = null;
              $collabData['collab_type'] = 'external';
              $collabData['external_org_name'] = $col;
            }

            $this->collab->insertCollab($collabData);
          }
        }
      }
    }

    // --- BUDGET BREAKDOWN ---
    // delete old budget rows
    $this->budget->deleteByEvent($eventId);

    $desc = $this->request->getPost("desc");
    $qty = $this->request->getPost("qty");
    $unit = $this->request->getPost("unit");
    $purpose = $this->request->getPost("purpose");
    $unit_price = $this->request->getPost("unit-price");
    $amount = $this->request->getPost("amount");

    if ($desc && is_array($desc)) {
      foreach ($desc as $i => $d) {
        $this->budget->insertBudget([
          "event_id" => $eventId,
          "description" => $d,
          "quantity" => $qty[$i],
          "unit" => $unit[$i],
          "purpose" => $purpose[$i],
          "unit_price" => $unit_price[$i],
          "amount" => $amount[$i],
        ]);
      }
    }

    // --- GOOGLE DRIVE LINKS ---
    $uploadModel = new EventUploadsModel();

    // Delete existing uploads for this event
    $uploadModel->deleteUploadsByEvent($eventId);

    $linkFields = [
      "program-paper" => "Program Paper",
      "communication-letter" => "Communication Letter",
      "proposal-paper" => "Proposal",
    ];

    foreach ($linkFields as $field => $type) {
      $driveLink = $this->request->getPost($field);

      if (!empty($driveLink) && filter_var($driveLink, FILTER_VALIDATE_URL)) {
        $uploadModel->insertUpload([
          "event_id" => $eventId,
          "org_id" => $orgId,
          "file_name" => $type,
          "file_path" => $driveLink,
          "file_type" => $type
        ]);
      }
    }

    // --- EVENT HISTORY ---
    $add_history = [
      "user_id" => $userId,
      "event_id" => $eventId,
      "remarks" => "Form Updated",
      "status_id" => 1
    ];
    $this->event_history->insert($add_history);

    // --- NOTIFICATION: If event was resubmitted after revision, notify the organization's adviser ---
    // Check if previous current_access_id was 0 (revision state)
    $previousEvent = $this->events->find($eventId);
    // Since we just updated it, check if it's now at access_id 1 and status 1 (resubmission)
    if ($eventData['current_access_id'] == 1 && $eventData['status_id'] == 1) {
      $event = $this->events->find($eventId);
      if ($event && $orgId) {
        // Get advisers (access_id = 1) for THIS organization only
        $advisers = $this->users->where('access_id', 1)
          ->where('org_id', $orgId)
          ->findAll();

        if (!empty($advisers)) {
          $adviserUserIds = array_column($advisers, 'user_id');
          $eventName = $event['event_name'] ?? 'Event';
          $message = "Event '{$eventName}' has been resubmitted and requires your approval.";
          $this->sendNotifications($adviserUserIds, $eventId, $message, 'event_submission');
        }
      }
    }

    return $this->response->setJSON([
      "status" => "success",
      "event_id" => $eventId
    ]);
  }

  public function updateEvent()
  {

    $event_id = $this->request->getPost("event_id");
    $remarks = $this->request->getPost("remarks");
    $status_id = $this->request->getPost("status_id");
    $user_id = $this->request->getPost("user_id");

    // Validate required fields
    if (!$event_id || !$status_id || !$user_id) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Missing required fields: event_id, status_id, or user_id"
      ])->setStatusCode(400);
    }

    // For rejection (6) and revision (7), remarks are required
    if (in_array($status_id, [6, 7]) && (empty($remarks) || trim($remarks) === '')) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Remarks are required for rejection or revision"
      ])->setStatusCode(400);
    }

    // Log the data being saved for debugging
    log_message('debug', "EventsController::updateEvent - Event ID: {$event_id}, Status ID: {$status_id}, User ID: {$user_id}, Remarks: " . substr($remarks, 0, 100));

    $data = [
      "user_id" => $user_id,
      "event_id" => $event_id,
      "remarks" => trim($remarks) ?: '', // Ensure remarks are trimmed and not null
      "status_id" => $status_id
    ];

    $inserted = $this->event_history->insert($data);

    // Log if insertion was successful
    if ($inserted) {
      log_message('debug', "EventsController::updateEvent - History record inserted successfully with ID: {$inserted}");
    } else {
      log_message('error', "EventsController::updateEvent - Failed to insert history record. Errors: " . json_encode($this->event_history->errors()));
    }

    $event = $this->events->find($event_id);
    if (!$event) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event not found"
      ]);
    }

    if ($status_id == 8) { // Approved
      $oldAccessId = $event['current_access_id'];
      $eventName = $event['event_name'] ?? 'Event';
      $orgId = $event['org_id'];
      $eventId = $event_id; // Use consistent variable naming

      // Get highest_access_level from database if not present
      if (!isset($event['highest_access_level']) || $event['highest_access_level'] == null || $event['highest_access_level'] == 0) {
        $event['highest_access_level'] = 5; // Default: Student(0), Adviser(1), SAO(2), Dean(3), VPAA(4), President(5)
      }

      // APPROVAL WORKFLOW SEQUENCE
      // Level 1 (Adviser): current_access_id = 1 approves → increments to 2
      // Level 2 (SAO): current_access_id = 2 approves → increments to 3
      // Level 3 (Dean): current_access_id = 3 approves → increments to 4
      // Level 4 (VPAA): current_access_id = 4 approves → increments to 5
      // Level 5 (President): current_access_id = 5 approves → increments to 6 → FULLY APPROVED

      // Always set status_id to 2 (In-Progress) for events in approval workflow
      $event['status_id'] = 2; // In-Progress

      // Increment current_access_id to move event to NEXT approval level
      if ($event['current_access_id'] >= 1 && $event['current_access_id'] <= $event['highest_access_level']) {
        $oldLevel = $event['current_access_id'];
        $event['current_access_id'] += 1;
        log_message('info', "EventsController: Event {$eventId} - Level {$oldLevel} approved. Moving to Level {$event['current_access_id']}");
      } else if ($event['current_access_id'] < 1) {
        $event['current_access_id'] = 1;
        log_message('info', "EventsController: Event {$eventId} - Starting at Level 1");
      }

      // Access level names for logging
      $accessLevelNames = [
        1 => 'Adviser',
        2 => 'SAO',
        3 => 'Dean',
        4 => 'VPAA',
        5 => 'President'
      ];

      // Check if event has been approved by ALL 5 levels
      // When current_access_id reaches 6, it means level 5 (President) just approved
      if ($event['current_access_id'] > $event['highest_access_level']) {

        // FULLY APPROVED: All 5 levels have completed approval
        $event['status_id'] = 5; // Final status: Fully Approved

        log_message('info', "EventsController: ✅ EVENT {$eventId} FULLY APPROVED BY ALL LEVELS");
        log_message('info', "EventsController: Event approved by: Adviser → SAO → Dean → VPAA → President");

        // Notify organization students that their event is approved by all admin levels
        $studentUsers = $this->users->where('access_id', 0)
          ->where('org_id', $orgId)
          ->findAll();
        $studentUserIds = array_column($studentUsers, 'user_id');

        if (!empty($studentUserIds)) {
          $message = "Your event '{$eventName}' has been successfully approved by all administrative levels (Adviser, SAO, Dean, VPAA, and President). The event is now confirmed and ready to proceed.";
          $this->sendNotifications($studentUserIds, $eventId, $message, 'event_approved');
          log_message('info', "EventsController: Sent approval notification to " . count($studentUserIds) . " organization students");
        } else {
          log_message('warning', "EventsController: No organization students found to notify for org_id: {$orgId}");
        }
      } else {
        // Event is still in approval workflow - waiting for NEXT level to approve
        $nextAccessId = $event['current_access_id'];
        $nextLevelName = $accessLevelNames[$nextAccessId] ?? 'Level ' . $nextAccessId;

        log_message('info', "EventsController: Event {$eventId} awaiting approval from {$nextLevelName} (access_id: {$nextAccessId})");

        // For advisers (access_id = 1), filter by organization
        if ($nextAccessId == 1 && $orgId) {
          $nextLevelUsers = $this->users->where('access_id', 1)
            ->where('org_id', $orgId)
            ->findAll();
          $nextLevelUserIds = array_column($nextLevelUsers, 'user_id');
        } else {
          // For other levels, get all users with that access_id
          $nextLevelUsers = $this->users->where('access_id', $nextAccessId)->findAll();
          $nextLevelUserIds = array_column($nextLevelUsers, 'user_id');
        }

        log_message('info', "EventsController: Found " . count($nextLevelUserIds) . " users at {$nextLevelName} level to receive event {$eventId}");
      }

      // Update event with new current_access_id and status
      // This is CRITICAL: The NotificationModel reads current_access_id to show events to the right approval level
      $updated = $this->events->update($eventId, [
        "current_access_id" => $event['current_access_id'],
        "status_id" => $event['status_id']
      ]);

      if ($updated) {
        log_message('info', "EventsController: Event {$eventId} updated - current_access_id: {$event['current_access_id']}, status_id: {$event['status_id']}");
      } else {
        log_message('error', "EventsController: Failed to update event {$eventId}");
      }
    }

    if ($status_id == 7) { // Return for Revision
      // Reset access_id to 0 to restart approval sequence
      $eventName = $event['event_name'] ?? 'Event';
      $orgId = $event['org_id'];

      $this->events->update($event_id, [
        "current_access_id" => 0,
        "status_id" => $status_id
      ]);

      // Notify students in the organization
      $studentUsers = $this->users->where('access_id', 0)
        ->where('org_id', $orgId)
        ->findAll();
      $studentUserIds = array_column($studentUsers, 'user_id');

      if (!empty($studentUserIds)) {
        $message = "Your event '{$eventName}' has been returned for revision. Please review the remarks and resubmit.";
        $this->sendNotifications($studentUserIds, $event_id, $message, 'event_revision');
      }
    }

    if ($status_id == 6) { // Rejected
      // Reset access_id to 0
      $eventName = $event['event_name'] ?? 'Event';
      $orgId = $event['org_id'];

      log_message('debug', "EventsController: Rejecting event ID: {$event_id}, org_id: {$orgId}");

      $this->events->update($event_id, [
        "current_access_id" => 0,
        "status_id" => $status_id
      ]);

      // Verify the update
      $updatedEvent = $this->events->find($event_id);
      log_message('debug', "EventsController: Event updated - status_id: " . ($updatedEvent['status_id'] ?? 'NULL') . ", current_access_id: " . ($updatedEvent['current_access_id'] ?? 'NULL'));

      // Notify students in the organization
      $studentUsers = $this->users->where('access_id', 0)
        ->where('org_id', $orgId)
        ->findAll();
      $studentUserIds = array_column($studentUsers, 'user_id');

      log_message('debug', "EventsController: Found " . count($studentUserIds) . " organization users to notify for org_id: {$orgId}");

      if (!empty($studentUserIds)) {
        $message = "Your event '{$eventName}' has been rejected. Please review the remarks.";
        $this->sendNotifications($studentUserIds, $event_id, $message, 'event_rejected');
      }
    }

    return $this->response->setJSON([
      "status" => "success",
      "message" => "Event history recorded and event table updated successfully!"
    ]);
  }

  /**
   * Delete an event (only allowed for rejected or revision status)
   */
  public function deleteEvent()
  {
    $session = session();
    $userId = $session->get('user_id');
    $orgId = $session->get('org_id');

    if (!$userId || !$orgId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Unauthorized access"
      ])->setStatusCode(401);
    }

    $eventId = $this->request->getPost('event_id');

    if (!$eventId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event ID is required"
      ])->setStatusCode(400);
    }

    // Get the event
    $event = $this->events->find($eventId);

    if (!$event) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event not found"
      ])->setStatusCode(404);
    }

    // Verify the event belongs to the user's organization
    if ($event['org_id'] != $orgId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "You can only delete events from your organization"
      ])->setStatusCode(403);
    }

    // Only allow deletion for rejected (6) or returned for revision (7) events
    if ($event['status_id'] != 6 && $event['status_id'] != 7) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "You can only delete events that are rejected or returned for revision"
      ])->setStatusCode(403);
    }

    // Delete related data first
    $this->collab->deleteByEvent($eventId);
    $this->budget->deleteByEvent($eventId);

    // Delete event history
    $this->event_history->where('event_id', $eventId)->delete();

    // Delete uploads
    $uploadModel = model("EventUploadsModel");
    $uploadModel->deleteUploadsByEvent($eventId);

    // Delete the event
    $deleted = $this->events->delete($eventId);

    if ($deleted) {
      return $this->response->setJSON([
        "status" => "success",
        "message" => "Event successfully deleted"
      ]);
    } else {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Failed to delete event"
      ])->setStatusCode(500);
    }
  }
  /**
   * Final approval of event and allocation of points
   * This sets the event status to Fully Approved (status_id = 5)
   * and allocates points to the organization via event_points table
   */
  public function approveEvent()
  {
    $session = session();
    $userId = $session->get('user_id');
    $userAccessId = $session->get('access_id');

    if (!$userId || !$userAccessId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Unauthorized access"
      ])->setStatusCode(401);
    }

    $eventId = $this->request->getPost('event_id');
    $pointSystemId = $this->request->getPost('points');

    if (!$eventId || !$pointSystemId) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event ID and points are required"
      ])->setStatusCode(400);
    }

    // Get the event
    $event = $this->events->find($eventId);

    if (!$event) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Event not found"
      ])->setStatusCode(404);
    }

    // Verify user has reached the highest approval level
    if ($userAccessId != $event['highest_access_level']) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Only the final approver can grant points"
      ])->setStatusCode(403);
    }

    // Update event to fully approved status
    $updated = $this->events->update($eventId, [
      "status_id" => 5 // Fully Approved
    ]);

    if (!$updated) {
      return $this->response->setJSON([
        "status" => "error",
        "message" => "Failed to update event status"
      ])->setStatusCode(500);
    }

    // Update or insert event_points record with point_system_id
    $eventPointsModel = model("EventPointsModel");
    $existingEventPoints = $eventPointsModel->getEventPoints($eventId);

    if ($existingEventPoints) {
      // Update existing event_points with point_system_id
      $eventPointsModel->updateEventPoints($eventId, [
        'point_system_id' => $pointSystemId
      ]);
    } else {
      // Insert new event_points record with point_system_id
      $eventPointsModel->insertEventPoints([
        'event_id' => $eventId,
        'point_system_id' => $pointSystemId
      ]);
    }

    // Add to event history
    $this->event_history->insert([
      "user_id" => $userId,
      "event_id" => $eventId,
      "remarks" => "Event fully approved with points allocated (point_system_id: {$pointSystemId})",
      "status_id" => 5
    ]);

    // Notify organization students
    $orgId = $event['org_id'];
    $eventName = $event['event_name'] ?? 'Event';

    $studentUsers = $this->users->where('access_id', 0)
      ->where('org_id', $orgId)
      ->findAll();
    $studentUserIds = array_column($studentUsers, 'user_id');

    if (!empty($studentUserIds)) {
      $message = "Your event '{$eventName}' has been fully approved and points have been allocated!";
      $this->sendNotifications($studentUserIds, $eventId, $message, 'event_approved');
    }

    log_message('info', "EventsController: Event {$eventId} fully approved with point_system_id: {$pointSystemId}");

    return $this->response->setJSON([
      "status" => "success",
      "message" => "Event approved and points allocated successfully"
    ]);
  }
}


?>