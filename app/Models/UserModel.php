<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'status',
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function withRelations(): self
    {
        return $this->select([
            'users.*',
            'roles.name as role_name',
            'roles.slug as role_slug',
            'profiles.phone',
            'profiles.address',
            'fakultas.nama as fakultas_nama',
            'program_studi.nama as program_studi_nama',
        ])
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->join('profiles', 'profiles.user_id = users.id', 'left')
            ->join('fakultas', 'fakultas.id = profiles.fakultas_id', 'left')
            ->join('program_studi', 'program_studi.id = profiles.program_studi_id', 'left');
    }

    public function findWithRelations(int $id): ?array
    {
        return $this->withRelations()->where('users.id', $id)->first();
    }
}
