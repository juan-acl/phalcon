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

    if (is_file(BASE_PATH . '/vendor/autoload.php')) {
        require_once BASE_PATH . '/vendor/autoload.php';
    }

    require APP_PATH . '/config/loader.php';
    require APP_PATH . '/config/services.php';

    $application = new Application($di);
    $response = $application->handle($_SERVER['REQUEST_URI'] ?? '/');

    SecurityHeaders::apply($response);
    $response->send();
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inconveniente al procesar la solicitud - Sistema escolar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 text-center">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4 p-md-5">
            <div class="mb-3 text-warning">
              <i class="bi bi-exclamation-triangle-fill" style="font-size: 3.5rem;"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-3">Inconveniente al Procesar la Operación</h2>
            <div class="alert alert-light border text-start small mb-4">
              <p class="mb-2 fw-semibold text-secondary"><i class="bi bi-info-circle me-1"></i>Motivo del inconveniente:</p>
              <p class="mb-2">El sistema no pudo completar la acción en este momento. La información ha sido resguardada para proteger los registros académicos existentes.</p>
              <hr class="my-2">
              <p class="mb-1 fw-semibold text-secondary"><i class="bi bi-check-circle me-1"></i>Qué debes hacer:</p>
              <ul class="mb-0 ps-3">
                <li>Verifica que los datos que intentas guardar no estén duplicados o incompletos.</li>
                <li>Vuelve a intentarlo en unos instantes o regresa a la pantalla anterior.</li>
              </ul>
            </div>
            <div class="d-flex justify-content-center gap-2">
              <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Regresar
              </a>
              <a href="/" class="btn btn-primary">
                <i class="bi bi-house-door me-1"></i>Ir al Inicio
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
    <?php
}
