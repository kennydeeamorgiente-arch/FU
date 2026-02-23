<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'org_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'status_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'event_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'event_desc' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'event_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'event_end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'event_start_time' => [
                'type' => 'TIME',
            ],
            'event_end_time' => [
                'type' => 'TIME',
            ],
            'event_purpose' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'event_uni_objectives' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'number_of_participants' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'has_invited_speaker' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'name_of_invited_resource_speaker' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'invited_speaker_description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'with_collaborators' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
            ],
            'event_is_in_campus' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'event_venue' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'event_budget' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'source_of_funds' => [
                'type' => 'VARCHAR',
                'constraint' => 111,
            ],
            'current_access_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'highest_access_level' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'activity_initiator' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'semester_academic_year' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'nature_activity' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'type_activity' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('event_id', true);
        $this->forge->addKey('org_id');
        $this->forge->addKey('status_id');
        $this->forge->addKey('current_access_id');
        $this->forge->addKey('highest_access_level');

        $this->forge->addForeignKey('org_id', 'organization', 'org_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'events_status', 'status_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('current_access_id', 'access_level', 'access_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('highest_access_level', 'access_level', 'access_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('events');
    }

    public function down()
    {
        $this->forge->dropTable('events');
    }
}