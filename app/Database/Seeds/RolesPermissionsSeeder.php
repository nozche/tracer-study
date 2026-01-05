<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Time::now('Asia/Jakarta', 'UTC')->toDateTimeString();

        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Full system access', 'created_at' => $now],
            ['name' => 'Staf', 'slug' => 'staf', 'description' => 'Administrative staff', 'created_at' => $now],
            ['name' => 'Mahasiswa', 'slug' => 'mahasiswa', 'description' => 'Student role', 'created_at' => $now],
            ['name' => 'Dosen', 'slug' => 'dosen', 'description' => 'Lecturer role', 'created_at' => $now],
            ['name' => 'Alumni', 'slug' => 'alumni', 'description' => 'Alumni role', 'created_at' => $now],
        ];
        $this->db->table('roles')->insertBatch($roles);

        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Create/update/remove users', 'created_at' => $now],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Create/update/remove roles', 'created_at' => $now],
            ['name' => 'Manage Permissions', 'slug' => 'manage_permissions', 'description' => 'Assign permissions to roles', 'created_at' => $now],
        ];
        $this->db->table('permissions')->insertBatch($permissions);

        $superAdminRoleId = $this->db->table('roles')->select('id')->where('slug', 'super_admin')->get()->getRow()?->id;
        if ($superAdminRoleId) {
            $permissionRows = $this->db->table('permissions')->select('id')->get()->getResult();
            $assignments = array_map(static fn($row) => ['role_id' => $superAdminRoleId, 'permission_id' => $row->id], $permissionRows);
            if ($assignments) {
                $this->db->table('role_permissions')->insertBatch($assignments);
            }
        }
    }
}
