<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PointSystemSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['id' => 4, 'description' => 'Sponsored activity by the organization outside the University', 'points' => 100],
            ['id' => 5, 'description' => 'Co-sponsored activity in collaboration with NGOs / Industry partners outside the University', 'points' => 100],
            ['id' => 6, 'description' => 'Sponsored activity by the organization within the University', 'points' => 80],
            ['id' => 7, 'description' => 'Co-sponsored activity in collaboration with NGOs / Industry partners within the University', 'points' => 80],
            ['id' => 8, 'description' => 'Sponsored activity by the organization within their respective department', 'points' => 60],
            ['id' => 9, 'description' => 'Co-sponsored activity in collaboration with other departments', 'points' => 60],
            ['id' => 10, 'description' => 'Participation to other department\'s activity in the university', 'points' => 30],
            ['id' => 11, 'description' => 'Participation to own activity within the university', 'points' => 10],
            ['id' => 12, 'description' => 'General Audience', 'points' => 10],
            ['id' => 13, 'description' => 'Participated as a Contestant of a competition organized by FUSG', 'points' => 30],
            ['id' => 14, 'description' => 'Won Competition of an SG Organized Activity', 'points' => 50],
        ];

        $this->db->table('point_system')->insertBatch($data);
    }
}