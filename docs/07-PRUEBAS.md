# 07 — Pruebas

## 1. Preparar (una sola vez)
Las pruebas hacen peticiones HTTP reales a un servidor de pruebas y revisan la base de datos. Usan una base separada que **se borra y recrea en cada prueba**: `escuela_test`. El código se niega a correr si la base no termina en `_test`.
```bash
mysql -u root -p -e "CREATE DATABASE escuela_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
composer install            # instala PHPUnit y Guzzle (solo para pruebas)
```
Las credenciales de MySQL se toman de `app/config/config.local.php` (la misma configuración que la app).

## 2. Ejecutar
Terminal 1 (servidor de pruebas, apunta a `escuela_test` y sin login):
```bash
# Linux/macOS/Git Bash
APP_ENV=testing php -S 127.0.0.1:8081 -t public public/router.php
# PowerShell
$env:APP_ENV="testing"; php -S 127.0.0.1:8081 -t public public/router.php
```
Terminal 2:
```bash
vendor/bin/phpunit
vendor/bin/phpunit --filter SeguridadTest
vendor/bin/phpunit --filter FlujoCompletoTest
```
| Archivo | Qué prueba |
|---|---|
| CatalogosTest | CRUD unificado de los 4 catálogos, integridad referencial, unicidad, CSRF y tipos válidos |
| FlujoCompletoTest | Catálogos → alumno con foto → nota → ficha → reportes (la información de punta a punta) |
| ValidacionTest | Obligatorios, fecha futura, carrera inexistente, notas inválidas, duplicados, 404 |
| SeguridadTest | SQL injection, XSS (almacenado y reflejado), archivos maliciosos, mass assignment, CSRF, GET que no elimina, cabeceras, cookie HttpOnly, errores sin detalles internos |
| ComportamientoTest | Umbral de aprobación (61 pts), filtros dinámicos de reportes, reemplazo y borrado de fotos, eliminación de notas con token |

**Importante:** las pruebas asumen las rutas y convenciones de docs/02 (por ejemplo la búsqueda con `?q=`, formularios en `/alumnos/nuevo`, token CSRF como campo oculto). Si una falla por una ruta distinta, ajusten la prueba; si falla por un comportamiento real (un XSS que sí se ejecuta, una subida `.php` aceptada), **corrijan el sistema, no la prueba**. Estas pruebas se escribieron sin ejecutarlas contra el proyecto terminado, así que es normal que al inicio alguna requiera un ajuste menor.

## 3. Ver cómo se mueve la información (evidencia para la presentación)
```bash
# PowerShell
$env:LOG_QUERIES="1"; php -S 127.0.0.1:8000 -t public public/router.php
Get-Content var/log/sql.log -Wait
# Bash
LOG_QUERIES=1 php -S 127.0.0.1:8000 -t public public/router.php &  tail -f var/log/sql.log
```
Qué mostrar:
- Cada consulta aparece con `?`/`:parámetro` y sus valores aparte en `binds` → no hay concatenación (protección contra inyección SQL).
- Buscar `' OR '1'='1` en el buscador: aparece como un valor más en `binds`, no como parte del SQL.
- Cuántas consultas hace cada pantalla (si el reporte hace decenas, usar un JOIN en lugar de consultar dentro de un `foreach`).
Apagar `LOG_QUERIES` al publicar (el log contiene datos).

## 4. Revisión rápida de la base
```sql
SELECT COUNT(*) FROM alumnos;
SELECT a.apellidos, c.nombre, n.nota FROM notas n
  JOIN alumnos a ON a.id = n.alumno_id JOIN cursos c ON c.id = n.curso_id ORDER BY n.id DESC LIMIT 10;
```

