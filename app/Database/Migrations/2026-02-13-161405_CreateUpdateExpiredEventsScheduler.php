<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUpdateExpiredEventsScheduler extends Migration
{
    public function up()
    {
        // Enable the event scheduler
        $this->db->query("SET GLOBAL event_scheduler = ON");

        // Create the event
        $sql = "
        CREATE EVENT IF NOT EXISTS update_expired_events
        ON SCHEDULE EVERY 1 DAY
        STARTS CONCAT(CURDATE(), ' 17:00:00')
        DO
        BEGIN
            UPDATE events
            SET status_id = 4
            WHERE event_end_date < CURDATE()
              AND status_id = 2;
        END
        ";

        $this->db->query($sql);
    }

    public function down()
    {
        // Drop the event
        $this->db->query("DROP EVENT IF EXISTS update_expired_events");
    }
}