<?php

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$preflight = static function (): ResponseInterface {
    return service('response')->setStatusCode(204);
};

foreach (['api/(:any)', 'auth/(:any)', 'dashboard/(:any)', 'tickets/(:any)', 'assets/(:any)', 'knowledge-base/(:any)', 'kb/(:any)', 'navigation/(:any)', 'settings/(:any)', 'users/(:any)'] as $path) {
    $routes->options($path, $preflight, ['filter' => 'cors']);
}

$registerApiRoutes = static function (RouteCollection $routes): void {
    $routes->group('auth', static function (RouteCollection $routes): void {
        $routes->get('splash', 'AuthController::splash');
        $routes->post('login', 'AuthController::login');
        $routes->post('forgot-password', 'AuthController::forgotPassword');
        $routes->get('profile/(:num)', 'AuthController::profile/$1');
        $routes->put('profile/(:num)', 'AuthController::updateProfile/$1');
        $routes->patch('profile/(:num)', 'AuthController::updateProfile/$1');
        $routes->post('change-password/(:num)', 'AuthController::changePassword/$1');
    });

    $routes->group('dashboard', static function (RouteCollection $routes): void {
        $routes->get('employee', 'DashboardController::employeeHome');
        $routes->get('employee/(:num)', 'DashboardController::employeeHome/$1');
        $routes->get('staff', 'DashboardController::staffDashboard');
        $routes->get('staff/(:num)', 'DashboardController::staffDashboard/$1');
        $routes->get('admin', 'DashboardController::adminDashboard');
        $routes->get('reports', 'DashboardController::reports');
    });

    $routes->group('tickets', static function (RouteCollection $routes): void {
        $routes->get('/', 'TicketsController::index');
        $routes->post('/', 'TicketsController::create');
        $routes->get('categories', 'TicketsController::categories');
        $routes->get('priorities', 'TicketsController::priorities');
        $routes->get('sla-rules', 'TicketsController::slaRules');
        $routes->post('(:num)/comments', 'TicketsController::addComment/$1');
        $routes->post('(:num)/status', 'TicketsController::changeStatus/$1');
        $routes->get('(:num)', 'TicketsController::show/$1');
        $routes->put('(:num)', 'TicketsController::update/$1');
        $routes->patch('(:num)', 'TicketsController::update/$1');
        $routes->delete('(:num)', 'TicketsController::delete/$1');
    });

    $routes->group('assets', static function (RouteCollection $routes): void {
        $routes->get('/', 'AssetsController::index');
        $routes->post('/', 'AssetsController::create');
        $routes->get('types', 'AssetsController::types');
        $routes->post('types', 'AssetsController::createType');
        $routes->post('(:num)/assign', 'AssetsController::assign/$1');
        $routes->post('assignments/(:num)/return', 'AssetsController::returnAssignment/$1');
        $routes->get('(:num)/maintenance', 'AssetsController::maintenanceLogs/$1');
        $routes->post('(:num)/maintenance', 'AssetsController::addMaintenanceLog/$1');
        $routes->get('(:num)', 'AssetsController::show/$1');
        $routes->put('(:num)', 'AssetsController::update/$1');
        $routes->patch('(:num)', 'AssetsController::update/$1');
        $routes->delete('(:num)', 'AssetsController::delete/$1');
    });

    $routes->group('knowledge-base', static function (RouteCollection $routes): void {
        $routes->get('/', 'KnowledgeBaseController::index');
        $routes->post('/', 'KnowledgeBaseController::create');
        $routes->get('articles', 'KnowledgeBaseController::index');
        $routes->get('categories', 'KnowledgeBaseController::categories');
        $routes->post('categories', 'KnowledgeBaseController::createCategory');
        $routes->put('categories/(:num)', 'KnowledgeBaseController::updateCategory/$1');
        $routes->patch('categories/(:num)', 'KnowledgeBaseController::updateCategory/$1');
        $routes->delete('categories/(:num)', 'KnowledgeBaseController::deleteCategory/$1');
        $routes->post('(:num)/publish', 'KnowledgeBaseController::publish/$1');
        $routes->post('(:num)/archive', 'KnowledgeBaseController::archive/$1');
        $routes->get('(:num)', 'KnowledgeBaseController::show/$1');
        $routes->put('(:num)', 'KnowledgeBaseController::update/$1');
        $routes->patch('(:num)', 'KnowledgeBaseController::update/$1');
        $routes->delete('(:num)', 'KnowledgeBaseController::delete/$1');
    });

    $routes->group('kb', static function (RouteCollection $routes): void {
        $routes->get('articles', 'KnowledgeBaseController::index');
        $routes->get('categories', 'KnowledgeBaseController::categories');
    });

    $routes->group('navigation', static function (RouteCollection $routes): void {
        $routes->get('/', 'NavigationController::index');
        $routes->get('tabs/(:segment)', 'NavigationController::tabs/$1');
    });

    $routes->group('settings', static function (RouteCollection $routes): void {
        $routes->get('/', 'SettingsController::index');
        $routes->get('health', 'SettingsController::health');
        $routes->get('ticket-options', 'SettingsController::ticketOptions');
        $routes->get('asset-options', 'SettingsController::assetOptions');
        $routes->get('knowledge-base-options', 'SettingsController::knowledgeBaseOptions');
    });

    $routes->group('users', static function (RouteCollection $routes): void {
        $routes->get('/', 'UsersController::index');
        $routes->post('/', 'UsersController::create');
        $routes->get('roles', 'UsersController::roles');
        $routes->get('departments', 'UsersController::departments');
        $routes->get('(:num)', 'UsersController::show/$1');
        $routes->put('(:num)', 'UsersController::update/$1');
        $routes->patch('(:num)', 'UsersController::update/$1');
        $routes->delete('(:num)', 'UsersController::delete/$1');
    });
};

$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], $registerApiRoutes);
$routes->group('', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], $registerApiRoutes);
