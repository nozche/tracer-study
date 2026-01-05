<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class FakultasSeeder extends Seeder
{
    public function run(): void
    {
        $now = Time::now('Asia/Jakarta', 'UTC')->toDateTimeString();

        $fakultas = [
            ['nama' => 'Teknik', 'created_at' => $now],
            ['nama' => 'Ekonomi dan Bisnis', 'created_at' => $now],
            ['nama' => 'Ilmu Komputer', 'created_at' => $now],
        ];

        $this->db->table('fakultas')->insertBatch($fakultas);
    }
}
