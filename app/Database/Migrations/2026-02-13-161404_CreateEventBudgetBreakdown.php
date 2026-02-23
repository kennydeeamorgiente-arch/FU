<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventBudgetBreakdown extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'budget_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'unit' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'purpose' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('budget_id', true);
        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('event_budget_breakdown');
    }

    public function down()
    {
        $this->forge->dropTable('event_budget_breakdown');
    }
}