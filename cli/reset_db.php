<?php
/**
 * Recrea la base y carga los datos de demo (equivale a migrate:fresh --seed).
 * Uso:  php cli/reset_db.php          (usa la configuración de app/config/config.php)
 * La base de datos debe existir (CREATE DATABASE escuela CHARACTER SET utf8mb4;).
 */
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config/config.php';
$db = $config['db'];

$pdo = new PDO(
    "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4",
    $db['username'],
    $db['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

foreach (['schema.sql', 'seed.sql'] as $archivo) {
    $pdo->exec(file_get_contents(BASE_PATH . '/database/' . $archivo));
    echo "OK  {$archivo}\n";
}
echo "Base '{$db['dbname']}' lista.\n";
