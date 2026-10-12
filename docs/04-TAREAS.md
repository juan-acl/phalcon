# 04 — Plan de tareas (marcar al terminar)

Cambio de framework: Laravel → **Phalcon**. Lo ya hecho en Laravel sirve como referencia de pantallas, validaciones y datos (ver docs/08 para la conversión). Entrega: 16/Oct/2026 · Presentación: 17/Oct/2026.

## Dom 11/Oct — Instalación y esqueleto
- [x] Instalar extensión Phalcon (README) y verificar `php --ri phalcon`
- [x] Instalar/usar MySQL; crear BD `escuela` y `escuela_test` (utilizando `registro_alumnos` existente)
- [x] Copiar este esqueleto; crear `config.local.php`
- [x] `php cli/reset_db.php` sin errores
- [x] `php -S 127.0.0.1:8000 -t public public/router.php` muestra la página de inicio

## Lun 12/Oct — Modelos y catálogos
- [x] Modelos: Carrera, Curso, Seccion, Semestre, Alumno, Nota (con relaciones)
- [x] CatalogosController + vistas compartidas (listar, crear, editar, eliminar)
- [x] Bloquear eliminación de catálogos en uso con mensaje claro

## Mar 13/Oct — Alumnos
- [x] CRUD de alumnos con select de carrera
- [x] Subida de foto con FotoUploader (+ borrar foto al editar/eliminar)
- [x] Listado con miniatura y buscador (`?q=`)

## Mié 14/Oct — Notas y reportes
- [x] Buscador de alumno (`/notas/buscar`) y formulario de nota
- [x] Validación: rango 0–100, duplicados
- [x] Ficha del alumno con historial (aprobado/reprobado según umbral)
- [x] Reporte de alumnos por carrera
- [x] Reporte de notas por carrera, curso y sección (con filtros)

## Jue 15/Oct — Seguridad, pruebas y demo
- [x] Token CSRF en todos los formularios, headers de seguridad activos
- [x] `bash scripts/auditoria.sh` sin hallazgos
- [x] Pruebas automáticas en verde (docs/07) y plan manual completado
- [x] Login opcional preparado para entorno con URL pública
- [x] Datos de demo listos y flujo completo probado

## Vie 16/Oct — Entrega
- [ ] Redactar PDF (docs/05) con capturas
- [ ] ZIP: PDF + código (sin `vendor/`, sin `config.local.php`, sin `var/log`, sin fotos de prueba)
- [ ] Subir a la plataforma antes de la fecha límite

## Sáb 17/Oct — Presentación
- [ ] Ensayar; cada integrante debe poder explicar el código
- [ ] Tener a mano la URL (ngrok o local) y `LOG_QUERIES=1` para mostrar las consultas
