<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Promise\Utils;
use Tests\Support\HttpTestCase;

/**
 * "Red team" sobre TU PROPIO sistema, en LOCAL (servidor de pruebas + base escuela_test).
 * Intenta: tumbar el sistema (errores 500, cuelgues, respuestas gigantes), filtrar archivos o
 * información, saltarse CSRF/sesión, e inyectar SQL/XSS/plantillas.
 * Copiar a tests/Feature/AtaquesTest.php
 *
 * Cada fallo es un hallazgo real o una mejora de endurecimiento: ver docs/10-ATAQUES-Y-ENDURECIMIENTO.md.
 * NUNCA apuntar estas pruebas a servidores que no sean tuyos.
 */
class AtaquesTest extends HttpTestCase
{
    private const MARCAS_SENSIBLES = ['CREATE TABLE', 'passHash', 'DB_PASS', "'password'", '<?php', 'PDO(', 'mysql:host'];

    // =====================================================================
    // Utilidades
    // =====================================================================

    /** Petición HTTP "cruda" (sin normalizar la URL) para rutas raras. */
    private function crudo(string $lineaPeticion, array $cabeceras = [], string $cuerpo = ''): array
    {
        $base = parse_url(getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081');
        $fp = @fsockopen($base['host'], $base['port'], $errno, $errstr, 5);
        if (!$fp) {
            $this->fail('Sin conexión con el servidor de pruebas');
        }
        stream_set_timeout($fp, 8);
        $h = array_merge(["Host: {$base['host']}:{$base['port']}", 'Connection: close'], $cabeceras);
        fwrite($fp, $lineaPeticion . "\r\n" . implode("\r\n", $h) . "\r\n\r\n" . $cuerpo);
        $resp = (string) stream_get_contents($fp);
        $meta = stream_get_meta_data($fp);
        fclose($fp);

        preg_match('#^HTTP/\d\.\d (\d{3})#', $resp, $m);
        [$cab, $cue] = array_pad(explode("\r\n\r\n", $resp, 2), 2, '');
        return ['status' => (int) ($m[1] ?? 0), 'cabeceras' => $cab, 'cuerpo' => $cue, 'timeout' => $meta['timed_out']];
    }

    private function nuevoCliente(): Client
    {
        return new Client([
            'base_uri' => getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081',
            'cookies' => new CookieJar(), 'http_errors' => false, 'allow_redirects' => false, 'timeout' => 20,
        ]);
    }

    /** Campos ocultos (token CSRF) de la página de un formulario. */
    private function ocultos(Client $cliente, string $pagina): array
    {
        $r = $cliente->get($pagina);
        $this->assertSame(200, $r->getStatusCode(), "No se pudo abrir {$pagina}");
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . (string) $r->getBody());
        $o = [];
        foreach ((new DOMXPath($dom))->query('//form//input[@type="hidden"]') as $i) {
            $o[$i->getAttribute('name')] = $i->getAttribute('value');
        }
        return $o;
    }

    /** POST con token válido y valores arbitrarios (arreglos, binarios, enormes...). */
    private function postConToken(string $paginaForm, string $accion, array $campos)
    {
        return $this->http->post($accion, ['form_params' => array_merge($this->ocultos($this->http, $paginaForm), $campos)]);
    }

    private function medir(callable $fn): array
    {
        $t = microtime(true);
        $r = $fn();
        return [$r, microtime(true) - $t];
    }

    private function sinError500($r, string $contexto): void
    {
        $this->assertLessThan(500, $r->getStatusCode(), "Error de servidor (>=500) en: {$contexto}");
    }

    // =====================================================================
    // A. Robustez: entradas hostiles que NO deben tumbar la app
    // =====================================================================

