<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas defensivas: verifican que el sistema RESISTE entradas maliciosas.
 * Cada prueba que falle es una vulnerabilidad real que hay que corregir en el sistema.
 */
class SeguridadTest extends HttpTestCase
{
    private function datosAlumno(int $carreraId, array $extra = []): array
    {
        return array_merge([
            'nombres' => 'X', 'apellidos' => 'Y',
            'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carreraId,
        ], $extra);
    }

    // ---------- SQL Injection ----------

    public function test_inyeccion_sql_en_busqueda_no_devuelve_todos_los_registros(): void
    {
        $this->crearAlumno('Zacarías', 'Pruebauno');
        $this->crearAlumno('Wendy', 'Pruebados');

        $r = $this->get('/alumnos', ['q' => "' OR '1'='1"]);
        $this->assertSame(200, $r->getStatusCode());
        $html = $this->cuerpo($r);
        $this->assertStringNotContainsString('Pruebauno', $html);
        $this->assertStringNotContainsString('Pruebados', $html);
    }

    public function test_inyeccion_sql_destructiva_no_borra_tablas(): void
    {
        $this->crearAlumno('Zacarías', 'Pruebauno');
        $this->crearAlumno('Wendy', 'Pruebados');

        $r = $this->get('/alumnos', ['q' => "'; DROP TABLE alumnos; --"]);
        $this->assertNotSame(500, $r->getStatusCode());
        $this->assertSame(2, $this->contar('alumnos'));
    }

    public function test_filtros_de_reportes_con_sql_no_provocan_error_500(): void
    {
        foreach (['1 OR 1=1', '1; DROP TABLE notas', "' UNION SELECT 1,2,3 --"] as $payload) {
            foreach (['carrera_id', 'curso_id', 'seccion_id'] as $filtro) {
                $r = $this->get('/reportes/notas', [$filtro => $payload]);
                $this->assertNotSame(500, $r->getStatusCode(), "Payload provocó 500 en {$filtro}: {$payload}");
            }
        }
        $this->assertSame(0, $this->contar('notas')); // la tabla sigue existiendo
    }

    // ---------- XSS ----------

