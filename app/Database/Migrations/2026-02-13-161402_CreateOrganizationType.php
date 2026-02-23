<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganizationType extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'org_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);

        $this->forge->addKey('org_type_id', true);
        $this->forge->createTable('organization_type');
    }

    public function down()
    {
        $this->forge->dropTable('organization_type');
    }
}