<?php

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$preflight = static function (): ResponseInterface {
    return service('response')->setStatusCode(204);
};

$routes->options('api/(:any)', $preflight, ['filter' => 'cors']);
$routes->options('auth/(:any)', $preflight, ['filter' => 'cors']);

$routes->group('api/auth', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->get('splash', 'AuthController::splash');
    $routes->post('login', 'AuthController::login');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->get('profile/(:num)', 'AuthController::profile/$1');
    $routes->put('profile/(:num)', 'AuthController::updateProfile/$1');
    $routes->patch('profile/(:num)', 'AuthController::updateProfile/$1');
    $routes->post('change-password/(:num)', 'AuthController::changePassword/$1');
});

$routes->group('auth', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->get('splash', 'AuthController::splash');
    $routes->post('login', 'AuthController::login');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->get('profile/(:num)', 'AuthController::profile/$1');
    $routes->put('profile/(:num)', 'AuthController::updateProfile/$1');
    $routes->patch('profile/(:num)', 'AuthController::updateProfile/$1');
    $routes->post('change-password/(:num)', 'AuthController::changePassword/$1');
});
