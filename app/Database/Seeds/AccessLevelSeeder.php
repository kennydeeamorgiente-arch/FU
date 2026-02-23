<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AccessLevelSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['access_id' => 0, 'access_name' => 'Student'],
            ['access_id' => 1, 'access_name' => 'Club Adviser'],
            ['access_id' => 2, 'access_name' => 'SAO'],
            ['access_id' => 3, 'access_name' => 'OSL'],
            ['access_id' => 4, 'access_name' => 'Vice Chancellor'],
            ['access_id' => 5, 'access_name' => 'OUC'],
        ];

        $this->db->table('access_level')->insertBatch($data);
    }
}