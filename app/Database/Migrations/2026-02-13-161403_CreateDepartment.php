<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepartment extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'department_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'department_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
        ]);

        $this->forge->addKey('department_id', true);
        $this->forge->createTable('department');
    }

    public function down()
    {
        $this->forge->dropTable('department');
    }
}