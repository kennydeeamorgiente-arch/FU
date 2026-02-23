<?php
namespace App\Models;
use CodeIgniter\Model;

class OrganizationTypeModel extends Model
{
  protected $table = 'organization_type';
  protected $primaryKey = 'org_type_id';
  protected $allowedFields = ['org_type_id', 'type'];
  protected $useTimestamps = true; // ✅ Correct spelling


  public function getOrgType($org_type_name)
  {
    return $this->where('type', $org_type_name)->first();
  }
}
?>