    public function test_parametros_como_arreglos_no_provocan_500(): void
    {
        $this->crearAlumno('Ana', 'Pérez');

        foreach (['/alumnos' => ['q' => ['x', 'y']], '/notas/buscar' => ['q' => ['x']],
                  '/reportes/notas' => ['carrera_id' => [1], 'curso_id' => ['a' => 'b'], 'seccion_id' => [[1]]],
                  '/reportes/alumnos-por-carrera' => ['carrera_id' => [1]]] as $ruta => $query) {
            $this->sinError500($this->get($ruta, $query), "GET {$ruta} con arreglos");
        }

        $r = $this->postConToken('/catalogos/carreras/nuevo', '/catalogos/carreras/crear', ['nombre' => ['a', 'b']]);
        $this->sinError500($r, 'POST nombre[] en catálogo');
        $this->assertSame(1, $this->contar('carreras'));

        $r = $this->postConToken('/alumnos/nuevo', '/alumnos/crear', ['nombres' => ['x'], 'apellidos' => ['y'], 'fecha_nacimiento' => ['2008-01-01'], 'carrera_id' => ['1']]);
        $this->sinError500($r, 'POST alumno con arreglos');
        $this->assertSame(1, $this->contar('alumnos'));
    }

    public function test_ids_extremos_no_provocan_500(): void
    {
        $valores = ['0', '-1', '99999999999999999999', '1e3', '0x10', '1.5', ' 1', '1 ', '٣', "1\0"];
        foreach ($valores as $v) {
            foreach (['carrera_id', 'curso_id', 'seccion_id'] as $filtro) {
                $this->sinError500($this->get('/reportes/notas', [$filtro => $v]), "{$filtro}=" . json_encode($v));
            }
        }
        $this->assertSame(404, $this->get('/alumnos/99999999999999999999')->getStatusCode());
        $this->assertSame(404, $this->get('/alumnos/00000000000000000001x')->getStatusCode());
    }

    public function test_textos_gigantes_se_rechazan_rapido_y_sin_500(): void
    {
        $this->insertar('carreras', ['nombre' => 'Computación']);
        $enorme = str_repeat('A', 1024 * 1024); // 1 MB

        [$r, $seg] = $this->medir(fn() => $this->postConToken('/alumnos/nuevo', '/alumnos/crear', [
            'nombres' => $enorme, 'apellidos' => $enorme, 'fecha_nacimiento' => '2008-01-01', 'carrera_id' => 1,
        ]));
        $this->sinError500($r, 'nombre de 1 MB');
        $this->assertSame(0, $this->contar('alumnos'));
        $this->assertLessThan(5, $seg, 'Procesar un texto de 1 MB tardó demasiado');

        $r = $this->postConToken('/catalogos/cursos/nuevo', '/catalogos/cursos/crear', ['nombre' => $enorme]);
        $this->sinError500($r, 'catálogo con 1 MB');
        $this->assertSame(0, $this->contar('cursos'));
    }

    public function test_busqueda_con_texto_enorme_no_cuelga_el_sistema(): void
    {
        [$r, $seg] = $this->medir(fn() => $this->get('/alumnos', ['q' => str_repeat('%_', 5000)]));
        $this->sinError500($r, 'q de 10 KB de comodines');
        $this->assertLessThan(5, $seg);
    }

    public function test_miles_de_parametros_no_provocan_500(): void
    {
        $campos = [];
        for ($i = 0; $i < 5000; $i++) {
            $campos["campo{$i}"] = 'x';
        }
        $r = $this->postConToken('/catalogos/carreras/nuevo', '/catalogos/carreras/crear', ['nombre' => 'Prueba'] + $campos);
        $this->sinError500($r, '5000 campos en un POST');
    }

    public function test_url_larguisima_no_provoca_500(): void
    {
        $r = $this->crudo('GET /' . str_repeat('a', 20000) . ' HTTP/1.1');
        $this->assertFalse($r['timeout'], 'El servidor se colgó con una URL de 20 KB');
        $this->assertLessThan(500, $r['status'], 'Error 500 con URL larga');
    }

    public function test_peticiones_mal_formadas_no_tumban_el_servidor(): void
    {
        foreach (['GET /alumnos?q=%ZZ%GG HTTP/1.1', "GET /alumnos/1%00 HTTP/1.1", 'GET //alumnos// HTTP/1.1',
                  'GET /alumnos?q=%c0%ae%c0%ae HTTP/1.1', 'POST /alumnos/crear HTTP/1.1'] as $linea) {
            $r = $this->crudo($linea, ['Content-Length: 0']);
            $this->assertFalse($r['timeout'], "Se colgó con: {$linea}");
            $this->assertNotSame(500, $r['status'], "Error 500 con: {$linea}");
        }
        // el servidor sigue vivo
        $this->assertSame(200, $this->get('/')->getStatusCode());
    }

