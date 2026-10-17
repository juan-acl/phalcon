# Sistema de Registro de Alumnos y Notas (Phalcon 5)

Aplicación web en **Phalcon** (PHP) para registrar alumnos de bachillerato, sus cursos aprobados y notas, con catálogos y reportes.

## Requisitos
- PHP 8.2+ (8.1 mínimo para Phalcon 5.x) con extensiones: `phalcon`, `pdo_mysql`, `mbstring`, `fileinfo`, `json`, `ctype`, `openssl`
- MySQL o MariaDB (XAMPP / Laragon / MySQL Installer). **Phalcon no soporta SQL Server.**
- Composer (solo para correr las pruebas)

## Instalar la extensión Phalcon en Windows
1. Ver versión y tipo de tu PHP: `php -v` y `php -i | findstr /i "Thread Architecture"` (anotar versión, **TS o NTS**, **x64**).
2. Descargar el `php_phalcon.dll` que coincida exactamente (versión + TS/NTS + x64) desde la página de releases de Phalcon (github.com/phalcon/cphalcon) o desde el paquete `phalcon` en pecl.php.net.
3. Copiar el DLL a la carpeta `ext` de PHP y agregar en `php.ini`: `extension=php_phalcon.dll`
4. Reiniciar Apache/terminal y verificar: `php -m | findstr /i phalcon` y `php --ri phalcon`
Si no hay DLL para tu combinación (por ejemplo XAMPP con PHP TS), descarga un PHP NTS desde windows.php.net, úsalo solo para este proyecto con `php -S`, y mantén MySQL de XAMPP.

## Puesta en marcha local
```bash
# 1. Crear la base (una vez)
mysql -u root -p -e "CREATE DATABASE escuela CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 2. Credenciales: crear app/config/config.local.php
#    <?php return ['db' => ['username' => 'escuela_user', 'password' => 'tu_clave']];

# 3. Crear tablas y datos de demo
php cli/reset_db.php

# 4. Levantar el servidor
php -S 127.0.0.1:8000 -t public public/router.php     # http://127.0.0.1:8000
```
En Apache (XAMPP) apuntar el DocumentRoot a la carpeta `public/` (el `.htaccess` ya redirige al front controller).

## Publicación para la presentación
- Local + URL pública: `ngrok http 8000`.
- Hosting con MySQL y soporte de la extensión Phalcon: subir el proyecto, apuntar el dominio a `public/`, crear `config.local.php`, importar `database/schema.sql` y `database/seed.sql`, y poner `APP_DEBUG` en 0.
- Si se publica con URL pública, activar el login: `AUTH_ENABLED=1`, `ADMIN_USER`, `ADMIN_PASS_HASH` (ver docs/06).

## Documentación
Carpeta `docs/`.
