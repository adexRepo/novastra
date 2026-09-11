<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$applicationRoot = dirname(__DIR__);
if (! is_file($applicationRoot.'/vendor/autoload.php') || ! is_file($applicationRoot.'/bootstrap/app.php')) {
    $applicationRoot = dirname(__DIR__).'/repositories/novastra-php';
}

if (! is_file($applicationRoot.'/vendor/autoload.php') || ! is_file($applicationRoot.'/bootstrap/app.php')) {
    http_response_code(503);
    exit('Application bootstrap is unavailable.');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $applicationRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $applicationRoot.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $applicationRoot.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
