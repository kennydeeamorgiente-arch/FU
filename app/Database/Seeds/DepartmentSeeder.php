<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['department_id' => 1, 'department_name' => 'College of Agriculture'],
            ['department_id' => 2, 'department_name' => 'College of Arts and Sciences'],
            ['department_id' => 3, 'department_name' => 'College of Business Administration'],
            ['department_id' => 4, 'department_name' => 'College of Computer Studies'],
            ['department_id' => 5, 'department_name' => 'College of Criminology'],
            ['department_id' => 6, 'department_name' => 'College of Education'],
            ['department_id' => 7, 'department_name' => 'College of Hospitality Management'],
            ['department_id' => 8, 'department_name' => 'College of Law & Jurisprudence'],
            ['department_id' => 9, 'department_name' => 'College of Nursing'],
            ['department_id' => 10, 'department_name' => 'Department of Architecture and Fine Arts'],
            ['department_id' => 11, 'department_name' => 'Foundation Preparatory Academy'],
            ['department_id' => 12, 'department_name' => 'School of Industrial Engineering & Technology'],
        ];


        $this->db->table('department')->insertBatch($data);
    }
}