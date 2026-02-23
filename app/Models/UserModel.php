<?php
namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['email', 'password', 'position', 'access_id', 'org_id','first_name', 'last_name'];
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


    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function getAllUser()
    {
        //kwaon tanan user
        return $this->findAll();
    }

    //kani para makuha tanan needed satong table
    public function getAllUserWithDetails()
    {

        return $this->select('users.user_id, users.email, a.access_name, o.org_name, users.position, users.created_at')
            ->join('access_level a', 'a.access_id = users.access_id', 'left')
            ->join('organization o', 'o.org_id = users.org_id', 'left')
            ->findAll();

    }

    // Get user by ID with all details
    public function getUserByIdWithDetails($userId)
    {
        return $this->select('users.user_id, users.email, users.access_id, users.org_id, a.access_name, o.org_name, users.position, users.created_at')
            ->join('access_level a', 'a.access_id = users.access_id', 'left')
            ->join('organization o', 'o.org_id = users.org_id', 'left')
            ->where('users.user_id', $userId)
            ->first();
    }

    public function createUser($data)
    {
        return $this->insert($data, true);
    }

    // Update user
    public function updateUser($user_id, $email, $position, $access_id, $org_id)
    {
        $data = [];
        if ($email)
            $data['email'] = $email;
        if ($position !== null)
            $data['position'] = $position;
        if ($access_id)
            $data['access_id'] = $access_id;
        if ($org_id !== null)
            $data['org_id'] = $org_id ? $org_id : null;

        if (empty($data)) {
            return false;
        }

        try {
            // Use the base table name for update (without alias)
            $db = \Config\Database::connect();
            $builder = $db->table('users');
            return $builder->where('user_id', $user_id)->update($data);
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function updateNameIfEmpty($user_id, $firstName, $lastName)
    {
        // Build update data only with non-empty values
        $updateData = [];
        
        if (!empty($firstName)) {
            $updateData['first_name'] = $firstName;
        }
        
        if (!empty($lastName)) {
            $updateData['last_name'] = $lastName;
        }
        
        // Only update if there's data to update
        if (!empty($updateData)) {
            try {
                $builder = $this->db->table('users');
                $builder->where('user_id', $user_id);
                $builder->update($updateData);
                return true;
            } catch (\Exception $e) {
                log_message('error', 'Error updating user name: ' . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }

    /**
     * Get all users by access_id
     */
    public function getUsersByAccessId($accessId)
    {
        return $this->where('access_id', $accessId)
            ->findAll();
    }

    /**
     * Get user IDs by access_id
     */
    public function getUserIdsByAccessId($accessId)
    {
        $users = $this->where('access_id', $accessId)
            ->findAll();
        
        return array_column($users, 'user_id');
    }
}
?>