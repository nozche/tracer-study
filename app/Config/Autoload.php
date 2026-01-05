<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    /** @var array<string, string> */
    public array $psr4 = [
        'App\\' => APPPATH,
    ];

    /** @var array<string, string> */
    public array $classmap = [];

    /** @var list<string> */
    public array $files = [];

    /** @var list<string> */
    public array $helpers = ['url', 'session'];
}
