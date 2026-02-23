<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => 55,
            ],
            'last_name' => [
                'type' => 'VARCHAR',
                'constraint' => 55,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'access_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'org_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATE',
            ],
            'deleted_at' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('user_id', true);
        $this->forge->addForeignKey('access_id', 'access_level', 'access_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('org_id', 'organization', 'org_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}