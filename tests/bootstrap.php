<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Las pruebas usan SIEMPRE la base de pruebas (escuela_test).
putenv('APP_ENV=testing');
define('BASE_PATH', dirname(__DIR__));
