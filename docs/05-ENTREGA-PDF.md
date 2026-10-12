# 05 — Índice del documento PDF

1. **Portada** (universidad, facultad, curso, integrantes, fecha, framework: Phalcon)
2. **Manual técnico de publicación**
   - Prerrequisitos: PHP, extensión Phalcon (versión, TS/NTS), MySQL, Composer (solo pruebas)
   - Instalación de la extensión paso a paso (README)
   - Configuración (`config.local.php`) y creación de la base (`schema.sql`, `seed.sql`, `cli/reset_db.php`)
   - Publicación web (ngrok / hosting) y variables de entorno
3. **Pasos utilizados del framework**
   - Estructura MVC, loader, servicios DI, router, controladores, modelos, Volt, validación, sesión/CSRF, subida de archivos
4. **Recomendaciones para usar Phalcon**
5. **Comentarios del grupo** (facilidad/dificultad)
6. **Seguridad aplicada y pruebas** (resumen de docs/06 y 07, capturas de pruebas)
7. **Anexos** (capturas de pantalla, diagrama de BD)

## Puntos para los comentarios sobre Phalcon (completar con la experiencia real del grupo)
- Instalación: es una extensión en C; hay que conseguir el DLL exacto (versión de PHP, TS/NTS). Es el paso más delicado.
- Sin generadores ni migraciones integrados: el esquema se maneja con SQL propio y las pantallas se escriben a mano (más trabajo manual que Laravel).
- Base de datos: solo MySQL, PostgreSQL y SQLite (no SQL Server).
- Ventajas: rendimiento y bajísimo consumo de memoria (código C en memoria compartida), componentes (router, validación, Volt, DI) altamente integrados y desacoplados, sin dependencias pesadas en producción (vendor/ no requerido en runtime).
- Documentación: concisa y clara, aunque con menor volumen de ejemplos comunitarios en comparación con Laravel.

## Notas para el PDF (llenar durante el desarrollo)

### 1. Módulo de Catálogos (carreras, cursos, secciones, semestres)
- **Dificultades:**
  - En Laravel se tenían 4 controladores y 12 vistas Blade dispersas con lógica duplicada para cada catálogo.
  - La validación en Laravel dependía de FormRequests con reglas específicas de Eloquent (`Rule::unique`), inexistentes en Phalcon.
  - El motor Blade usa directivas (`@extends`, `@section`, `@error`, `@method('DELETE')`) que no tienen equivalencia directa en Volt.
- **Soluciones:**
  - Consolidación en un único `CatalogosController` con lista blanca de tipos (`carreras`, `cursos`, `secciones`, `semestres`) y metadatos dinámicos, reduciendo las 12 vistas a 3 plantillas Volt compartidas (`index.volt`, `nuevo.volt`, `editar.volt`).
  - Validación nativa con `Phalcon\Filter\Validation` (`PresenceOf`, `StringLength`) y consultas parametrizadas (`:nombre:`, `bind`) para validar unicidad sin riesgo de inyección SQL.
  - Protección de integridad referencial: verificación de registros dependientes (`alumnos` y `notas`) antes de eliminar, informando al usuario mediante `flashSession->error()` y previniendo errores 500 por violaciones de clave foránea.
  - Eliminación siempre por método POST con token CSRF embebido mediante `security.getTokenKey()` y `security.getToken()`.

### 2. Módulo de Alumnos
- **Dificultades:**
  - Manejo de subida y almacenamiento de fotografías en Laravel (`Storage::disk('public')`) vs manejo en PHP/Phalcon.
  - Riesgo de subida de archivos maliciosos (ej. webshells `.php` o dobles extensiones `.php.jpg`).
  - Acumulación de archivos huérfanos en disco al reemplazar fotos en edición o al eliminar alumnos.
  - Validación de fechas para evitar fechas de nacimiento futuras o formatos inválidos.
