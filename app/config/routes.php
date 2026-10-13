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
$router->addPost('/catalogos/semestres/{id:[0-9]+}/cerrar', ['controller' => 'catalogos', 'action' => 'cerrar', 'tipo' => 'semestres']);
$router->addPost('/catalogos/semestres/{id:[0-9]+}/reabrir', ['controller' => 'catalogos', 'action' => 'reabrir', 'tipo' => 'semestres']);

// ---- Tablero de Control ----
$router->addGet('/tablero', ['controller' => 'tablero', 'action' => 'index']);

// ---- Docentes (Profesores) ----
$router->addGet('/profesores', ['controller' => 'profesores', 'action' => 'index']);
$router->addGet('/profesores/nuevo', ['controller' => 'profesores', 'action' => 'nuevo']);
$router->addPost('/profesores/crear', ['controller' => 'profesores', 'action' => 'crear']);
$router->addGet('/profesores/{id:[0-9]+}/editar', ['controller' => 'profesores', 'action' => 'editar']);
$router->addPost('/profesores/{id:[0-9]+}/actualizar', ['controller' => 'profesores', 'action' => 'actualizar']);
$router->addPost('/profesores/{id:[0-9]+}/eliminar', ['controller' => 'profesores', 'action' => 'eliminar']);

// ---- Clases ----
$router->addGet('/clases', ['controller' => 'clases', 'action' => 'index']);
$router->addGet('/clases/nueva', ['controller' => 'clases', 'action' => 'nueva']);
$router->addPost('/clases/crear', ['controller' => 'clases', 'action' => 'crear']);
$router->addGet('/clases/{id:[0-9]+}/editar', ['controller' => 'clases', 'action' => 'editar']);
$router->addPost('/clases/{id:[0-9]+}/actualizar', ['controller' => 'clases', 'action' => 'actualizar']);
$router->addPost('/clases/{id:[0-9]+}/eliminar', ['controller' => 'clases', 'action' => 'eliminar']);

// ---- Inscripciones ----
$router->addGet('/inscripciones', ['controller' => 'inscripciones', 'action' => 'index']);
$router->addGet('/inscripciones/nueva', ['controller' => 'inscripciones', 'action' => 'nueva']);
$router->addPost('/inscripciones/crear', ['controller' => 'inscripciones', 'action' => 'crear']);
$router->addPost('/inscripciones/{id:[0-9]+}/eliminar', ['controller' => 'inscripciones', 'action' => 'eliminar']);

// ---- Auditoría ----
$router->addGet('/auditoria', ['controller' => 'auditoria', 'action' => 'index']);

// ---- Portal Docente ----
$router->addGet('/profesor', ['controller' => 'profesor', 'action' => 'index']);
$router->addGet('/profesor/clases', ['controller' => 'profesor', 'action' => 'clases']);
$router->addGet('/profesor/clases/{id:[0-9]+}', ['controller' => 'profesor', 'action' => 'clase']);
$router->addPost('/profesor/clases/{id:[0-9]+}/notas', ['controller' => 'profesor', 'action' => 'notas']);
$router->addPost('/profesor/clases/{id:[0-9]+}/publicar', ['controller' => 'profesor', 'action' => 'publicar']);

// ---- Portal Estudiante ----
$router->addGet('/mi/perfil', ['controller' => 'alumno', 'action' => 'perfil']);
$router->addGet('/mi/notas', ['controller' => 'alumno', 'action' => 'notas']);
$router->addGet('/mi/ficha', ['controller' => 'alumno', 'action' => 'perfil']);

// ---- Reportes ----
$router->addGet('/reportes/alumnos-por-carrera', ['controller' => 'reportes', 'action' => 'alumnosPorCarrera']);
$router->addGet('/reportes/notas', ['controller' => 'reportes', 'action' => 'notas']);

// ---- Autenticación y Cuenta ----
$router->addGet('/login', ['controller' => 'auth', 'action' => 'login']);
$router->addPost('/login', ['controller' => 'auth', 'action' => 'login']);
$router->addPost('/logout', ['controller' => 'auth', 'action' => 'logout']);
$router->addGet('/cuenta/clave', ['controller' => 'cuenta', 'action' => 'clave']);
$router->addPost('/cuenta/clave', ['controller' => 'cuenta', 'action' => 'clave']);
