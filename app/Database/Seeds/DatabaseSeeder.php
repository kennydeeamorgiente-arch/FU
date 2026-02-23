<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('AccessLevelSeeder');
        $this->call('DepartmentSeeder');
        $this->call('OrganizationTypeSeeder');
        $this->call('EventsStatusSeeder');
        $this->call('PointSystemSeeder');
    }
}