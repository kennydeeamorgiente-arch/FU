<?php
namespace App\Models;
use CodeIgniter\Model;
use Exception;

class BudgetBreakdownModel extends Model
{
  protected $table = 'event_budget_breakdown';
  protected $primaryKey = 'budget_id';
  protected $allowedFields = ['event_id', 'description', 'quantity', 'unit', 'purpose', 'unit_price', 'amount'];
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

  public function getBudgetById($id)
  {
    return $this->where("event_id", $id)->findAll();
  }

  public function insertBudget($data)
  {
    return $this->insert($data);
  }

  public function deleteByEvent($eventId)
  {
    return $this->where('event_id', $eventId)->delete();
  }
}
?>