<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventsStatus extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'status_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);

        $this->forge->addKey('status_id', true);
        $this->forge->createTable('events_status');
    }

    public function down()
    {
        $this->forge->dropTable('events_status');
    }
}