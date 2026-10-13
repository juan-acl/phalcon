<?php

/** @var \Phalcon\Mvc\Router $router */

// Solo POST modifica datos. Un GET a una ruta de modificación no existe -> 404.
$router->notFound(['controller' => 'error', 'action' => 'notFound']);

$router->addGet('/', ['controller' => 'index', 'action' => 'index']);

// ---- Alumnos ----
$router->addGet('/alumnos', ['controller' => 'alumnos', 'action' => 'index']);
$router->addGet('/alumnos/nuevo', ['controller' => 'alumnos', 'action' => 'nuevo']);
$router->addPost('/alumnos/crear', ['controller' => 'alumnos', 'action' => 'crear']);
$router->addGet('/alumnos/{id:[0-9]+}', ['controller' => 'alumnos', 'action' => 'ver']);
$router->addGet('/alumnos/{id:[0-9]+}/ficha', ['controller' => 'alumnos', 'action' => 'ficha']);
$router->addGet('/alumnos/{id:[0-9]+}/editar', ['controller' => 'alumnos', 'action' => 'editar']);
$router->addPost('/alumnos/{id:[0-9]+}/actualizar', ['controller' => 'alumnos', 'action' => 'actualizar']);
$router->addPost('/alumnos/{id:[0-9]+}/eliminar', ['controller' => 'alumnos', 'action' => 'eliminar']);

// ---- Notas ----
$router->addGet('/notas/buscar', ['controller' => 'notas', 'action' => 'buscar']);
$router->addGet('/alumnos/{id:[0-9]+}/notas/nueva', ['controller' => 'notas', 'action' => 'nueva']);
$router->addPost('/alumnos/{id:[0-9]+}/notas/crear', ['controller' => 'notas', 'action' => 'crear']);
$router->addGet('/notas/{id:[0-9]+}/editar', ['controller' => 'notas', 'action' => 'editar']);
$router->addPost('/notas/{id:[0-9]+}/actualizar', ['controller' => 'notas', 'action' => 'actualizar']);
$router->addPost('/notas/{id:[0-9]+}/eliminar', ['controller' => 'notas', 'action' => 'eliminar']);

// ---- Catálogos (un solo controlador con lista blanca de tipos) ----
$tipos = '{tipo:(carreras|cursos|secciones|semestres)}';
$router->addGet("/catalogos/$tipos", ['controller' => 'catalogos', 'action' => 'index']);
$router->addGet("/catalogos/$tipos/nuevo", ['controller' => 'catalogos', 'action' => 'nuevo']);
$router->addPost("/catalogos/$tipos/crear", ['controller' => 'catalogos', 'action' => 'crear']);
$router->addGet("/catalogos/$tipos/{id:[0-9]+}/editar", ['controller' => 'catalogos', 'action' => 'editar']);
$router->addPost("/catalogos/$tipos/{id:[0-9]+}/actualizar", ['controller' => 'catalogos', 'action' => 'actualizar']);
$router->addPost("/catalogos/$tipos/{id:[0-9]+}/eliminar", ['controller' => 'catalogos', 'action' => 'eliminar']);

// ---- Reportes ----
$router->addGet('/reportes/alumnos-por-carrera', ['controller' => 'reportes', 'action' => 'alumnosPorCarrera']);
$router->addGet('/reportes/notas', ['controller' => 'reportes', 'action' => 'notas']);

// ---- Autenticación y Cuenta ----
$router->addGet('/login', ['controller' => 'auth', 'action' => 'login']);
$router->addPost('/login', ['controller' => 'auth', 'action' => 'login']);
$router->addPost('/logout', ['controller' => 'auth', 'action' => 'logout']);
$router->addGet('/cuenta/clave', ['controller' => 'cuenta', 'action' => 'clave']);
$router->addPost('/cuenta/clave', ['controller' => 'cuenta', 'action' => 'clave']);
