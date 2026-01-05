<?php

use CodeIgniter\Router\RouteCollection;
use Config\Services;

/** @var RouteCollection $routes */
$routes = Services::routes();

$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->post('logout', 'Auth::logout');

$routes->group('admin', ['filter' => 'rbac:super_admin'], static function ($routes) {
    $routes->get('users', 'Admin\\Users::index');
    $routes->get('users/create', 'Admin\\Users::create');
    $routes->post('users', 'Admin\\Users::store');
    $routes->get('users/(:num)/edit', 'Admin\\Users::edit/$1');
    $routes->post('users/(:num)', 'Admin\\Users::update/$1');
    $routes->post('users/(:num)/delete', 'Admin\\Users::delete/$1');
});

return $routes;
