<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCollaboration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'coll_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'org_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'For internal FU organizations',
            ],
            'external_org_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'For external organizations',
            ],
            'collaborator_type' => [
                'type' => 'ENUM',
                'constraint' => ['internal', 'external'],
                'default' => 'internal',
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Organization description or role in event',
            ],
            'is_primary_organizer' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 = primary organizer, 0 = collaborator',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('coll_id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('org_id');
        $this->forge->addKey('collaborator_type');

        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('org_id', 'organization', 'org_id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('collaboration');
    }

    public function down()
    {
        $this->forge->dropTable('collaboration');
    }
}