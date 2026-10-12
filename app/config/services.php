<?php

/** @var \Phalcon\Di\FactoryDefault $di */
/** @var array $config */

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Html\Escaper;
use Phalcon\Logger\Adapter\Stream as LoggerStream;
use Phalcon\Logger\Logger;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Session\Adapter\Stream as SessionStream;
use Phalcon\Session\Manager as SessionManager;

$di->setShared('config', fn() => $config);

// ---- Router (sin rutas por defecto: solo las de routes.php) ----
$di->setShared('router', function () {
    $router = new Router(false);
    $router->removeExtraSlashes(true);
    require APP_PATH . '/config/routes.php';
    return $router;
});

// ---- Dispatcher ----
$di->setShared('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('App\\Controllers');
    return $dispatcher;
});

// ---- URL ----
$di->setShared('url', function () use ($config) {
    $url = new Url();
    $url->setBaseUri($config['app']['baseUri']);
    return $url;
});

// ---- Vistas (Volt) ----
$di->setShared('voltService', function ($view) use ($di, $config) {
    $volt = new Volt($view, $di);
    $volt->setOptions([
        'path'      => $config['app']['cacheDir'] . '/volt/',
        'separator' => '_',
        'always'    => $config['app']['debug'],
    ]);
    return $volt;
});

$di->setShared('view', function () {
    $view = new View();
    $view->setViewsDir(APP_PATH . '/views/');
    $view->registerEngines(['.volt' => 'voltService']);
    return $view;
});

// ---- Base de datos (MySQL/MariaDB) ----
$di->setShared('db', function () use ($config) {
    $db = new Mysql($config['db']);

    // Evidencia para la presentación: registra el SQL y sus parámetros (LOG_QUERIES=1)
    if ($config['app']['logQueries']) {
        $logger = new Logger('sql', ['main' => new LoggerStream(BASE_PATH . '/var/log/sql.log')]);
        $events = new EventsManager();
        $events->attach('db:beforeQuery', function (Event $event, $connection) use ($logger) {
            $logger->info($connection->getSQLStatement() . ' | binds: ' . json_encode($connection->getSQLVariables()));
        });
        $db->setEventsManager($events);
    }

    return $db;
});

// ---- Sesión (necesaria para CSRF y mensajes flash) ----
$di->setShared('session', function () use ($di) {
    $savePath = sys_get_temp_dir();
    $session = new SessionManager();
    $session->setAdapter(new SessionStream(['savePath' => $savePath]));

    // Detección de fijación de sesión (Strict Mode):
    // Si el cliente envía un PHPSESSID pero dicho archivo no existe aún en el servidor,
    // se trata de un identificador no inicializado o impuesto. Regeneramos la sesión.
    $nombreCookie = session_name();
    $idEnviado = null;
    if (!empty($_SERVER['HTTP_COOKIE']) && preg_match('/(?:^|;\s*)' . preg_quote($nombreCookie, '/') . '=([^;]+)/', $_SERVER['HTTP_COOKIE'], $m)) {
        $idEnviado = urldecode($m[1]);
    }
    $esFijacion = false;
    if ($idEnviado) {
        $archivoSesion = rtrim($savePath, '/\\') . DIRECTORY_SEPARATOR . $idEnviado;
        if (!preg_match('/^[a-zA-Z0-9,-]{20,128}$/', (string) $idEnviado) || !is_file($archivoSesion)) {
            $esFijacion = true;
        }
    }

    $session->start();

    if ($esFijacion) {
        $session->regenerateId(true);
        $nuevoId = $session->getId();
        $params = session_get_cookie_params();
        setcookie(
            $nombreCookie,
            $nuevoId,
            [
                'expires'  => $params['lifetime'] ? time() + $params['lifetime'] : 0,
                'path'     => $params['path'] ?: '/',
                'domain'   => $params['domain'] ?: '',
                'secure'   => $params['secure'] ?: false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    return $session;
});

// ---- Mensajes flash con clases Bootstrap ----
$di->setShared('flashSession', function () use ($di) {
    $flash = new FlashSession(new Escaper(), $di->getShared('session'));
    $flash->setCssClasses([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ]);
    return $flash;
});
