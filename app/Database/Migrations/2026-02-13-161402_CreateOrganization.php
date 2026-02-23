<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganization extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'org_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'org_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'adviser' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'org_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'facebook_link' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'org_drive_link' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'foundationu_logo.png',
            ],
            'org_num_members' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('org_id', true);
        $this->forge->addForeignKey('org_type_id', 'organization_type', 'org_type_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('organization');
    }

    public function down()
    {
        $this->forge->dropTable('organization');
    }
}