<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePointSystem extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'points' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('point_system');
    }

    public function down()
    {
        $this->forge->dropTable('point_system');
    }
}