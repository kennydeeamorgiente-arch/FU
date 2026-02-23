<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EventsStatusSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['status_id' => 1, 'name' => 'Pending'],
            ['status_id' => 2, 'name' => 'In-Progress'],
            ['status_id' => 3, 'name' => 'Awaiting Documentation'],
            ['status_id' => 4, 'name' => 'For Verification'],
            ['status_id' => 5, 'name' => 'Completed'],
            ['status_id' => 6, 'name' => 'Rejected'],
            ['status_id' => 7, 'name' => 'Returned For Revision'],
            ['status_id' => 8, 'name' => 'Approved'],
        ];

        $this->db->table('events_status')->insertBatch($data);
    }
}