# 08 — Guía Phalcon (equivalencias y patrones)

Esta guía sirve para pasar lo hecho en Laravel a Phalcon y como referencia de código para el desarrollo. Es un punto de partida: ante la duda, verificar en https://docs.phalcon.io (versión 5.x).

## 1. Equivalencias Laravel → Phalcon
| Laravel | Phalcon (este proyecto) |
|---|---|
| `php artisan serve` | `php -S 127.0.0.1:8000 -t public public/router.php` |
| Migraciones + seeders | `database/schema.sql` + `database/seed.sql` + `php cli/reset_db.php` |
| `routes/web.php` | `app/config/routes.php` (`addGet`, `addPost`) |
| Controller (`make:controller`) | Clase `XxxController` en `app/controllers` con métodos `xxxAction()` |
| Eloquent (`Alumno::where(...)`) | `Alumno::find([...conditions/bind...])`, `findFirst`, `->save()` |
| Relaciones `belongsTo/hasMany` | Mismas, en `initialize()` del modelo con `alias` |
| FormRequest | `Phalcon\Filter\Validation` en el controlador |
| Blade `{{ $x }}` | Volt `{{ x\|e }}` |
| `@foreach` / `@if` | `{% for x in lista %}` / `{% if cond %}` + `{% endfor %}` / `{% endif %}` |
| `@csrf` | `<input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">` |
| `route('alumnos.index')` | `{{ url('alumnos') }}` |
| `@extends('layouts.app')` | Layout principal `app/views/index.volt` con `{{ content() }}` |
| `session()->flash()` | `$this->flashSession->success('...')` |
| `redirect()->route()` | `return $this->response->redirect('alumnos');` |
| `Storage::putFile` + `storage:link` | `FotoUploader` → `public/uploads/` |
| `config('x')` | `$this->config['x']` (servicio `config`) |
| `.env` | Variables de entorno o `app/config/config.local.php` |
| `php artisan test` | `vendor/bin/phpunit` (pruebas HTTP) |

## 2. Cómo convertir las vistas Blade (ejemplo)
```blade
{{-- Blade --}}
@foreach($alumnos as $a)
  <tr><td>{{ $a->nombres }}</td><td><a href="{{ route('alumnos.show', $a) }}">Ver</a></td></tr>
@endforeach
```
```volt
{# Volt #}
{% for a in alumnos %}
  <tr><td>{{ a.nombres|e }}</td><td><a href="{{ url('alumnos/' ~ a.id) }}">Ver</a></td></tr>
{% endfor %}
```
En Volt la concatenación es `~`. Para convertir vistas Blade a Volt se deben seguir las equivalencias de esta sección.

## 3. Controlador de referencia (patrón)
```php
<?php
namespace App\Controllers;

use App\Library\Uploads\FotoUploader;
use App\Models\Alumno;
use App\Models\Carrera;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Filter\Validation\Validator\Date;

class AlumnosController extends BaseController
{
    public function indexAction(): void
    {
        $q = trim((string) $this->request->getQuery('q', null, ''));
        $this->view->setVar('q', $q);
        $this->view->setVar('alumnos', Alumno::find([
            'conditions' => 'nombres LIKE :q: OR apellidos LIKE :q:',
            'bind'       => ['q' => '%' . $q . '%'],
            'order'      => 'apellidos, nombres',
            'limit'      => 200,
        ]));
    }

    public function nuevoAction(): void
    {
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre']));
        $this->view->setVar('old', []);
        $this->view->setVar('errores', []);
    }

    public function crearAction()
    {
        $datos = [
            'nombres'          => trim((string) $this->request->getPost('nombres', null, '')),
            'apellidos'        => trim((string) $this->request->getPost('apellidos', null, '')),
            'fecha_nacimiento' => trim((string) $this->request->getPost('fecha_nacimiento', null, '')),
            'carrera_id'       => (int) $this->request->getPost('carrera_id', null, 0),
        ];

        $v = new Validation();
        $v->add('nombres', new PresenceOf(['message' => 'Los nombres son obligatorios']));
        $v->add('nombres', new StringLength(['max' => 100, 'messageMaximum' => 'Máximo 100 caracteres']));
        $v->add('apellidos', new PresenceOf(['message' => 'Los apellidos son obligatorios']));
        $v->add('apellidos', new StringLength(['max' => 100, 'messageMaximum' => 'Máximo 100 caracteres']));
        $v->add('fecha_nacimiento', new Date(['format' => 'Y-m-d', 'message' => 'Fecha inválida']));

        $errores = [];
        foreach ($v->validate($datos) as $m) {
            $errores[] = $m->getMessage();
        }
        if ($datos['fecha_nacimiento'] !== '' && strtotime($datos['fecha_nacimiento']) >= time()) {
            $errores[] = 'La fecha de nacimiento debe ser pasada';
        }
        if (!Carrera::findFirst(['conditions' => 'id = :id:', 'bind' => ['id' => $datos['carrera_id']]])) {
            $errores[] = 'Selecciona una carrera válida';
        }

        // Foto (opcional)
        $rutaFoto = null;
        if ($this->request->hasFiles(true)) {
            $uploader = new FotoUploader($this->config['app']['uploadsDir']);
            foreach ($this->request->getUploadedFiles(true) as $archivo) {
                if ($archivo->getKey() === 'foto' && $archivo->getError() !== UPLOAD_ERR_NO_FILE) {
                    [$rutaFoto, $errorFoto] = $uploader->guardar($archivo);
                    if ($errorFoto) {
                        $errores[] = $errorFoto;
                    }
                }
            }
        }

        if ($errores) {
            $this->conErrores($errores, $datos);
            $this->view->setVar('carreras', Carrera::find(['order' => 'nombre']));
            $this->view->pick('alumnos/nuevo');
            return;
        }

        $alumno = new Alumno();
        $alumno->assign($datos, ['nombres', 'apellidos', 'fecha_nacimiento', 'carrera_id']); // lista blanca
        $alumno->foto = $rutaFoto;
        if (!$alumno->save()) {
            // si falla, no dejar la foto huérfana
            (new FotoUploader($this->config['app']['uploadsDir']))->eliminar($rutaFoto);
            $this->conErrores(array_map(fn($m) => $m->getMessage(), iterator_to_array($alumno->getMessages())), $datos);
            $this->view->setVar('carreras', Carrera::find(['order' => 'nombre']));
            $this->view->pick('alumnos/nuevo');
            return;
        }

        $this->flashSession->success('Alumno registrado');
        return $this->irA('alumnos');
    }
}
```
Notas: `getUploadedFiles(true)` descarta los archivos con error de tamaño del request; si se necesita detectar el error, usar `getUploadedFiles(false)`. Comprobar el comportamiento con las pruebas de subida.

