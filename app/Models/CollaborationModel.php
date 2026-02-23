<?php
namespace App\Models;
use CodeIgniter\Model;
use Exception;

class CollaborationModel extends Model
{
  protected $table = 'collaboration';
  protected $primaryKey = 'coll_id';
  protected $allowedFields = [
    'org_id',
    'event_id',
    'is_primary_organizer'
  ];

  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = true;
  // Dates - collaboration table doesn't have created_at column
  protected $useTimestamps = false;

  protected $dateFormat = 'datetime';
  protected $createdField = '';
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



  public function insertCollab($data)
  {
    return $this->insert($data);
  }

  public function getCollabById($id)
  {
    return $this->select('collaboration.*')
      ->where('event_id', $id)
      ->where('is_primary_organizer', 0)
      ->findAll();
  }

  public function deleteByEvent($eventId)
  {
    return $this->where('event_id', $eventId)->delete();
  }

}
?>