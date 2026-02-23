<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventPoints extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'event_points_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'point_system_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('event_points_id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('point_system_id');

        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('point_system_id', 'point_system', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('event_points');
    }

    public function down()
    {
        $this->forge->dropTable('event_points');
    }
}