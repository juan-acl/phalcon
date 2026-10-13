<?php
/**
 * Crea o actualiza usuarios de demostración con contraseñas seguras (password_hash).
 * Uso: php cli/seed_usuarios.php
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

echo "Sembrando usuarios en {$db['dbname']}...\n";

// 1. Director
$passDirector = getenv('DIRECTOR_PASS') ?: 'Director123!';
$hashDirector = password_hash($passDirector, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO usuarios (usuario, password_hash, rol, activo, debe_cambiar_clave)
    VALUES ('director', :hash, 'director', 1, 0)
    ON DUPLICATE KEY UPDATE password_hash = :hash_up, activo = 1
");
$stmt->execute(['hash' => $hashDirector, 'hash_up' => $hashDirector]);
echo "  [OK] Usuario 'director' (clave: {$passDirector})\n";

// 2. Profesores
$profesores = $pdo->query("SELECT id, nombres, apellidos FROM profesores ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($profesores as $idx => $p) {
    $uName = 'profesor' . ($idx + 1);
    $pass = 'Profesor123!';
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (usuario, password_hash, rol, profesor_id, activo, debe_cambiar_clave)
        VALUES (:usuario, :hash, 'profesor', :pid, 1, 0)
        ON DUPLICATE KEY UPDATE password_hash = :hash_up, activo = 1, profesor_id = :pid_up
    ");
    $stmt->execute([
        'usuario' => $uName,
        'hash'    => $hash,
        'pid'     => $p['id'],
        'hash_up' => $hash,
        'pid_up'  => $p['id']
    ]);
    echo "  [OK] Usuario '{$uName}' -> Profesor {$p['nombres']} {$p['apellidos']} (clave: {$pass})\n";
}

// 3. Alumnos
$alumnos = $pdo->query("SELECT id, nombres, apellidos FROM alumnos ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($alumnos as $idx => $a) {
    $uName = 'alumno' . ($idx + 1);
    $pass = 'Alumno123!';
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (usuario, password_hash, rol, alumno_id, activo, debe_cambiar_clave)
        VALUES (:usuario, :hash, 'alumno', :aid, 1, 0)
        ON DUPLICATE KEY UPDATE password_hash = :hash_up, activo = 1, alumno_id = :aid_up
    ");
    $stmt->execute([
        'usuario' => $uName,
        'hash'    => $hash,
        'aid'     => $a['id'],
        'hash_up' => $hash,
        'aid_up'  => $a['id']
    ]);
    echo "  [OK] Usuario '{$uName}' -> Alumno {$a['nombres']} {$a['apellidos']} (clave: {$pass})\n";
}

echo "Usuarios sembrados con éxito.\n";
