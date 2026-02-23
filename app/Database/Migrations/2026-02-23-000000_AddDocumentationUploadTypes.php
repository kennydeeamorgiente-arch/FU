<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentationUploadTypes extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE event_uploads
            MODIFY COLUMN file_type ENUM(
                'Proposal',
                'Program Paper',
                'Communication Letter',
                'Documentation',
                'Financial Report'
            ) NOT NULL
        ");

        $this->db->query("
            ALTER TABLE event_uploads
            MODIFY COLUMN uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down()
    {
        $this->db->query("
            DELETE FROM event_uploads
            WHERE file_type IN ('Documentation', 'Financial Report')
        ");

        $this->db->query("
            ALTER TABLE event_uploads
            MODIFY COLUMN file_type ENUM(
                'Proposal',
                'Program Paper',
                'Communication Letter'
            ) NOT NULL
        ");

        $this->db->query("
            ALTER TABLE event_uploads
            MODIFY COLUMN uploaded_at DATETIME NOT NULL
        ");
    }
}
