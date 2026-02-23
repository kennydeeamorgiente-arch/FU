<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventsHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'event_history_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
        ]);

        $this->forge->addKey('event_history_id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('event_id');
        $this->forge->addKey('status_id');

        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'events_status', 'status_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('events_history');
    }

    public function down()
    {
        $this->forge->dropTable('events_history');
    }
}