<?php
namespace App\Models;
use CodeIgniter\Model;

class AccessLevelModel extends Model
{
  protected $table = 'access_level';
  protected $primaryKey = 'access_id';
  protected $allowedFields = ['access_id', 'access_name'];
  protected $useTimestamps = true;

  public function getAllAccessLevels()
  {
    return $this->findAll();
  }
}
?>

