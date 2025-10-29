<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Railway-specific configuration
if (isset($_SERVER['RAILWAY_STATIC_URL'])) {
    $_SERVER['APP_HOST'] = $_SERVER['RAILWAY_STATIC_URL'];
}

// Set default timezone
date_default_timezone_set('UTC');

return function (array $context) {
    $env = $context['APP_ENV'] ?? 'prod';
    $debug = (bool) ($context['APP_DEBUG'] ?? ('prod' !== $env));
    
    return new Kernel($env, $debug);
};