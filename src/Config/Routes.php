<?php

/**
 * @package   Pubvana\Admin
 * @copyright 2026 Pubvana
 * @license   MIT
 */

/**
 * Admin routes.
 *
 * Auto-prefixed by Flight School. Prefix: /admin
 *
 * Routes:
 *   GET  /admin                      - Dashboard
 *   GET  /admin/settings             - Settings (default class)
 *   GET  /admin/settings/@class      - Settings (specific class)
 *   POST /admin/settings/@class      - Save settings
 *   GET  /admin/users                - User list
 *   GET  /admin/users/create         - Create user form
 *   POST /admin/users/store          - Store new user
 *   GET  /admin/users/@id/edit       - Edit user form
 *   POST /admin/users/@id/update     - Update user
 *   POST /admin/users/@id/delete     - Delete user
 *   GET  /admin/groups                - Group list
 *   GET  /admin/groups/create         - Create group form
 *   POST /admin/groups/store          - Store new group
 *   GET  /admin/groups/@id/edit       - Edit group form
 *   POST /admin/groups/@id/update     - Update group
 *   POST /admin/groups/@id/delete     - Delete group
 */

use Enlivenapp\FlightCsrf\Middlewares\CsrfMiddleware;
use Enlivenapp\FlightShield\Middlewares\GroupMiddleware;
use Enlivenapp\FlightShield\Middlewares\PermissionMiddleware;
use Pubvana\Admin\Controllers\DashboardController;
use Pubvana\Admin\Controllers\GroupsController;
use Pubvana\Admin\Controllers\SettingsController;
use Pubvana\Admin\Controllers\UsersController;

/** @var \flight\net\Router $router */
/** @var \flight\Engine $app */
/** @var string $configPrepend */

$router->get('/', function () use ($app, $configPrepend) {
    (new DashboardController($app, $configPrepend))->index();
})->addMiddleware(new GroupMiddleware($app, 'superadmin'));

$router->get('/settings', function () use ($app, $configPrepend) {
    (new SettingsController($app, $configPrepend))->index();
})->addMiddleware(new PermissionMiddleware($app, 'settings.edit'));

$router->get('/settings/@class', function (string $class) use ($app, $configPrepend) {
    (new SettingsController($app, $configPrepend))->index($class);
})->addMiddleware(new PermissionMiddleware($app, 'settings.edit'));

$router->post('/settings/@class', function (string $class) use ($app, $configPrepend) {
    (new SettingsController($app, $configPrepend))->save($class);
})->addMiddleware(new PermissionMiddleware($app, 'settings.edit'))
  ->addMiddleware(new CsrfMiddleware($app));

// Users
$router->get('/users', function () use ($app, $configPrepend) {
    (new UsersController($app, $configPrepend))->index();
})->addMiddleware(new PermissionMiddleware($app, 'users.list'));

$router->get('/users/create', function () use ($app, $configPrepend) {
    (new UsersController($app, $configPrepend))->create();
})->addMiddleware(new PermissionMiddleware($app, 'users.create'));

$router->post('/users/store', function () use ($app, $configPrepend) {
    (new UsersController($app, $configPrepend))->store();
})->addMiddleware(new PermissionMiddleware($app, 'users.create'))
  ->addMiddleware(new CsrfMiddleware($app));

$router->get('/users/@id/edit', function (string $id) use ($app, $configPrepend) {
    (new UsersController($app, $configPrepend))->edit($id);
})->addMiddleware(new PermissionMiddleware($app, 'users.edit'));

$router->post('/users/@id/update', function (string $id) use ($app, $configPrepend) {
    (new UsersController($app, $configPrepend))->update($id);
})->addMiddleware(new PermissionMiddleware($app, 'users.edit'))
  ->addMiddleware(new CsrfMiddleware($app));

$router->post('/users/@id/delete', function (string $id) use ($app, $configPrepend) {
    (new UsersController($app, $configPrepend))->delete($id);
})->addMiddleware(new PermissionMiddleware($app, 'users.delete'))
  ->addMiddleware(new CsrfMiddleware($app));

// Groups
$router->get('/groups', function () use ($app, $configPrepend) {
    (new GroupsController($app, $configPrepend))->index();
})->addMiddleware(new GroupMiddleware($app, 'superadmin'));

$router->get('/groups/create', function () use ($app, $configPrepend) {
    (new GroupsController($app, $configPrepend))->create();
})->addMiddleware(new GroupMiddleware($app, 'superadmin'));

$router->post('/groups/store', function () use ($app, $configPrepend) {
    (new GroupsController($app, $configPrepend))->store();
})->addMiddleware(new GroupMiddleware($app, 'superadmin'))
  ->addMiddleware(new CsrfMiddleware($app));

$router->get('/groups/@id/edit', function (string $id) use ($app, $configPrepend) {
    (new GroupsController($app, $configPrepend))->edit($id);
})->addMiddleware(new GroupMiddleware($app, 'superadmin'));

$router->post('/groups/@id/update', function (string $id) use ($app, $configPrepend) {
    (new GroupsController($app, $configPrepend))->update($id);
})->addMiddleware(new GroupMiddleware($app, 'superadmin'))
  ->addMiddleware(new CsrfMiddleware($app));

$router->post('/groups/@id/delete', function (string $id) use ($app, $configPrepend) {
    (new GroupsController($app, $configPrepend))->delete($id);
})->addMiddleware(new GroupMiddleware($app, 'superadmin'))
  ->addMiddleware(new CsrfMiddleware($app));
