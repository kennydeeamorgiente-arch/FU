<?php
namespace App\Models;
use CodeIgniter\Model;
use App\Models\OrganizationTypeModel;
use Exception;

class OrganizationModel extends Model
{
  protected $table = 'organization';
  protected $primaryKey = 'org_id';
  protected $allowedFields = ['Org_Name', 'Description', 'Adviser', 'org_type_id', 'facebook_link', 'logo', 'org_num_members'];
  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = true;
  protected $useSoftDeletes = true;
  // Dates
  protected $useTimestamps = true;

  protected $dateFormat = 'datetime';
  protected $createdField = 'created_at';
  protected $updatedField = '';
  protected $deletedField = 'deleted_at';

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

  public function uploadOrganization($org_name, $org_fb, $org_adviser, $org_type, $org_logo, $org_num_members, $org_overview, $org_drive_link)
  {

    $org_type_model = new OrganizationTypeModel();
    $org_type_info = $org_type_model->getOrgType($org_type);
    $org_type_id = $org_type_info['org_type_id'];

    $data = [
      'Org_Name' => $org_name,
      'Description' => $org_overview,
      'Adviser' => $org_adviser,
      'org_type_id' => $org_type_id,
      'facebook_link' => $org_fb,
      'org_num_members' => $org_num_members,
      'logo' => $org_logo,
      'org_drive_link' => $org_drive_link
    ];

    try {
      return $this->insert($data, false);
    } catch (Exception $e) {
      log_message('error', $e);
      return false;
    }
  }

  public function editOrganization($org_id, $org_name, $org_fb, $org_adviser, $org_type, $org_logo, $org_num_members, $org_overview)
  {
    $org_type_model = new OrganizationTypeModel();
    $org_type_info = $org_type_model->getOrgType($org_type);

    if (!$org_type_info) {
      log_message("error", "Org type not found, ");
      return false;
    }

    $org_type_id = $org_type_info['org_type_id'];

    $data = array_filter([
      'Org_Name' => $org_name,
      'Description' => $org_overview,
      'Adviser' => $org_adviser,
      'org_type_id' => $org_type_id,
      'facebook_link' => $org_fb,
      'org_num_members' => $org_num_members,
      'logo' => $org_logo
    ], fn($v) => $v !== null && $v !== '');

    try {
      return $this->update($org_id, $data);
    } catch (Exception $e) {
      log_message('error', $e);
      return false;
    }
  }