- **Soluciones:**
  - Implementación del servicio `FotoUploader` (`app/library/Uploads/FotoUploader.php`), que valida el tipo MIME real usando `finfo` (solo `image/jpeg` e `image/png`), limita el peso a un máximo estricto de 2 MB y genera nombres pseudoaleatorios únicos (`bin2hex(random_bytes(16))`).
  - Protección por `.htaccess` en el directorio `public/uploads/` para desactivar la ejecución de cualquier script (`php_flag engine off`, `SetHandler default-handler`).
  - Limpieza automática del sistema de archivos: al actualizar la foto o eliminar el alumno, la foto anterior se remueve con `unlink()`.
  - Buscador insensible a mayúsculas con coincidencias parciales (`LIKE :q:`) que busca sobre nombres o apellidos, enlazando parámetros de forma segura.

### 3. Módulo de Notas
- **Dificultades:**
  - Flujo de negocio: el requerimiento estipula que primero debe buscarse el alumno y luego ingresar sus calificaciones, previniendo notas sin alumno asignado.
  - Validación de rango numérico (0 a 100) y regla de unicidad compuesta: un alumno no puede registrar dos notas para el mismo curso y semestre.
  - Umbral académico: visualización clara del estado Aprobado/Reprobado basado en la nota mínima de 61 puntos.
- **Soluciones:**
  - Separación en pantalla de búsqueda (`/notas/buscar`) con redirección a la ficha del alumno o al formulario guiado de calificación (`/alumnos/{id}/notas/nueva`).
  - Validación con `Numericality` e `InclusiveBetween` (`minimum: 0`, `maximum: 100`) y validación previa en base de datos para impedir duplicados en la tupla `(alumno_id, curso_id, semestre_id)`.
  - Presentación en Volt con badges condicionales de Bootstrap: verde (`badge bg-success`) para notas >= 61 y rojo (`badge bg-danger`) para notas reprobadas.

### 4. Módulo de Reportes
- **Dificultades:**
  - Optimización de consultas para evitar problemas de N+1 queries al listar alumnos y sus carreras o notas con sus múltiples relaciones (alumno, curso, semestre, sección).
  - Manejo de filtros opcionales dinámicos (carrera, curso, sección).
- **Soluciones:**
  - Servicio `ReporteService` (`app/library/Reportes/ReporteService.php`) utilizando Phalcon PHQL y Models con `belongsTo` optimizados, trayendo la información en una única consulta estructurada.
  - Formulario de filtros con preservación del estado seleccionado en los `<select>` de Volt (`{% if selected %} selected {% endif %}`).

### 5. Seguridad y Rendimiento
- **Cabeceras HTTP de Seguridad:** Implementación de `SecurityHeaders` con `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin` y `Content-Security-Policy`.
- **Protección contra CSRF:** Validación de token CSRF en `BaseController::beforeExecuteRoute()` para todas las peticiones POST, retornando 403 Forbidden si el token falta o es inválido.
- **Auditoría de Código:** Script automatizado `scripts/auditoria.sh` que analiza estáticamente el código fuente verificando ausencia de inyecciones SQL, concatenación no segura, superglobales directas y escape `|e` en Volt.
- **Pruebas Automatizadas:** 44 pruebas de integración de caja negra con PHPUnit 11 y Guzzle (`CatalogosTest`, `FlujoCompletoTest`, `ValidacionTest`, `SeguridadTest`) ejecutadas sobre base de datos de pruebas `escuela_test`, con un 100% de éxito (204 aserciones).

### Comandos de Referencia:
- `php cli/reset_db.php` — Reinicia el esquema y carga los datos de demostración.
- `bash scripts/auditoria.sh` — Auditoría estática de seguridad y buenas prácticas.
- `vendor/bin/phpunit` — Ejecución de la suite completa de pruebas automatizadas.
- `php -S 127.0.0.1:8000 -t public public/router.php` — Servidor de desarrollo local.
