<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfilesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'fakultas_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'program_studi_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('fakultas_id', 'fakultas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('program_studi_id', 'program_studi', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('profiles');
    }

    public function down(): void
    {
        $this->forge->dropTable('profiles');
    }
}