## 4. Vista de formulario de referencia (`app/views/alumnos/nuevo.volt`)
```volt
<h2>Nuevo alumno</h2>

{% if errores is not empty %}
  <div class="alert alert-danger"><ul class="mb-0">
    {% for e in errores %}<li>{{ e|e }}</li>{% endfor %}
  </ul></div>
{% endif %}

<form method="post" action="{{ url('alumnos/crear') }}" enctype="multipart/form-data">
  <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

  <div class="mb-3"><label class="form-label">Nombres</label>
    <input class="form-control" name="nombres" maxlength="100" value="{{ old['nombres']|default('')|e }}" required></div>
  <div class="mb-3"><label class="form-label">Apellidos</label>
    <input class="form-control" name="apellidos" maxlength="100" value="{{ old['apellidos']|default('')|e }}" required></div>
  <div class="mb-3"><label class="form-label">Fecha de nacimiento</label>
    <input class="form-control" type="date" name="fecha_nacimiento" value="{{ old['fecha_nacimiento']|default('')|e }}" required></div>
  <div class="mb-3"><label class="form-label">Carrera</label>
    <select class="form-select" name="carrera_id" required>
      <option value="">Selecciona...</option>
      {% for c in carreras %}
        <option value="{{ c.id }}">{{ c.nombre|e }}</option>
      {% endfor %}
    </select></div>
  <div class="mb-3"><label class="form-label">Fotografía (JPG/PNG, máx. 2 MB)</label>
    <input class="form-control" type="file" name="foto" accept="image/jpeg,image/png"></div>

  <button class="btn btn-primary">Guardar</button>
</form>
```
Eliminar (siempre un `<form>` POST con token, nunca un enlace):
```volt
<form method="post" action="{{ url('alumnos/' ~ a.id ~ '/eliminar') }}" onsubmit="return confirm('¿Eliminar?')">
  <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
  <button class="btn btn-sm btn-danger">Eliminar</button>
</form>
```
(Si la CSP bloquea `onsubmit` inline, usar un `<script src>` propio en `public/js/` o `data-confirm` con un script externo.)

## 5. Notas: validación de rango y duplicados
```php
$nota = $this->request->getPost('nota', null, '');
if (!is_numeric($nota) || $nota < 0 || $nota > 100) { $errores[] = 'La nota debe estar entre 0 y 100'; }

$existe = \App\Models\Nota::findFirst([
    'conditions' => 'alumno_id = :a: AND curso_id = :c: AND semestre_id = :s:',
    'bind'       => ['a' => $alumnoId, 'c' => $cursoId, 's' => $semestreId],
]);
if ($existe) { $errores[] = 'El alumno ya tiene nota en ese curso y semestre'; }
```
Verificar también que curso, sección y semestre existan (`findFirst` con `bind`).

## 6. Reportes
Usar `App\Library\Reportes\ReporteService` (SQL con JOIN y parámetros enlazados). En el controlador:
```php
$service = new \App\Library\Reportes\ReporteService($this->db, $this->config['app']['notaAprobacion']);
$carrera = (int) $this->request->getQuery('carrera_id', null, 0) ?: null;
$this->view->setVar('filas', $service->notas($carrera, $curso, $seccion));
```
Los filtros se castean a entero; un valor como `1 OR 1=1` se convierte en `1` y nunca se concatena.

## 7. Catálogos (un controlador, lista blanca)
```php
private const MODELOS = [
    'carreras'  => \App\Models\Carrera::class,
    'cursos'    => \App\Models\Curso::class,
    'secciones' => \App\Models\Seccion::class,
    'semestres' => \App\Models\Semestre::class,
];

private function modelo(): string
{
    $tipo = (string) $this->dispatcher->getParam('tipo');
    if (!isset(self::MODELOS[$tipo])) { throw new \RuntimeException('Catálogo inválido'); } // la ruta ya lo restringe
    return self::MODELOS[$tipo];
}
```
Al eliminar, capturar el fallo de la FK: si `delete()` devuelve false, mostrar "No se puede eliminar: está en uso" con `flashSession->error`.

## 8. Cosas típicas que fallan al empezar con Phalcon
- "Class 'Phalcon\...' not found": la extensión no está cargada (`php -m`), o es del PHP/TS-NTS equivocado.
- 404 en todas las rutas con Apache: falta `mod_rewrite` o el `.htaccess` de `public/`.
- Vistas no cambian: borrar `var/cache/volt/*` (o usar `APP_DEBUG=1`, que recompila siempre).
- Token CSRF inválido en el segundo envío: el token se consume al validarse; recargar la página del formulario.
- Variables no disponibles en Volt: hay que pasarlas con `$this->view->setVar()`.