    public function test_xss_almacenado_se_escapa_en_ficha_listado_y_reportes(): void
    {
        $id = $this->crearAlumno('<script>alert(1)</script>', '<img src=x onerror=alert(2)>');

        foreach (["/alumnos/{$id}", '/alumnos', '/reportes/alumnos-por-carrera'] as $ruta) {
            $html = $this->cuerpo($this->get($ruta));
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html, "XSS sin escapar en {$ruta}");
            $this->assertStringNotContainsString('<img src=x onerror=alert(2)>', $html, "XSS sin escapar en {$ruta}");
        }
        $this->assertStringContainsString('&lt;script&gt;', $this->cuerpo($this->get("/alumnos/{$id}")));
    }

    public function test_xss_reflejado_en_busqueda_se_escapa(): void
    {
        $html = $this->cuerpo($this->get('/alumnos', ['q' => '<script>alert(3)</script>']));
        $this->assertStringNotContainsString('<script>alert(3)</script>', $html);
    }

    // ---------- Subida de archivos ----------

    public function test_archivo_php_con_extension_y_tipo_falsos_es_rechazado(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $shell = $this->archivo('foto.png', '<?php system($_GET["c"]); ?>', 'image/png');

        $r = $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera), ['foto' => $shell]);

        $this->assertContains($r->getStatusCode(), [200, 422]);
        $this->assertSame(0, $this->contar('alumnos'));
        $this->assertEmpty(array_filter(glob($this->raiz . '/public/uploads/*') ?: [], fn($f) => !str_starts_with(basename($f), '.')));
    }

    public function test_extension_php_directa_es_rechazada(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $shell = $this->archivo('shell.php', '<?php echo 1;', 'application/x-php');

        $r = $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera), ['foto' => $shell]);
        $this->assertContains($r->getStatusCode(), [200, 422]);
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_svg_es_rechazado(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $svg = $this->archivo('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 'image/svg+xml');

        $r = $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera), ['foto' => $svg]);
        $this->assertContains($r->getStatusCode(), [200, 422]);
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_imagen_demasiado_pesada_es_rechazada(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $grande = $this->archivo('grande.png', base64_decode(self::PNG_1X1) . str_repeat('0', 3 * 1024 * 1024), 'image/png');

        $r = $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera), ['foto' => $grande]);
        $this->assertContains($r->getStatusCode(), [200, 422]);
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_la_foto_se_guarda_con_nombre_aleatorio(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);

        $r = $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera), ['foto' => $this->png('../../evil name.png')]);
        $this->assertSame(302, $r->getStatusCode());

        $foto = $this->pdo->query('SELECT foto FROM alumnos')->fetchColumn();
        $this->assertMatchesRegularExpression('#^uploads/[a-f0-9]{32}\.png$#', $foto);
    }

    // ---------- Asignación masiva ----------

    public function test_campos_no_permitidos_son_ignorados(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);

        $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera, ['id' => 9999, 'created_at' => '2000-01-01 00:00:00']));

        $fila = $this->pdo->query('SELECT id, created_at FROM alumnos')->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($fila, 'No se creó el alumno');
        $this->assertNotEquals(9999, $fila['id']);
        $this->assertStringStartsNotWith('2000-01-01', $fila['created_at']);
    }

    // ---------- CSRF ----------

    public function test_post_sin_token_csrf_es_rechazado(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);

        $r = $this->http->post('/alumnos/crear', ['form_params' => $this->datosAlumno($carrera)]);

        $this->assertSame(403, $r->getStatusCode());
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_post_con_token_falso_es_rechazado(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $this->get('/alumnos/nuevo'); // inicia la sesión

        $r = $this->http->post('/alumnos/crear', ['form_params' => $this->datosAlumno($carrera, ['token' => 'falso', 'csrf' => 'falso'])]);

        $this->assertSame(403, $r->getStatusCode());
        $this->assertSame(0, $this->contar('alumnos'));
    }

    // ---------- Métodos HTTP ----------

    public function test_eliminar_no_funciona_por_get(): void
    {
        $id = $this->crearAlumno('Ana');

        $this->assertSame(404, $this->get("/alumnos/{$id}/eliminar")->getStatusCode());
        $this->assertSame(1, $this->contar('alumnos'));
    }

    public function test_eliminar_por_post_sin_token_es_rechazado(): void
    {
        $id = $this->crearAlumno('Ana');

        $r = $this->http->post("/alumnos/{$id}/eliminar");

        $this->assertSame(403, $r->getStatusCode());
        $this->assertSame(1, $this->contar('alumnos'));
    }

    // ---------- Cabeceras, cookies y errores ----------

    public function test_cabeceras_de_seguridad_presentes(): void
    {
        $r = $this->get('/alumnos');
        $this->assertTrue($r->hasHeader('X-Frame-Options'));
        $this->assertSame('nosniff', $r->getHeaderLine('X-Content-Type-Options'));
        $this->assertTrue($r->hasHeader('Referrer-Policy'));
        $this->assertTrue($r->hasHeader('Content-Security-Policy'));
    }

    public function test_cookie_de_sesion_es_httponly(): void
    {
        $cliente = new \GuzzleHttp\Client([
            'base_uri'    => getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081',
            'http_errors' => false,
        ]);
        $r = $cliente->get('/alumnos/nuevo'); // primera petición sin cookies para recibir Set-Cookie
        $this->assertNotEmpty($r->getHeaderLine('Set-Cookie'), 'No se envió cookie de sesión');
        $this->assertStringContainsStringIgnoringCase('HttpOnly', $r->getHeaderLine('Set-Cookie'));
    }

    public function test_los_errores_no_filtran_detalles_internos(): void
    {
        $html = $this->cuerpo($this->get('/ruta/que/no/existe'));
        foreach (['Stack trace', 'Phalcon\\', 'Exception', '.php on line'] as $pista) {
            $this->assertStringNotContainsString($pista, $html);
        }
    }
}