## 5. Plan de pruebas manuales
| # | Caso | Pasos | Resultado esperado | OK |
|---|---|---|---|---|
| 1 | Crear carrera | Catálogos → Carreras → nuevo | Aparece en lista y en el select de alumnos | ☐ |
| 2 | Carrera duplicada | Repetir el nombre | Mensaje de error, no se duplica | ☐ |
| 3 | Editar / eliminar catálogo | Editar nombre; eliminar uno sin uso | Cambia / desaparece | ☐ |
| 4 | Eliminar catálogo en uso | Eliminar carrera con alumnos | Se bloquea con mensaje (no error 500) | ☐ |
| 5 | Crear alumno completo | Todos los campos + foto jpg | Se guarda, foto visible | ☐ |
| 6 | Alumno sin datos | Enviar vacío | Errores por campo | ☐ |
| 7 | Foto inválida | Subir .php, .pdf, .svg, imagen > 2 MB | Rechazada con mensaje | ☐ |
| 8 | Editar alumno | Cambiar carrera y foto | Cambia; la foto vieja se elimina | ☐ |
| 9 | Buscar alumno | Por nombre y por apellido | Resultados correctos | ☐ |
| 10 | Buscar sin resultados | Texto inexistente | Mensaje "sin resultados" | ☐ |
| 11 | Ingresar nota válida | Alumno → curso/sección/semestre/nota 85 | Se guarda y aparece en historial | ☐ |
| 12 | Nota inválida | -5, 101, texto | Rechazada | ☐ |
| 13 | Nota duplicada | Mismo curso y semestre | Rechazada | ☐ |
| 14 | Aprobado/reprobado | Nota 60 y 61 | 61 aprobado, 60 no (umbral 61) | ☐ |
| 15 | Consulta de alumno | Abrir ficha | Datos, foto e historial completos | ☐ |
| 16 | Reporte por carrera | Elegir carrera | Solo alumnos de esa carrera | ☐ |
| 17 | Reporte de notas | Filtrar carrera + curso + sección | Solo coincidencias | ☐ |
| 18 | Reporte vacío | Filtro sin datos | Tabla vacía con mensaje, sin error | ☐ |
| 19 | Datos precargados | `php cli/reset_db.php` | Sistema con datos de demo | ☐ |
| 20 | Persistencia | Reiniciar servidor | Los datos siguen | ☐ |

## 6. Pruebas de seguridad manuales
Escribir estos textos en nombres, buscador y catálogos. Esperado: se muestran como **texto**, sin ejecutar nada y sin error 500.
| Entrada | Esperado |
|---|---|
| `<script>alert(1)</script>` | Texto literal, sin alerta |
| `<img src=x onerror=alert(1)>` | Texto literal |
| `' OR '1'='1` en el buscador | Sin resultados (no lista todo) |
| `'; DROP TABLE alumnos; --` | Sin efecto, tabla intacta |
| `1 OR 1=1` en filtros de reporte | Validación o sin resultados |
| `shell.php` renombrado a `.jpg` | Rechazado |
| Borrar el campo oculto del token (Inspeccionar) y enviar | 403 |
| Abrir `/alumnos/99999` y `/alumnos/abc` | 404 |
| Forzar un error con `APP_DEBUG=0` | Página genérica, sin traza |

Guardar capturas: sirven de anexo del PDF y para la presentación.

## 7. Registro de resultados
| Fecha | Prueba | Resultado | Observación / corrección |
|---|---|---|---|
| 2026-10-12 | scripts/auditoria.sh | OK (0 hallazgos) | Validación estática de seguridad limpia |
| 2026-10-12 | CatalogosTest | OK (14/14 tests, 60 assertions) | Módulo de Catálogos (carreras, cursos, secciones, semestres) |
| 2026-10-12 | FlujoCompletoTest | OK (1/1 test, 36 assertions) | Flujo e2e completo de alumno y notas |
| 2026-10-12 | ValidacionTest | OK (11/11 tests, 41 assertions) | Validación de campos y límites de datos |
| 2026-10-12 | SeguridadTest | OK (18/18 tests, 67 assertions) | XSS, CSRF, SQL Injection, MIME-spoofing |
| 2026-10-12 | ComportamientoTest | OK (14/14 tests, 98 assertions) | Umbral de aprobación (61), filtros de reportes y fotos |
| 2026-10-12 | AtaquesTest (Red Team) | OK (57/57 tests, 515 assertions) | Robustez, fuzzing, fijación de sesión, headers y DoS local |
| 2026-10-12 | **Suite Completa (PHPUnit)** | **OK (115/115 tests, 817 assertions)** | **100% de cobertura y pruebas superadas sin errores** |

