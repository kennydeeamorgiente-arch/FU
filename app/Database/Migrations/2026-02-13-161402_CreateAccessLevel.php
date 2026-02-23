<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccessLevel extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'access_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'access_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);

        $this->forge->addKey('access_id', true);
        $this->forge->createTable('access_level');
    }

    public function down()
    {
        $this->forge->dropTable('access_level');
    }
}