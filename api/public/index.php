<?php

declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return static function (array $context) {
    if ($context['APP_ENV'] === 'dev') {
        // CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: origin, accept, content-type, host, access-control-allow-origin, x-requested-with');
        header('Access-Control-Expose-Headers: content-type, content-disposition, content-encoding');
        header('Access-Control-Allow-Credentials: true');
        // CORB
        header('X-Content-Type-Options: nosniff');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
