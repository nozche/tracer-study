<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Time::now('Asia/Jakarta', 'UTC')->toDateTimeString();

        $fakultas = $this->db->table('fakultas')->get()->getResult();
        $fakultasMap = [];
        foreach ($fakultas as $row) {
            $fakultasMap[$row->nama] = $row->id;
        }

        $programs = [
            ['nama' => 'Teknik Informatika', 'fakultas' => 'Ilmu Komputer'],
            ['nama' => 'Sistem Informasi', 'fakultas' => 'Ilmu Komputer'],
            ['nama' => 'Teknik Industri', 'fakultas' => 'Teknik'],
            ['nama' => 'Manajemen', 'fakultas' => 'Ekonomi dan Bisnis'],
        ];

        $rows = [];
        foreach ($programs as $program) {
            $rows[] = [
                'nama'        => $program['nama'],
                'fakultas_id' => $fakultasMap[$program['fakultas']] ?? null,
                'created_at'  => $now,
            ];
        }

        if ($rows !== []) {
            $this->db->table('program_studi')->insertBatch($rows);
        }
    }
}
