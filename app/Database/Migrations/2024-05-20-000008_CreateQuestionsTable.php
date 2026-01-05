<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'survey_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'question_text' => [
                'type' => 'TEXT',
            ],
            'question_type' => [
                'type'       => 'ENUM',
                'constraint' => ['short_text', 'paragraph', 'single_choice', 'multiple_choice', 'rating'],
                'default'    => 'short_text',
            ],
            'options' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'is_required' => [
                'type'    => 'BOOLEAN',
                'null'    => false,
                'default' => false,
            ],
            'display_order' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['survey_id', 'display_order']);
        $this->forge->addForeignKey('survey_id', 'surveys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('questions');
    }

    public function down(): void
    {
        $this->forge->dropTable('questions', true);
    }
}
