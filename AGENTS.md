# AGENTS.md — Reglas del proyecto (leer SIEMPRE antes de editar)

## Contexto
Sistema web para registrar alumnos de bachillerato, sus cursos aprobados y notas.
Framework: **Phalcon 5.x** (extensión de PHP) · PHP 8.2+ · **MySQL/MariaDB** · plantillas **Volt** + Bootstrap 5.
Proyecto académico (UMG, Desarrollo Web). Entrega: 16/Oct/2026. Presentación: 17/Oct/2026.
Nota: el proyecto empezó en Laravel y se cambió a Phalcon por indicación del docente. Si aparece código o comandos de Laravel (artisan, Blade, Eloquent, migrations), NO usarlos: ver docs/08-GUIA-PHALCON.md.

## Documentos de referencia
- docs/01-REQUISITOS.md → qué pide el docente
- docs/02-ARQUITECTURA.md → capas, carpetas y rutas
- docs/03-BASE-DE-DATOS.md → tablas (database/schema.sql y seed.sql)
- docs/04-TAREAS.md → plan y checklist (actualizar al terminar cada tarea)
- docs/05-ENTREGA-PDF.md → índice del PDF final
- docs/06-SEGURIDAD.md → reglas de seguridad
- docs/07-PRUEBAS.md → pruebas automáticas y manuales
- docs/08-GUIA-PHALCON.md → patrones de código y equivalencias Laravel → Phalcon

## Reglas
1. Capas separadas (ver 02): controladores delgados, lógica en app/library, datos en app/models, presentación solo en app/views (.volt).
2. Todo controlador extiende `BaseController` (aplica login opcional y verificación CSRF en todo POST). Excepción: `ErrorController`.
3. Rutas SOLO en app/config/routes.php. Todo lo que modifica datos es POST (`addPost`); nunca GET.
4. Consultas SIEMPRE con parámetros enlazados (`:param:` + `bind`, o `?`/`:param` en SQL crudo). Prohibido concatenar variables en SQL o en `conditions`.
5. En Volt, toda variable dinámica se imprime con `|e`: `{{ alumno.nombres|e }}`. Prohibido imprimir datos de usuario sin escapar.
6. Todo formulario POST incluye el token CSRF (ver 08). Todo POST valida con `Phalcon\Filter\Validation` antes de guardar.
7. Asignación masiva: usar `$modelo->assign($datos, ['campo1', 'campo2'])` con lista blanca; nunca `assign($_POST)`.
8. Fotos: solo con `App\Library\Uploads\FotoUploader` (JPG/PNG, máx. 2 MB, nombre aleatorio).
9. Si la validación falla: volver a mostrar el formulario con `conErrores()` (HTTP 422) y no guardar. Si todo sale bien: redirigir (302) con mensaje flash.
10. El esquema vive en database/schema.sql y los datos de demo en database/seed.sql. Cualquier cambio de BD se hace ahí (y se documenta en docs/03). `php cli/reset_db.php` debe dejar el sistema listo.
11. Umbral de aprobación: 61 (config `app.notaAprobacion`); no escribir el número suelto en el código.
12. No agregar dependencias de Composer a la app (Composer solo para pruebas). No usar otros frameworks ni ORM.
13. Al terminar una tarea: marcarla en docs/04-TAREAS.md y anotar en docs/05-ENTREGA-PDF.md pasos/dificultades relevantes (sirven para el PDF).
14. Antes de dar algo por terminado: `php cli/reset_db.php`, correr `bash scripts/auditoria.sh` y las pruebas (docs/07), y probar el flujo en el navegador.
15. Si no estás seguro de una API de Phalcon, consultar https://docs.phalcon.io (versión 5.x) en lugar de inventarla.
