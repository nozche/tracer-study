<?php

use CodeIgniter\Router\RouteCollection;
use Config\Services;

/** @var RouteCollection $routes */
$routes = Services::routes();

$routes->get('/', 'Home::index');

return $routes;
