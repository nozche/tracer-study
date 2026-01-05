<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResponseAnswersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'response_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'question_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'answer_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'answer_json' => [
                'type' => 'JSON',
                'null' => true,
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
        $this->forge->addKey(['response_id', 'question_id']);
        $this->forge->addForeignKey('response_id', 'responses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('question_id', 'questions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('response_answers');
    }

    public function down(): void
    {
        $this->forge->dropTable('response_answers', true);
    }
}
