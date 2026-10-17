# 02 — Arquitectura (Phalcon MVC)

Patrón MVC de Phalcon con tres bloques separados.

## BACKEND (lógica y datos)
```
app/
├── config/
│   ├── config.php        Configuración (entorno, BD, auth)
│   ├── loader.php        Autoload de namespaces
│   ├── services.php      Contenedor DI: router, vistas, BD, sesión, flash
│   └── routes.php        TODAS las rutas
├── controllers/          Delgados: reciben request, validan, llaman a modelos/library, devuelven vista o redirección
│   ├── BaseController.php      CSRF + login opcional
│   ├── ErrorController.php     404 / 403 / 401
│   ├── IndexController.php
│   ├── AlumnosController.php
│   ├── NotasController.php
│   ├── CatalogosController.php (carreras|cursos|secciones|semestres con lista blanca)
│   └── ReportesController.php
├── models/               Alumno, Carrera, Curso, Seccion, Semestre, Nota (Phalcon\Mvc\Model)
└── library/
    ├── Http/SecurityHeaders.php
    ├── Uploads/FotoUploader.php
    └── Reportes/ReporteService.php   (consultas de reportes con SQL enlazado)
database/
├── schema.sql            Crea las tablas (reemplaza migraciones)
└── seed.sql              Datos de demo (reemplaza seeders)
cli/reset_db.php          schema + seed en un comando
```

## FRONTEND (presentación)
```
app/views/
├── index.volt            Layout principal (navbar, flash, {{ content() }})
├── index/index.volt      Inicio
├── error/                notFound, forbidden, unauthorized
├── alumnos/              index, nuevo, editar, ver (ficha + historial)
├── notas/                buscar, nueva
├── catalogos/            index, nuevo, editar (compartidas por los 4 catálogos)
├── reportes/             alumnos-por-carrera, notas
└── partials/             tabla, errores, token CSRF, paginación
public/
├── index.php             Front controller
├── router.php            Para php -S
├── uploads/              Fotos (con .htaccess que bloquea ejecución)
└── (css/js propios si hacen falta; Bootstrap por CDN)
```
Volt: `{{ var|e }}`, `{% for x in lista %}`, `{% if %}`, `{{ url('ruta') }}`. Sin lógica de negocio en vistas.

## ESTRUCTURA / SOPORTE
```
README.md  docs/  scripts/auditoria.sh  tests/  composer.json (solo pruebas)
var/cache/volt  var/log     Caché de plantillas y registros
app/config/config.local.php Credenciales (NO versionar, NO incluir en el ZIP)
```

## Flujo de una petición
`public/index.php` → Router (routes.php) → Dispatcher → `BaseController::beforeExecuteRoute` (login/CSRF) → Acción del controlador (validación, modelos) → Vista Volt → Respuesta + cabeceras de seguridad

## Rutas
| Método | Ruta | Acción |
|---|---|---|
| GET | `/alumnos` (`?q=`) | Listado con búsqueda |
| GET | `/alumnos/nuevo` · POST `/alumnos/crear` | Formulario / crear |
| GET | `/alumnos/{id}` | Ficha: datos + historial de notas |
| GET | `/alumnos/{id}/editar` · POST `/alumnos/{id}/actualizar` | Editar |
| POST | `/alumnos/{id}/eliminar` | Eliminar |
| GET | `/notas/buscar` (`?q=`) | Buscar alumno para ingresar notas |
| GET | `/alumnos/{id}/notas/nueva` · POST `/alumnos/{id}/notas/crear` | Ingreso de nota |
| POST | `/notas/{id}/eliminar` | Eliminar nota |
| GET/POST | `/catalogos/{carreras\|cursos\|secciones\|semestres}` + `/nuevo` `/crear` `/{id}/editar` `/{id}/actualizar` `/{id}/eliminar` | CRUD de catálogos |
| GET | `/reportes/alumnos-por-carrera` | Reporte general por carrera |
| GET | `/reportes/notas` (`?carrera_id=&curso_id=&seccion_id=`) | Notas por carrera, curso y sección |

Convenciones de formularios: el `action` del `<form>` es la ruta POST (`/alumnos/crear`), y la página que contiene el formulario es la ruta GET `nuevo`/`nueva`. Las pruebas automáticas dependen de esto.
