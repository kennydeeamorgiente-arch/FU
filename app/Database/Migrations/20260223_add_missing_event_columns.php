<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingEventColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('events', [
            'activity_initiator' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'highest_access_level'
            ],
            'semester_academic_year' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'activity_initiator'
            ],
            'nature_activity' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'semester_academic_year'
            ],
            'type_activity' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'nature_activity'
            ],
            'organization_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'type_activity'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('events', [
            'activity_initiator',
            'semester_academic_year', 
            'nature_activity',
            'type_activity',
            'organization_name'
        ]);
    }
}
