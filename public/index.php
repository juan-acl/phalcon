<?php

declare(strict_types=1);

use App\Library\Http\SecurityHeaders;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Cookies de sesión más seguras (antes de iniciar la sesión)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// No revelar tecnología ni versiones
header_remove('X-Powered-By');
@ini_set('expose_php', '0');

$config = require APP_PATH . '/config/config.php';

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/var/log/php-error.log');
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');

try {
    $di = new FactoryDefault();

    require APP_PATH . '/config/loader.php';
    require APP_PATH . '/config/services.php';

    $application = new Application($di);
    $response = $application->handle($_SERVER['REQUEST_URI'] ?? '/');

    SecurityHeaders::apply($response);
    $response->send();
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $config['app']['debug'] ? (string) $e : 'Error interno del servidor.';
}
