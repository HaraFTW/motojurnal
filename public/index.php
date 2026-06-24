<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Resolve Laravel application root
|--------------------------------------------------------------------------
|
| Local:  index.php lives in the project's public/ folder.
| Shared hosting: public/ is copied to public_html/motojurnal/ while the
| app is cloned to /home/motojurnal/ (sibling of public_html).
|
| Override anytime with LARAVEL_ROOT in the server environment or .env
| (loaded later; for index.php use SetEnv in .htaccess if needed).
|
*/
$laravelRoot = getenv('LARAVEL_ROOT') ?: null;

if (! $laravelRoot) {
    $candidates = [
        dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'motojurnal',
        __DIR__.DIRECTORY_SEPARATOR.'..',
    ];

    foreach ($candidates as $candidate) {
        $resolved = realpath($candidate);

        if ($resolved && is_file($resolved.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
            $laravelRoot = $resolved;
            break;
        }
    }
}

if (! $laravelRoot) {
    $laravelRoot = realpath(__DIR__.'/..') ?: __DIR__.'/..';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelRoot.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelRoot.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
