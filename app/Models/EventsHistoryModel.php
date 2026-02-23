<?php
namespace App\Models;
use CodeIgniter\Model;
use Exception;

class EventsHistoryModel extends Model
{
  protected $table = 'events_history';
  protected $primaryKey = 'events_history_id';
  protected $allowedFields = ['user_id', 'event_id', 'remarks', 'created_at', 'status_id'];
  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = false;
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


  public function getHistoryById($id)
  {
    // Validate event_id
    if (empty($id) || !is_numeric($id)) {
      log_message('error', "EventsHistoryModel::getHistoryById - Invalid event_id: {$id}");
      return [];
    }

    return $this
      ->select('
        events_history.*,
        events_status.name as status_name,
        access_level.access_id as access_id,
        access_level.access_name as access_name
    ')
      ->join('users', 'users.user_id = events_history.user_id', 'left') // join users to get access_id
      ->join('access_level', 'access_level.access_id = users.access_id', 'left') // get access_name via users
      ->join('events_status', 'events_status.status_id = events_history.status_id', 'left')
      ->where('events_history.event_id', (int)$id) // Ensure event_id is an integer
      ->where('events_history.event_id IS NOT NULL') // Additional safeguard against NULL event_id
      ->orderBy('events_history.created_at', 'ASC') // Order by creation date to show chronological history
      ->findAll();
  }





}
?>