<?php

/**
 * Configuración central. Valores por variable de entorno o por app/config/config.local.php
 * (config.local.php NO se sube a git ni al ZIP).
 */
$env = getenv('APP_ENV') ?: 'development';

$config = [
    'app' => [
        'env'        => $env,
        'debug'      => (getenv('APP_DEBUG') ?: '0') === '1',
        'baseUri'    => '/',
        'uploadsDir' => BASE_PATH . '/public/uploads',
        'cacheDir'   => BASE_PATH . '/var/cache',
        'logQueries' => (getenv('LOG_QUERIES') ?: '0') === '1',
        'notaAprobacion' => 61,
    ],
    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'dbname'   => getenv('DB_NAME') ?: 'escuela',
        'charset'  => 'utf8mb4',
    ],
    'auth' => [
        'enabled'  => (getenv('AUTH_ENABLED') ?: '0') === '1',
        'user'     => getenv('ADMIN_USER') ?: 'admin',
        // Generar con: php -r "echo password_hash('tu_clave', PASSWORD_DEFAULT);"
        'passHash' => getenv('ADMIN_PASS_HASH') ?: '',
    ],
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}

// El entorno de pruebas SIEMPRE usa su propia base y sin login
if ($env === 'testing') {
    $config['db']['dbname'] = getenv('DB_NAME_TEST') ?: 'escuela_test';
    $config['auth']['enabled'] = false;
    $config['app']['debug'] = false;
}

return $config;
