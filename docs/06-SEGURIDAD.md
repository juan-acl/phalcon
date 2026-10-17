# 06 — Seguridad (Phalcon)

Phalcon da las herramientas, pero **no protege solo**: a diferencia de Laravel, hay que activar escapado, CSRF y validación a propósito. `public/index.php`, `BaseController`, `SecurityHeaders` y `FotoUploader` ya traen lo básico; hay que usarlos y no saltárselos.

## 1. Inyección SQL
**Riesgo:** una entrada altera la consulta (`' OR '1'='1`).
```php
// MAL
Alumno::find("nombres LIKE '%$q%'");
$this->db->fetchAll("SELECT * FROM alumnos WHERE id = $id");

// BIEN (parámetros enlazados)
Alumno::find([
    'conditions' => 'nombres LIKE :q: OR apellidos LIKE :q:',
    'bind'       => ['q' => '%' . $q . '%'],
]);
$this->db->fetchAll('SELECT * FROM alumnos WHERE id = :id', \Phalcon\Db\Enum::FETCH_ASSOC, ['id' => $id]);
```
- Rutas con `{id:[0-9]+}`: un id no numérico ni siquiera llega al controlador (404).
- Filtros de reportes: castear/validar (`(int)`, `exists` manual) antes de usar.
- Orden elegido por el usuario: solo con lista blanca (`in_array($col, ['nombres','apellidos'], true)`).
- Usuario de MySQL con permisos solo sobre la base `escuela` (no `root`).

## 2. XSS
**Riesgo:** un nombre como `<script>…</script>` se ejecuta en el navegador.
- En Volt: **siempre `{{ dato|e }}`** (también `|escape_attr` dentro de atributos si hace falta).
- Nunca `<?= $x ?>` ni código PHP dentro de las vistas.
- Mensajes flash: `Phalcon\Flash\Session` escapa por defecto; no desactivar el escapado.
- Cabecera `Content-Security-Policy` (en `SecurityHeaders`).

## 3. CSRF
- Todo formulario POST lleva el token:
```volt
<input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
```
- `BaseController::beforeExecuteRoute` verifica el token en TODO POST (responde 403 si falta o es falso).
- Eliminar siempre por POST (botón dentro de un `<form>`), nunca por enlace GET: las rutas de modificación solo existen con `addPost`.

## 4. Subida de archivos
Usar solo `App\Library\Uploads\FotoUploader`:
- Tipo real por contenido (`getRealType()` + `getimagesize`), solo JPG/PNG (nada de SVG/PHP), máx. 2 MB.
- Nombre aleatorio; el nombre original se descarta.
- `public/uploads/.htaccess` bloquea la ejecución de scripts en esa carpeta.
- Al editar/eliminar un alumno: `$uploader->eliminar($alumno->foto)`.

## 5. Asignación masiva
```php
$alumno->assign($datos, ['nombres', 'apellidos', 'fecha_nacimiento', 'carrera_id']); // lista blanca
```
Nunca `assign($this->request->getPost())` sin lista blanca.

## 6. Autenticación
El enunciado no pide login, **pero si publican con URL pública cualquiera puede editar/borrar datos.** `BaseController` incluye un login HTTP Basic opcional:
```bash
php -r "echo password_hash('tu_clave_larga', PASSWORD_DEFAULT);"   # copiar el hash
# en config.local.php o variables de entorno:
AUTH_ENABLED=1  ADMIN_USER=admin  ADMIN_PASS_HASH=<hash>
```
Úsenlo siempre sobre HTTPS (ngrok y hosting lo dan). Para una demo local se puede dejar apagado y mencionarlo como mejora en el PDF.

## 7. Validación de entradas
Con `Phalcon\Filter\Validation` (PresenceOf, StringLength, Date, Numericality, Between) en cada POST. Reglas: nombres/apellidos ≤ 100, fecha válida y pasada, nota numérica entre 0 y 100, IDs existentes en su tabla, nota única por alumno-curso-semestre. Validar en el servidor aunque el HTML tenga `required`.

## 8. Sesión y cookies
`public/index.php` activa `httponly`, `samesite=Lax`, `use_strict_mode` y `use_only_cookies`. Con HTTPS agregar `ini_set('session.cookie_secure', '1')`.

## 9. Configuración y despliegue
- `APP_DEBUG=0` al publicar (con debug se muestran trazas con rutas y consultas).
- `config.local.php` y credenciales **nunca** en git ni en el ZIP.
- `var/log/` puede contener datos: no incluirlo en el ZIP; `LOG_QUERIES=0` fuera de las demos.
- Mantener PHP y la extensión Phalcon en versiones con soporte.

## 10. Errores
Páginas 404/403/401 genéricas (`ErrorController`); el detalle queda en `var/log/php-error.log`.

## Reglas de seguridad centrales
Sin SQL concatenado · `|e` en Volt · CSRF en todo formulario · `assign` con lista blanca · subidas solo con `FotoUploader` · cambios de datos solo por POST.

## Checklist final antes de entregar
- [ ] `bash scripts/auditoria.sh` sin hallazgos
- [ ] Pruebas automáticas en verde (SeguridadTest incluido)
- [ ] `APP_DEBUG=0` en lo publicado
- [ ] ZIP sin `config.local.php`, `vendor/`, `var/log/`, fotos de prueba
- [ ] Login activo si hay URL pública
- [ ] Usuario de MySQL sin permisos de administrador
