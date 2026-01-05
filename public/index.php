<?php

// Path to the front controller
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Composer autoload
if (! file_exists(FCPATH . '../vendor/autoload.php')) {
    exit('Composer dependencies not installed. Run `composer install`.');
}

require FCPATH . '../vendor/autoload.php';

// Load our paths configuration file
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();

// Load the framework bootloader.
require rtrim($paths->systemDirectory, '\\/') . '/Boot.php';

// Launch the application
require_once SYSTEMPATH . 'bootstrap.php';
