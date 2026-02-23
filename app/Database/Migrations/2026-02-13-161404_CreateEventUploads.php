<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventUploads extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'upload_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'org_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_type' => [
                'type' => 'ENUM',
                'constraint' => ['Proposal', 'Program Paper', 'Communication Letter'],
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('upload_id', true);
        $this->forge->addKey('event_id');

        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('org_id', 'organization', 'org_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('event_uploads');
    }

    public function down()
    {
        $this->forge->dropTable('event_uploads');
    }
}