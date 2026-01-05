<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{
    protected $table = 'profiles';
    protected $allowedFields = [
        'user_id',
        'fakultas_id',
        'program_studi_id',
        'phone',
        'address',
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