    public function test_unicode_raro_y_bytes_nulos_no_rompen_nada(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        foreach (["Ana 😀 Ñandú", "A\u{202E}nombre invertido", "Nul\0Byte", "Ñ\u{0301}", "\xF0\x28\x8C\x28"] as $nombre) {
            $r = $this->postConToken('/alumnos/nuevo', '/alumnos/crear', [
                'nombres' => $nombre, 'apellidos' => 'Prueba', 'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carrera,
            ]);
            $this->sinError500($r, 'nombre ' . json_encode($nombre));
        }
        $this->sinError500($this->get('/alumnos'), 'listado con unicode raro');
        $this->sinError500($this->get('/reportes/alumnos-por-carrera'), 'reporte con unicode raro');
    }

    public function test_metodos_http_inesperados_no_modifican_datos(): void
    {
        $id = $this->crearAlumno('Ana', 'Pérez');
        foreach (['PUT', 'DELETE', 'PATCH', 'OPTIONS', 'TRACE', 'HEAD'] as $m) {
            $r = $this->http->request($m, "/alumnos/{$id}", ['headers' => ['X-HTTP-Method-Override' => 'DELETE']]);
            $this->sinError500($r, "método {$m}");
            $this->assertSame(1, $this->contar('alumnos'), "{$m} modificó datos");
        }
        $this->sinError500($this->http->post('/alumnos'), 'POST a una ruta solo-GET');
        $this->sinError500($this->http->get('/alumnos', ['query' => ['_method' => 'DELETE']]), '_method=DELETE');
    }

    // =====================================================================
    // B. Archivos, rutas e información expuesta
    // =====================================================================

