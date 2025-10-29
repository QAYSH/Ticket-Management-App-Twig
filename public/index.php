<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

// TEMPORARY FIX: Suppress deprecation warnings for PHP 8.1 compatibility
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Handle Railway environment variables
$env = $_SERVER['APP_ENV'] ?? 'prod';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));

// Set default timezone
date_default_timezone_set('UTC');

// Configure for Railway
if (isset($_SERVER['RAILWAY_STATIC_URL'])) {
    // Set trusted proxies for Railway
    Request::setTrustedProxies(
        ['127.0.0.1', 'REMOTE_ADDR'], 
        Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
    );
}

return function (array $context) use ($env, $debug) {
    return new Kernel($env, $debug);
};