  public function getAllOrganizations($dateRange = null)
  {
    $hasEventPoints = $this->db->tableExists('event_points');
    $hasPointSystem = $this->db->tableExists('point_system');
    $hasPointsTables = $hasEventPoints && $hasPointSystem;

    // Build date filter for events join
    $eventDateFilter = '';
    if ($dateRange && isset($dateRange['start_date']) && isset($dateRange['end_date'])) {
      $startDate = $this->db->escape($dateRange['start_date']);
      $endDate = $this->db->escape($dateRange['end_date']);
      $eventDateFilter = " AND events.event_start_date >= {$startDate} AND events.event_start_date <= {$endDate}";
    }

    $pointsSelect = $hasPointsTables
      ? 'COALESCE(SUM(point_system.points), 0) AS total_points'
      : '0 AS total_points';

    // Build the query with proper date filtering at JOIN level
    // This ensures only events within the date range are joined and counted
    // Use explicit column names with aliases to match JavaScript expectations
    $query = $this->select("
        organization.org_id,
        organization.Org_Name AS org_name,
        organization.Description AS description,
        organization.Adviser AS adviser,
        organization.org_type_id,
        organization.facebook_link,
        organization.logo,
        organization.org_num_members,
        organization.created_at,
        organization.deleted_at,
        organization_type.type AS org_type_name,
        COUNT(DISTINCT events.event_id) AS num_events,
        CASE 
            WHEN organization.deleted_at IS NOT NULL THEN 'deleted'
            WHEN DATEDIFF(CURDATE(), COALESCE(MAX(all_events.event_start_date), '1970-01-01')) > 60 THEN 'inactive'
            ELSE 'active'
        END AS status,
        {$pointsSelect}
    ")
      ->join('organization_type', 'organization_type.org_type_id = organization.org_type_id', 'inner')
      ->join('events', "events.org_id = organization.org_id{$eventDateFilter}", 'left', false)
      ->join('events as all_events', 'all_events.org_id = organization.org_id', 'left')
      ->where('organization.deleted_at IS NULL', null, false)
      ->groupBy('organization.org_id')
      ->orderBy('total_points', 'DESC');

    if ($hasPointsTables) {
      $query->join('event_points', 'event_points.event_id = events.event_id', 'left');
      $query->join('point_system', 'point_system.id = event_points.point_system_id', 'left');
    }

    return $query->findAll();
  }

  public function searchOrganization($search)
  {
    return $this->select("
        organization.org_id,
        organization.org_Name AS org_name,
        organization.description AS description,
        organization.adviser AS adviser,
        organization.org_type_id,
        organization.facebook_link,
        organization.logo,
        organization.org_num_members
    ")
      ->like('Org_Name', $search, 'both')
      ->where('organization.deleted_at IS NULL', null, false)
      ->findAll();
  }



  public function getOrganizationById($id)
  {
    $id = ($id !== null) ? (int) $id : null;
    $hasEventPoints = $this->db->tableExists('event_points');
    $hasPointSystem = $this->db->tableExists('point_system');
    $hasPointsTables = $hasEventPoints && $hasPointSystem;
    $hasOrgDriveLink = $this->db->fieldExists('org_drive_link', 'organization');
    $driveLinkSelect = $hasOrgDriveLink ? "organization.org_drive_link," : "";

    $pointsAggregation = $hasPointsTables ? 'COALESCE(SUM(point_system.points), 0)' : '0';
    $subqueryJoins = $hasPointsTables
      ? "LEFT JOIN event_points ON event_points.event_id = events.event_id
         LEFT JOIN point_system ON point_system.id = event_points.point_system_id"
      : '';

    $simpleBuilderFactory = function () use ($driveLinkSelect) {
      return $this->db->table('organization')
        ->select("
            organization.org_id,
            organization.Org_Name AS org_name,
            organization.Description AS description,
            organization.Adviser AS adviser,
            organization.org_type_id,
            organization.facebook_link,
            organization.logo,
            organization.org_num_members,
            {$driveLinkSelect}
            organization.created_at,
            organization.deleted_at,
            organization_type.type AS org_type_name,
            0 AS num_events,
            'active' AS status,
            0 AS total_points,
            0 AS org_rank
        ")
        ->join('organization_type', 'organization_type.org_type_id = organization.org_type_id', 'inner')
        ->where('organization.deleted_at IS NULL', null, false);
    };

    try {
      $subquery = "
          SELECT 
              organization.org_id,
              {$pointsAggregation} AS total_points,
              DENSE_RANK() OVER (ORDER BY {$pointsAggregation} DESC, organization.org_id ASC) AS org_rank
          FROM organization
          LEFT JOIN events ON events.org_id = organization.org_id
          {$subqueryJoins}
          WHERE organization.deleted_at IS NULL
          GROUP BY organization.org_id
      ";

      $complexBuilder = $this->db->table('organization')
        ->select("
              organization.org_id,
              organization.Org_Name AS org_name,
              organization.Description AS description,
              organization.Adviser AS adviser,
              organization.org_type_id,
              organization.facebook_link,
              organization.logo,
              organization.org_num_members,
              {$driveLinkSelect}
              organization.created_at,
              organization.deleted_at,
              organization_type.type AS org_type_name,
              COUNT(DISTINCT events.event_id) AS num_events,
              CASE 
                  WHEN organization.deleted_at IS NOT NULL THEN 'deleted'
                  WHEN DATEDIFF(CURDATE(), COALESCE(MAX(events.event_start_date), '1970-01-01')) > 60 THEN 'inactive'
                  ELSE 'active'
              END AS status,
              COALESCE(sub.total_points, 0) AS total_points,
              COALESCE(sub.org_rank, 0) AS org_rank
          ")
        ->join('organization_type', 'organization_type.org_type_id = organization.org_type_id', 'inner')
        ->join("($subquery) AS sub", "sub.org_id = organization.org_id", 'left')
        ->join('events', 'events.org_id = organization.org_id', 'left')
        ->where('organization.deleted_at IS NULL', null, false)
        ->groupBy('organization.org_id')
        ->orderBy('num_events', 'ASC');

      if ($id !== null) {
        $result = $complexBuilder
          ->where('organization.org_id', $id)
          ->get()
          ->getRowArray();

        // If no result found, try a simpler query without the complex subquery
        if (!$result) {
          return $simpleBuilderFactory()
            ->where('organization.org_id', $id)
            ->get()
            ->getRowArray();
        }

        return $result;
      }

      return $complexBuilder->get()->getResultArray();
    } catch (\Exception $e) {
      log_message('error', 'Error in getOrganizationById: ' . $e->getMessage());

      // Fallback to simple query if complex query fails
      if ($id !== null) {
        return $simpleBuilderFactory()
          ->where('organization.org_id', $id)
          ->get()
          ->getRowArray();
      }

      return [];
    }
  }


}
?>