    public static function rutasSensibles(): array
    {
        return array_map(fn($r) => [$r], [
            '/.htaccess', '/uploads/.htaccess', '/.env', '/.git/config', '/composer.json', '/composer.lock', '/phpunit.xml',
            '/app/config/config.php', '/app/config/config.local.php', '/database/schema.sql', '/database/seed.sql',
            '/var/log/sql.log', '/var/log/php-error.log', '/cli/reset_db.php', '/docs/06-SEGURIDAD.md',
            '/%2e%2e/database/schema.sql', '/..%2fdatabase%2fschema.sql', '/%252e%252e/app/config/config.php',
            '/....//database/schema.sql', '/uploads/../../app/config/config.php', '/uploads/%2e%2e/%2e%2e/composer.json',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rutasSensibles')]
    public function test_archivos_sensibles_no_son_accesibles(string $ruta): void
    {
        $r = $this->crudo("GET {$ruta} HTTP/1.1");

        $this->assertNotSame(500, $r['status'], "Error 500 en {$ruta}");
        foreach (self::MARCAS_SENSIBLES as $marca) {
            $this->assertStringNotContainsString($marca, $r['cuerpo'], "Se filtró contenido sensible ({$marca}) en {$ruta}");
        }
        if ($r['status'] === 200) {
            $this->assertStringContainsString('<html', strtolower($r['cuerpo']), "{$ruta} devolvió un archivo (200) que no es una página de la app");
        }
    }

    public function test_no_hay_listado_de_directorios(): void
    {
        foreach (['/uploads/', '/uploads', '/'] as $ruta) {
            $r = $this->crudo("GET {$ruta} HTTP/1.1");
            $this->assertStringNotContainsStringIgnoringCase('Index of', $r['cuerpo'], "Listado de directorio en {$ruta}");
        }
    }

    public function test_los_errores_no_revelan_version_ni_tecnologia(): void
    {
        $r = $this->get('/alumnos');
        $this->assertFalse($r->hasHeader('X-Powered-By'), 'Se expone X-Powered-By: ' . $r->getHeaderLine('X-Powered-By') . ' (agregar header_remove en index.php)');

        $r = $this->get('/ruta/inexistente');
        $this->assertStringNotContainsStringIgnoringCase('phalcon', $this->cuerpo($r));
    }

    public function test_los_errores_de_base_de_datos_no_se_muestran_al_usuario(): void
    {
        $this->insertar('carreras', ['nombre' => 'Computación']);
        $html = '';
        foreach (['Computación', "'", '"', '\\', "') OR 1=1 --"] as $nombre) {
            $html .= $this->cuerpo($this->postConToken('/catalogos/carreras/nuevo', '/catalogos/carreras/crear', ['nombre' => $nombre]));
        }
        foreach (['SQLSTATE', 'Duplicate entry', 'You have an error in your SQL', 'mysqli', 'PDOException', 'Stack trace'] as $pista) {
            $this->assertStringNotContainsString($pista, $html, "Se muestra un detalle interno de la BD: {$pista}");
        }
    }

    // =====================================================================
    // C. Subida de archivos hostil
    // =====================================================================

    public function test_imagen_con_php_al_final_no_se_ejecuta(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $poliglota = $this->archivo('foto.png', base64_decode(self::PNG_1X1) . "<?php echo 'PWNED'; ?>", 'image/png');

        $r = $this->submit('/alumnos/nuevo', ['nombres' => 'X', 'apellidos' => 'Y', 'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carrera], ['foto' => $poliglota]);

        if ($r->getStatusCode() === 302) { // aceptada como imagen: debe guardarse como .png y servirse sin ejecutar
            $foto = $this->pdo->query('SELECT foto FROM alumnos')->fetchColumn();
            $this->assertMatchesRegularExpression('#\.(png|jpg)$#', $foto);
            $serv = $this->crudo("GET /{$foto} HTTP/1.1");
            $this->assertStringContainsString('image/png', $serv['cabeceras']);
            $this->assertStringContainsString("<?php echo 'PWNED'", $serv['cuerpo'], 'El archivo no se sirvió como datos');
        } else {
            $this->assertContains($r->getStatusCode(), [200, 422]); // rechazada: también es válido
        }
    }

    public function test_imagen_con_dimensiones_gigantes_es_rechazada(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $ihdr = 'IHDR' . pack('NNCCCCC', 65000, 65000, 8, 2, 0, 0, 0);
        $png = "\x89PNG\r\n\x1a\n" . pack('N', 13) . $ihdr . pack('N', crc32($ihdr)) . pack('N', 0) . 'IEND' . pack('N', crc32('IEND'));
        $bomba = $this->archivo('bomba.png', $png, 'image/png');

        $r = $this->submit('/alumnos/nuevo', ['nombres' => 'X', 'apellidos' => 'Y', 'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carrera], ['foto' => $bomba]);

        $this->assertContains($r->getStatusCode(), [200, 422], 'Se aceptó una imagen de 65000x65000 px (limitar dimensiones en FotoUploader)');
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_muchos_archivos_y_nombres_larguisimos_no_provocan_500(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $campos = $this->ocultos($this->http, '/alumnos/nuevo') + ['nombres' => 'X', 'apellidos' => 'Y', 'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carrera];
        $partes = [];
        foreach ($campos as $n => $v) {
            $partes[] = ['name' => $n, 'contents' => (string) $v];
        }
        for ($i = 0; $i < 60; $i++) {
            $partes[] = ['name' => "foto{$i}", 'contents' => base64_decode(self::PNG_1X1), 'filename' => str_repeat('n', 3000) . '.png'];
        }
        $partes[] = ['name' => 'foto', 'contents' => base64_decode(self::PNG_1X1), 'filename' => str_repeat('ñ', 5000) . '.png'];

        $r = $this->http->post('/alumnos/crear', ['multipart' => $partes]);
        $this->sinError500($r, '60 archivos + nombres de 5000 caracteres');
    }

    public function test_el_campo_foto_enviado_como_texto_no_se_guarda(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $this->postConToken('/alumnos/nuevo', '/alumnos/crear', [
            'nombres' => 'X', 'apellidos' => 'Y', 'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carrera,
            'foto' => '../../app/config/config.php',
        ]);
        $foto = $this->pdo->query('SELECT foto FROM alumnos')->fetchColumn();
        $this->assertEmpty($foto, 'Se guardó una ruta enviada como texto en el campo foto: ' . $foto);
    }

    // =====================================================================
    // D. Sesión, CSRF y cabeceras
    // =====================================================================

    public function test_un_token_csrf_no_se_puede_reutilizar(): void
    {
        $token = $this->ocultos($this->http, '/catalogos/carreras/nuevo');

        $r1 = $this->http->post('/catalogos/carreras/crear', ['form_params' => $token + ['nombre' => 'Uno']]);
        $r2 = $this->http->post('/catalogos/carreras/crear', ['form_params' => $token + ['nombre' => 'Dos']]);

        $this->assertSame(302, $r1->getStatusCode());
        $this->assertSame(403, $r2->getStatusCode(), 'El mismo token se aceptó dos veces (replay)');
        $this->assertSame(1, $this->contar('carreras'));
    }

    public function test_un_token_de_otra_sesion_es_rechazado(): void
    {
        $atacante = $this->nuevoCliente();
        $victima = $this->nuevoCliente();

        $this->loguearCliente($atacante, 'director');
        $this->loguearCliente($victima, 'director');

        $tokenAtacante = $this->ocultos($atacante, '/catalogos/carreras/nuevo');
        $this->ocultos($victima, '/catalogos/carreras/nuevo'); // la víctima tiene su propia sesión

        $r = $victima->post('/catalogos/carreras/crear', ['form_params' => $tokenAtacante + ['nombre' => 'Falsa']]);

        $this->assertSame(403, $r->getStatusCode(), 'Un token de otra sesión fue aceptado');
        $this->assertSame(0, $this->contar('carreras'));
    }

    public function test_el_servidor_no_acepta_ids_de_sesion_impuestos(): void
    {
        $impuesto = 'atacante1234567890abcdef12345678';
        $cliente = new Client(['base_uri' => getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081', 'http_errors' => false, 'allow_redirects' => false, 'cookies' => false]);

        $r = $cliente->get('/catalogos/carreras/nuevo', ['headers' => ['Cookie' => "PHPSESSID={$impuesto}"]]);

        $set = $r->getHeaderLine('Set-Cookie');
        $this->assertNotEmpty($set, 'No se emitió una sesión nueva: el id impuesto fue aceptado (fijación de sesión)');
        $this->assertStringNotContainsString($impuesto, $set);
    }

    public function test_cookie_de_sesion_tiene_samesite(): void
    {
        $cliente = $this->nuevoCliente();
        $set = $cliente->get('/alumnos/nuevo')->getHeaderLine('Set-Cookie');
        $this->assertStringContainsStringIgnoringCase('HttpOnly', $set);
        $this->assertMatchesRegularExpression('/SameSite=(Lax|Strict)/i', $set);
    }

    public function test_no_hay_redireccion_abierta_ni_inyeccion_en_el_host(): void
    {
        $r = $this->http->get('/alumnos', ['query' => ['redirect' => 'http://evil.example', 'next' => 'http://evil.example', 'url' => 'http://evil.example']]);
        $this->assertFalse($r->hasHeader('Location'));

        $campos = $this->ocultos($this->http, '/catalogos/carreras/nuevo') + ['nombre' => 'HostTest'];
        $r = $this->http->post('/catalogos/carreras/crear', ['form_params' => $campos, 'headers' => ['Host' => 'evil.example', 'X-Forwarded-Host' => 'evil.example']]);
        $this->assertStringNotContainsString('evil.example', $r->getHeaderLine('Location'), 'La redirección usa el Host enviado por el cliente');
    }

    public function test_paginas_con_datos_personales_no_se_guardan_en_cache(): void
    {
        $id = $this->crearAlumno('Ana', 'Pérez');
        $r = $this->get("/alumnos/{$id}");
        $this->assertMatchesRegularExpression('/no-store|no-cache|private/i', $r->getHeaderLine('Cache-Control'), 'La ficha del alumno puede quedar en cachés compartidas');
    }

    // =====================================================================
    // E. Inyección avanzada (SQL ciega por tiempo, plantillas, atributos HTML)
    // =====================================================================

    public static function payloadsDeTiempo(): array
    {
        return array_map(fn($p) => [$p], [
            "' OR SLEEP(3) OR '",
            "'; SELECT SLEEP(3); -- ",
            "1' AND (SELECT 1 FROM (SELECT SLEEP(3))a) AND '1'='1",
            "%' AND SLEEP(3) AND '%'='",
            "\" OR SLEEP(3) OR \"",
            "1 AND SLEEP(3)",
            "1 OR SLEEP(3)",
            "(SELECT SLEEP(3))",
        ]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('payloadsDeTiempo')]
    public function test_inyeccion_sql_ciega_por_tiempo_no_funciona(string $payload): void
    {
        $this->crearAlumno('Ana', 'Pérez');

        $objetivos = [['/alumnos', 'q'], ['/notas/buscar', 'q'], ['/reportes/notas', 'carrera_id'], ['/reportes/notas', 'curso_id'],
                      ['/reportes/notas', 'seccion_id'], ['/reportes/alumnos-por-carrera', 'carrera_id']];
        foreach ($objetivos as [$ruta, $param]) {
            [$r, $seg] = $this->medir(fn() => $this->get($ruta, [$param => $payload]));
            $this->sinError500($r, "{$ruta}?{$param}");
            $this->assertLessThan(2.5, $seg, "SQL INYECTABLE: {$ruta}?{$param} tardó {$seg}s con el payload {$payload}");
        }
    }

    public function test_sql_de_segundo_orden_dato_guardado_no_se_ejecuta_despues(): void
    {
        $veneno = "x'); DROP TABLE notas; SELECT SLEEP(3); -- ";
        $carrera = $this->insertar('carreras', ['nombre' => $veneno]);
        $alumno = $this->crearAlumno($veneno, $veneno, $carrera);
        $curso = $this->insertar('cursos', ['nombre' => $veneno]);
        $sem = $this->insertar('semestres', ['nombre' => $veneno]);
        $sec = $this->insertar('secciones', ['nombre' => $veneno]);
        $this->insertar('notas', ['alumno_id' => $alumno, 'curso_id' => $curso, 'semestre_id' => $sem, 'seccion_id' => $sec, 'nota' => 70]);

        foreach (['/alumnos', "/alumnos/{$alumno}", "/alumnos/{$alumno}/editar", '/reportes/alumnos-por-carrera', '/reportes/notas',
                  "/reportes/notas?carrera_id={$carrera}", '/catalogos/carreras', '/catalogos/cursos', "/alumnos/{$alumno}/notas/nueva", '/notas/buscar?q=x'] as $ruta) {
            [$r, $seg] = $this->medir(fn() => $this->http->get($ruta));
            $this->sinError500($r, $ruta);
            $this->assertLessThan(2.5, $seg, "Un dato guardado se ejecutó como SQL en {$ruta}");
        }
        $this->assertSame(1, $this->contar('notas'), 'La tabla notas fue afectada');
    }

    public function test_inyeccion_de_plantillas_volt_en_datos_se_muestra_como_texto(): void
    {
        $id = $this->crearAlumno('{{ 7*7 }}', '{% for i in [1,2,3] %}X{% endfor %}');

        foreach (["/alumnos/{$id}", '/alumnos'] as $ruta) {
            $html = $this->cuerpo($this->get($ruta));
            $this->assertStringContainsString('{{ 7*7 }}', $html, "La plantilla se interpretó en {$ruta}");
            $this->assertStringNotContainsString('XXX', $html);
        }
    }

    public function test_xss_en_atributos_html_se_escapa(): void
    {
        $payload = '" onfocus="alert(1)" autofocus="';
        $id = $this->crearAlumno($payload, "'><svg onload=alert(2)>");

        foreach (["/alumnos/{$id}/editar", "/alumnos/{$id}", '/alumnos'] as $ruta) {
            $html = $this->cuerpo($this->get($ruta));
            $this->assertStringNotContainsString('" onfocus="alert(1)"', $html, "XSS en atributo en {$ruta}");
            $this->assertStringNotContainsString('<svg onload=alert(2)>', $html, "XSS en {$ruta}");
        }

        $html = $this->cuerpo($this->get('/alumnos', ['q' => '"><script>alert(3)</script>']));
        $this->assertStringNotContainsString('"><script>alert(3)</script>', $html, 'XSS reflejado en el valor del buscador');
    }

    // =====================================================================
    // F. Denegación de servicio a nivel de aplicación
    // =====================================================================

    private function poblarMuchos(int $n): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Masiva']);
        $curso = $this->insertar('cursos', ['nombre' => 'Curso masivo']);
        $sem = $this->insertar('semestres', ['nombre' => 'Sem masivo']);
        $sec = $this->insertar('secciones', ['nombre' => 'M']);

        $this->pdo->beginTransaction();
        $a = $this->pdo->prepare('INSERT INTO alumnos (nombres, apellidos, fecha_nacimiento, carrera_id) VALUES (?,?,?,?)');
        $nt = $this->pdo->prepare('INSERT INTO notas (alumno_id, curso_id, semestre_id, seccion_id, nota) VALUES (?,?,?,?,?)');
        for ($i = 1; $i <= $n; $i++) {
            $a->execute(["Nombre{$i}", "Apellido{$i}", '2008-01-01', $carrera]);
            $nt->execute([$this->pdo->lastInsertId(), $curso, $sem, $sec, 70]);
        }
        $this->pdo->commit();
    }

    public function test_los_listados_y_reportes_no_crecen_sin_limite(): void
    {
        $this->poblarMuchos(3000);

        foreach (['/alumnos', '/reportes/alumnos-por-carrera', '/reportes/notas', '/notas/buscar'] as $ruta) {
            [$r, $seg] = $this->medir(fn() => $this->get($ruta));
            $kb = strlen((string) $r->getBody()) / 1024;
            $this->sinError500($r, $ruta);
            $this->assertLessThan(5, $seg, "{$ruta} tardó {$seg}s con 3000 alumnos (falta paginación o LIMIT)");
            $this->assertLessThan(1500, $kb, sprintf('%s devolvió %.0f KB con 3000 alumnos (falta paginación o LIMIT)', $ruta, $kb));
        }
    }

    public function test_rafaga_de_peticiones_simultaneas_no_provoca_errores(): void
    {
        $this->crearAlumno('Ana', 'Pérez');
        $promesas = [];
        for ($i = 0; $i < 60; $i++) {
            $promesas[] = $this->http->getAsync($i % 2 ? '/alumnos' : '/reportes/notas');
        }
        [$respuestas, $seg] = $this->medir(fn() => Utils::unwrap($promesas));

        foreach ($respuestas as $r) {
            $this->assertLessThan(500, $r->getStatusCode());
        }
        $this->assertLessThan(30, $seg, 'Una ráfaga de 60 peticiones tardó demasiado');
        $this->assertSame(200, $this->get('/')->getStatusCode(), 'El sistema no respondió después de la ráfaga');
    }

    // =====================================================================
    // G. Control de acceso y prevención de escalación de roles (RBAC / IDOR)
    // =====================================================================

    public function test_invitado_sin_autenticar_no_puede_acceder_a_rutas_privadas(): void
    {
        $cliente = $this->nuevoCliente();
        $rutas = ['/alumnos', '/tablero', '/profesores', '/clases', '/inscripciones', '/auditoria', '/profesor/clases', '/mi/perfil', '/mi/notas'];
        foreach ($rutas as $ruta) {
            $r = $cliente->get($ruta);
            $this->assertTrue(
                in_array($r->getStatusCode(), [302, 401, 403], true),
                "Ruta privada {$ruta} accesible sin login (código {$r->getStatusCode()})"
            );
        }
    }

    public function test_alumno_no_puede_escalar_a_director_ni_profesor(): void
    {
        $this->loginComo('alumno');
        $rutasDirector = ['/tablero', '/profesores', '/clases', '/inscripciones', '/auditoria', '/alumnos/nuevo'];
        foreach ($rutasDirector as $ruta) {
            $r = $this->get($ruta);
            $this->assertSame(403, $r->getStatusCode(), "Alumno no debe poder acceder a {$ruta}");
        }

        $rProf = $this->get('/profesor/clases');
        $this->assertSame(403, $rProf->getStatusCode(), 'Alumno no debe poder acceder al portal de profesores');
    }

    public function test_profesor_no_puede_escalar_a_director(): void
    {
        $this->loginComo('profesor');
        $rutasDirector = ['/tablero', '/profesores', '/clases', '/inscripciones', '/auditoria', '/alumnos/nuevo'];
        foreach ($rutasDirector as $ruta) {
            $r = $this->get($ruta);
            $this->assertSame(403, $r->getStatusCode(), "Profesor no debe poder acceder a {$ruta}");
        }
    }

    public function test_manipulacion_de_idor_entre_alumnos_responde_404(): void
    {
        $this->loginComo('alumno');
        $otroId = $this->crearAlumno('Victima', 'Prueba');

        $rVer = $this->get("/alumnos/{$otroId}");
        $this->assertSame(404, $rVer->getStatusCode(), 'Ver alumno ajeno debe devolver 404 (no 403 ni 200)');

        $rFicha = $this->get("/alumnos/{$otroId}/ficha?formato=pdf");
        $this->assertSame(404, $rFicha->getStatusCode(), 'Descargar ficha ajena debe devolver 404');
    }
}

