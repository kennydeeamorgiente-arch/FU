<?php
namespace App\Models;
use CodeIgniter\Model;
use Exception;

class EventsModel extends Model
{
  protected $table = 'events';
  protected $primaryKey = 'event_id';
  protected $allowedFields = [
    'org_id',
    'status_id',
    'event_name',
    'event_desc',
    'event_start_date',
    'event_end_date',
    'event_start_time',
    'event_end_time',
    'event_purpose',
    'event_uni_objectives',
    'number_of_participants',
    'has_invited_speaker',
    'name_of_invited_resource_speaker',
    'invited_speaker_description',
    'with_collaborators',
    'date_submitted',
    'event_is_in_campus',
    'event_venue',
    'event_budget',
    'source_of_funds',
    'current_access_id',
    'highest_access_level',
    'activity_initiator',
    'semester_academic_year',
    'organization_name',
    'nature_activity',
    'type_activity'
  ];

  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = true;
  // Dates
  protected $useTimestamps = true;

  protected $dateFormat = 'datetime';
  protected $createdField = 'created_at';
  protected $updatedField = '';
  protected $deletedField = '';

  // Validation
  protected $validationRules = [];
  protected $validationMessages = [];
  protected $skipValidation = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;
  protected $beforeInsert = [];
  protected $afterInsert = [];
  protected $beforeUpdate = [];
  protected $afterUpdate = [];
  protected $beforeFind = [];
  protected $afterFind = [];
  protected $beforeDelete = [];
  protected $afterDelete = [];


