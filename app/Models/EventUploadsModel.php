<?php
namespace App\Models;
use CodeIgniter\Model;
use Exception;

class EventUploadsModel extends Model
{
  protected $table = 'event_uploads';
  protected $primaryKey = 'upload_id';
  protected $allowedFields = [
    'upload_id',
    'org_id',
    'event_id',
    'file_name',
    'file_path',
    'file_type',
    'uploaded_at',
  ];

  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = true;
  // Dates
  protected $useTimestamps = true;

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



  public function insertUpload($data)
  {
    return $this->insert($data);
  }

  public function getUploadsByEvent($eventId)
  {
    return $this->where("event_id", $eventId)->findAll();
  }
  public function deleteUploadsByEvent($eventId)
  {
    return $this->where("event_id", $eventId)->delete();
  }

  public function upsertUploadLink($eventId, $orgId, $fileType, $filePath, $fileName = null)
  {
    $existingUpload = $this
      ->where('event_id', (int) $eventId)
      ->where('org_id', (int) $orgId)
      ->where('file_type', $fileType)
      ->first();

    $data = [
      'org_id' => (int) $orgId,
      'event_id' => (int) $eventId,
      'file_name' => $fileName ?: $fileType,
      'file_path' => $filePath,
      'file_type' => $fileType,
      'uploaded_at' => date('Y-m-d H:i:s'),
    ];

    if ($existingUpload) {
      return (bool) $this->update($existingUpload['upload_id'], $data);
    }

    return (bool) $this->insert($data);
  }
}
?>
