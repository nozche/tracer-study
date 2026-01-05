<?php

namespace Config;

use CodeIgniter\Database\Config as BaseConfig;

class Database extends BaseConfig
{
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'port'     => 3306,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'DDLBackup'=> false,
        'schema'   => '',
        'saveQueries' => true,
    ];

    public array $tests = [
        'DSN'      => '',
        'hostname' => '127.0.0.1',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 'test_',
        'swapPre'  => '',
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->default['hostname'] = env('database.default.hostname', $this->default['hostname']);
        $this->default['username'] = env('database.default.username', $this->default['username']);
        $this->default['password'] = env('database.default.password', $this->default['password']);
        $this->default['database'] = env('database.default.database', $this->default['database']);
        $this->default['DBDriver'] = env('database.default.DBDriver', $this->default['DBDriver']);
        $this->default['DBPrefix'] = env('database.default.DBPrefix', $this->default['DBPrefix']);
        $this->default['port'] = (int) env('database.default.port', $this->default['port']);
    }
}
