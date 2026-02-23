<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['org_type_id' => 1, 'type' => 'Academic'],
            ['org_type_id' => 2, 'type' => 'Non-Academic'],
            ['org_type_id' => 3, 'type' => 'Cultural'],
            ['org_type_id' => 4, 'type' => 'Sports'],
            ['org_type_id' => 5, 'type' => 'Religious'],
        ];

        $this->db->table('organization_type')->insertBatch($data);
    }
}