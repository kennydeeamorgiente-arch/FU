<?php
namespace App\Models;
use CodeIgniter\Model;
use Exception;

class EventPointsModel extends Model
{
  protected $table = 'event_points';
  protected $primaryKey = 'event_points_id';
  protected $allowedFields = [
    "event_id",
    "point_system_id",
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

  /**
   * Update event points by event_id
   */
  public function updateEventPoints($eventId, $data)
  {
    return $this->where("event_id", $eventId)->set($data)->update();
  }

  /**
   * Insert new event points record
   */
  public function insertEventPoints($data)
  {
    return $this->insert($data);
  }

  /**
   * Get event points by event_id
   */
  public function getEventPoints($eventId)
  {
    return $this->where("event_id", $eventId)->first();
  }

  /**
   * Delete event points by event_id
   */
  public function deleteByEvent($eventId)
  {
    return $this->where("event_id", $eventId)->delete();
  }
}
?>