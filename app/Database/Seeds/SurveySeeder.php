<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $now = Time::now('Asia/Jakarta', 'UTC');
        $periodStart = Time::now('Asia/Jakarta', 'UTC');
        $periodEnd = Time::now('Asia/Jakarta', 'UTC')->addMonths(1);

        $this->db->table('surveys')->insert([
            'title'        => 'Survey Tracer Study 2024',
            'description'  => 'Survey awal untuk mengumpulkan umpan balik alumni terkait pengalaman belajar dan karier.',
            'status'       => 'publish',
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
            'created_at'   => $now->toDateTimeString(),
        ]);

        $surveyId = $this->db->insertID();
        if (! $surveyId) {
            return;
        }

        $questions = [
            [
                'survey_id'      => $surveyId,
                'question_text'  => 'Bagaimana tingkat kepuasan Anda terhadap kurikulum di program studi?',
                'question_type'  => 'single_choice',
                'options'        => json_encode(['Sangat Puas', 'Puas', 'Cukup', 'Kurang']),
                'is_required'    => true,
                'display_order'  => 1,
                'created_at'     => $now->toDateTimeString(),
            ],
            [
                'survey_id'      => $surveyId,
                'question_text'  => 'Sebutkan keterampilan yang paling membantu karier Anda setelah lulus.',
                'question_type'  => 'paragraph',
                'options'        => null,
                'is_required'    => false,
                'display_order'  => 2,
                'created_at'     => $now->toDateTimeString(),
            ],
            [
                'survey_id'      => $surveyId,
                'question_text'  => 'Seberapa relevan materi perkuliahan dengan pekerjaan Anda saat ini?',
                'question_type'  => 'rating',
                'options'        => json_encode(['min' => 1, 'max' => 5, 'labels' => ['Tidak Relevan', 'Sangat Relevan']]),
                'is_required'    => true,
                'display_order'  => 3,
                'created_at'     => $now->toDateTimeString(),
            ],
            [
                'survey_id'      => $surveyId,
                'question_text'  => 'Pilih fasilitas kampus yang paling sering Anda gunakan (boleh lebih dari satu).',
                'question_type'  => 'multiple_choice',
                'options'        => json_encode(['Perpustakaan', 'Laboratorium', 'Pusat Karier', 'Kantin']),
                'is_required'    => false,
                'display_order'  => 4,
                'created_at'     => $now->toDateTimeString(),
            ],
        ];

        $this->db->table('questions')->insertBatch($questions);
    }
}
