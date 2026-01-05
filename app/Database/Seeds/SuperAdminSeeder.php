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

        $this->db->table('users')->insert([
            'name'          => 'Super Admin',
            'email'         => 'superadmin@example.com',
            'password_hash' => $password,
            'status'        => 'active',
            'created_at'    => $now,
        ]);

        $userId = $this->db->insertID();
        $roleId = $this->db->table('roles')->select('id')->where('slug', 'super_admin')->get()->getRow()?->id;

        if ($userId && $roleId) {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }
    }
}
