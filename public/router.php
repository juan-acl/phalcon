<?php
// Uso: php -S 127.0.0.1:8000 -t public public/router.php
// Sirve archivos estáticos existentes (css, fotos) y manda el resto al front controller.
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$archivo = __DIR__ . $ruta;

// Bloquear acceso estático a archivos ocultos (.htaccess, .env) y archivos de código/datos
if (preg_match('/(^|\/)\./', $ruta) || preg_match('/\.(php|phtml|phar|sql|lock|json|xml|md|log|ini|sh|bat)$/i', $archivo)) {
    require __DIR__ . '/index.php';
    return;
}

if ($ruta !== '/' && is_file($archivo)) {
    return false; // lo sirve el servidor embebido
}

require __DIR__ . '/index.php';
