<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventDocumentation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'doc_id' => [
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'file_type' => [
                'type' => 'ENUM',
                'constraint' => ['Documentation', 'Photo', 'Video', 'Other'],
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('doc_id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('user_id');

        $this->forge->addForeignKey('org_id', 'organization', 'org_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('event_documentation');
    }

    public function down()
    {
        $this->forge->dropTable('event_documentation');
    }
}