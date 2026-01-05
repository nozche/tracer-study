<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = Time::now('Asia/Jakarta', 'UTC')->toDateTimeString();
        $password = password_hash('SuperAdmin@123', PASSWORD_DEFAULT);

        $existingUser = $this->db->table('users')->where('email', 'superadmin@example.com')->get()->getRow();
        if ($existingUser) {
            $userId = $existingUser->id;
        } else {
            $this->db->table('users')->insert([
                'name'          => 'Super Admin',
                'email'         => 'superadmin@example.com',
                'password_hash' => $password,
                'status'        => 'active',
                'created_at'    => $now,
            ]);
            $userId = $this->db->insertID();
        }

        $roleId = $this->db->table('roles')->select('id')->where('slug', 'super_admin')->get()->getRow()?->id;

        if ($userId && $roleId) {
            $alreadyAssigned = $this->db->table('user_roles')->where([
                'user_id' => $userId,
                'role_id' => $roleId,
            ])->countAllResults() > 0;

            if (! $alreadyAssigned) {
                $this->db->table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ]);
            }
        }

        $profileExists = $this->db->table('profiles')->where('user_id', $userId)->countAllResults() > 0;
        if (! $profileExists) {
            $fakultasId = $this->db->table('fakultas')->select('id')->orderBy('id', 'ASC')->get()->getRow()?->id;
            $programStudiId = $this->db->table('program_studi')->select('id')->orderBy('id', 'ASC')->get()->getRow()?->id;

            $this->db->table('profiles')->insert([
                'user_id'          => $userId,
                'fakultas_id'      => $fakultasId,
                'program_studi_id' => $programStudiId,
                'phone'            => '080000000000',
                'address'          => 'Administrator profile',
                'created_at'       => $now,
            ]);
        }
    }
}
