<?php

/**
 * Public routes owned by Admin.
 *
 * routePrepend is empty so these register at the root level.
 *
 * Routes:
 *   GET / - Homepage (dispatches based on CMS.frontPageType setting)
 *
 * @package Pubvana\Admin\Config
 */

use Pubvana\Admin\Controllers\HomepageController;

/** @var \flight\net\Router $router */
/** @var \flight\Engine $app */
/** @var string $configPrepend */

$router->get('/', function () use ($app, $configPrepend) {
    (new HomepageController($app, $configPrepend))->index();
});
