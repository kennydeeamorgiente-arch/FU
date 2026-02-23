<?php
namespace App\Models;
use CodeIgniter\Model;

class PointsModel extends Model
{
  protected $table = 'point_system';
  protected $primaryKey = 'id';
  protected $allowedFields = ['description', 'points'];
  protected bool $allowEmptyInserts = false;
  protected bool $updateOnlyChanged = true;
  protected $useSoftDeletes = false;
  // Dates
  protected $useTimestamps = false;

  protected $dateFormat = '';
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


  public function getAllPoints()
  {
    return $this->findAll();
  }


}
?>