  public function getAllEvents()
  {
    $builder = $this->select("
      events.*,
      organization.Org_Name as org_name,
      events_status.name as status_name,
      access_level.access_name as access_name,
      events.highest_access_level
    ");
    $builder->join('organization', "organization.org_id = events.org_id", "left");
    $builder->join('access_level', "access_level.access_id = events.current_access_id", "left");
    $builder->join('events_status', "events_status.status_id = events.status_id", "left");
    $builder->orderBy('events.event_start_date', 'ASC');
    $builder->orderBy('events.current_access_id', 'ASC');
    $query = $builder->get();
    return $query->getResult();
  }

  public function getEventById($id)
  {
    $event = $this
      ->select('events.*, organization.org_name, events_status.name as status_name, access_level.access_name as access_name')
      ->join('organization', 'organization.org_id = events.org_id', 'left')
      ->join('access_level', 'access_level.access_id = events.current_access_id', 'left')
      ->join('events_status', 'events_status.status_id = events.status_id', 'left')
      ->where('events.event_id', $id)
      ->first();

    // Ensure highest_access_level is set (default to 5 if not set)
    if ($event && (!isset($event['highest_access_level']) || $event['highest_access_level'] == null || $event['highest_access_level'] == 0)) {
      $event['highest_access_level'] = 5; // Default: Student(0), Adviser(1), SAO(2), Dean(3), VPAA(4), President(5)
    }

    // Date normalization - convert invalid dates
    if ($event && isset($event['event_start_date'])) {
      if ($event['event_start_date'] == '0000-00-00' || $event['event_start_date'] == '0000-00-00 00:00:00') {
        $event['event_start_date'] = '';
      } else {
        // Extract date part from datetime string
        $event['event_start_date'] = date('Y-m-d', strtotime($event['event_start_date']));
      }
    }

    if ($event && isset($event['event_end_date'])) {
      if ($event['event_end_date'] == '0000-00-00' || $event['event_end_date'] == '0000-00-00 00:00:00') {
        $event['event_end_date'] = '';
      } else {
        $event['event_end_date'] = date('Y-m-d', strtotime($event['event_end_date']));
      }
    }

    return $event;
  }
  public function getEventsByOrgId($org_id)
  {
    return $this
      ->select('events.*, organization.org_name, events_status.name as status_name, access_level.access_name as access_name')
      ->join('organization', 'organization.org_id = events.org_id', 'left')
      ->join('access_level', 'access_level.access_id = events.current_access_id', 'left')
      ->join('events_status', 'events_status.status_id = events.status_id', 'left')
      ->where('events.org_id', $org_id)
      ->findAll();
  }

  public function getAwaitingDocumentationByOrgId($orgId)
  {
    return $this
      ->select('events.event_id, events.event_name, events.event_start_date, events.event_end_date, events.event_venue, events.event_budget, events_status.name as status_name')
      ->join('events_status', 'events_status.status_id = events.status_id', 'left')
      ->where('events.org_id', $orgId)
      ->where('events.status_id', 3)
      ->orderBy('events.event_end_date', 'DESC')
      ->findAll();
  }

  public function getEventByOrgAndStatus($eventId, $orgId, $statusId)
  {
    return $this
      ->where('event_id', (int) $eventId)
      ->where('org_id', (int) $orgId)
      ->where('status_id', (int) $statusId)
      ->first();
  }

  public function getEventsByOrgIdAndDateRange($org_id, $startDate, $endDate)
  {
    // Filter events where event_start_date falls within the selected date range
    // Convert dates to datetime format for comparison (add time to end date to include the full day)
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';

    return $this
      ->select('events.*, organization.org_name, events_status.name as status_name, access_level.access_name as access_name')
      ->join('organization', 'organization.org_id = events.org_id', 'left')
      ->join('access_level', 'access_level.access_id = events.current_access_id', 'left')
      ->join('events_status', 'events_status.status_id = events.status_id', 'left')
      ->where('events.org_id', $org_id)
      ->where('events.event_start_date >=', $startDateTime)
      ->where('events.event_start_date <=', $endDateTime)
      ->orderBy('events.event_start_date', 'ASC')
      ->findAll();
  }

  public function getEventsByDates($startDate, $endDate)
  {
    $builder = $this->select("
          events.*,
          organization.Org_Name as org_name,
          events_status.name as status_name,
          access_level.access_name as access_name,
          events.highest_access_level
      ");
    $builder->join('organization', "organization.org_id = events.org_id", "left");
    $builder->join('access_level', "access_level.access_id = events.current_access_id", "left");
    $builder->join('events_status', "events_status.status_id = events.status_id", "left");

    $builder->where('events.event_start_date >=', $startDate);
    $builder->where('events.event_end_date <=', $endDate);
    $builder->orderBy('events.event_start_date', 'ASC');

    $query = $builder->get();

    return $query->getResultArray();
  }

  public function insertEvent($data)
  {
    return $this->insert($data);
  }
  // public function updateEventStatus($id, $access_id){
  //   return $this
  //   ->select('')
  // }
  public function updateEvent($id, $data)
  {
    return $this->update($id, $data);
  }
  public function deleteByEventId($eventId)
  {
    return $this->where("event_id", $eventId)->delete();
  }
  public function countAllEvents()
  {
    return $this->countAllResults();
  }

  /**
   * Get events filtered by access level
   * Only returns events where current_access_id matches the user's access_id
   * This ensures hierarchical approval workflow:
   * - access_level 1 only sees newly submitted events (current_access_id = 1, status_id = 1)
   * - access_level 2 only sees events approved by level 1 (current_access_id = 2, status_id = 2)
   * - access_level 3 only sees events approved by level 2 (current_access_id = 3, status_id = 2)
   * - And so on...
   * 
   * When a level approves an event:
   * - current_access_id increases by 1 (moves to next level)
   * - status_id changes to 2 (In-Progress) to indicate the event is progressing through approval
   * - The next level can then see and approve the event
   */
  public function getEventsByAccessLevel($accessId)
  {
    $builder = $this->select("
      events.*,
      organization.Org_Name as org_name,
      events_status.name as status_name,
      access_level.access_name as access_name,
      events.highest_access_level
    ");
    $builder->join('organization', "organization.org_id = events.org_id", "left");
    $builder->join('access_level', "access_level.access_id = events.current_access_id", "left");
    $builder->join('events_status', "events_status.status_id = events.status_id", "left");
    // Filter by current_access_id: each level only sees events pending their approval
    $builder->where('events.current_access_id', $accessId);
    // Include both pending (1) and in-progress (2) events
    // status_id = 1: Newly submitted events
    // status_id = 2: Events approved by previous level(s), now in progress through approval workflow
    $builder->whereIn('events.status_id', [1, 2]);
    $builder->orderBy('events.event_start_date', 'ASC');
    $builder->orderBy('events.created_at', 'DESC');
    $query = $builder->get();
    return $query->getResult();
  }

  /**
   * Get events filtered by access level AND organization
   * Used for advisers (access_id = 1) who should only see events from their organization
   */
  public function getEventsByAccessLevelAndOrg($accessId, $orgId)
  {
    $builder = $this->select("
      events.*,
      organization.Org_Name as org_name,
      events_status.name as status_name,
      access_level.access_name as access_name,
      events.highest_access_level
    ");
    $builder->join('organization', "organization.org_id = events.org_id", "left");
    $builder->join('access_level', "access_level.access_id = events.current_access_id", "left");
    $builder->join('events_status', "events_status.status_id = events.status_id", "left");
    // Filter by current_access_id: each level only sees events pending their approval
    $builder->where('events.current_access_id', $accessId);
    // Filter by organization: advisers only see events from their organization
    $builder->where('events.org_id', $orgId);
    // Include both pending (1) and in-progress (2) events
    // status_id = 1: Newly submitted events
    // status_id = 2: Events approved by previous level(s), now in progress through approval workflow
    $builder->whereIn('events.status_id', [1, 2]);
    $builder->orderBy('events.event_start_date', 'ASC');
    $builder->orderBy('events.created_at', 'DESC');
    $query = $builder->get();
    return $query->getResult();
  }

  /**
   * Get events filtered by date range AND organization
   * Used for calendar view when user is an adviser
   */
  public function getEventsByDatesAndOrg($startDate, $endDate, $orgId)
  {
    $builder = $this->select("
      events.*,
      organization.Org_Name as org_name,
      events_status.name as status_name,
      access_level.access_name as access_name,
      events.highest_access_level
    ");
    $builder->join('organization', "organization.org_id = events.org_id", "left");
    $builder->join('access_level', "access_level.access_id = events.current_access_id", "left");
    $builder->join('events_status', "events_status.status_id = events.status_id", "left");

    $builder->where('events.event_start_date >=', $startDate);
    $builder->where('events.event_end_date <=', $endDate);
    // Filter by organization: advisers only see events from their organization
    $builder->where('events.org_id', $orgId);
    // For advisers (access_id = 1), filter by current_access_id and status
    // This ensures they only see events pending their approval
    $builder->where('events.current_access_id', 1);
    $builder->whereIn('events.status_id', [1, 2]);
    $builder->orderBy('events.event_start_date', 'ASC');
    $builder->orderBy('events.created_at', 'DESC');

    $query = $builder->get();
    return $query->getResultArray();
  }
}
?>
