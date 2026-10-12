<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas del módulo de Catálogos (carreras, cursos, secciones, semestres).
 * Copiar a tests/Feature/CatalogosTest.php
 */
class CatalogosTest extends HttpTestCase
{
    private function rechazado(int $status): void
    {
        $this->assertContains($status, [200, 422], "Se esperaba volver a ver el formulario, llegó {$status}");
    }

    private function nombreDe(string $tabla, int $id): ?string
    {
        $st = $this->pdo->prepare("SELECT nombre FROM `$tabla` WHERE id = ?");
        $st->execute([$id]);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    // ---------- Crear ----------

    public function test_nombre_duplicado_es_rechazado(): void
    {
        $this->insertar('carreras', ['nombre' => 'Computación']);

        $r = $this->submit('/catalogos/carreras/nuevo', ['nombre' => 'Computación']);

        $this->rechazado($r->getStatusCode());
        $this->assertSame(1, $this->contar('carreras'));
    }

    public function test_nombre_vacio_o_solo_espacios_es_rechazado(): void
    {
        foreach (['', '   '] as $valor) {
            $r = $this->submit('/catalogos/cursos/nuevo', ['nombre' => $valor]);
            $this->rechazado($r->getStatusCode());
        }
        $this->assertSame(0, $this->contar('cursos'));
    }

    public function test_nombre_demasiado_largo_es_rechazado_sin_error_500(): void
    {
        $r = $this->submit('/catalogos/cursos/nuevo', ['nombre' => str_repeat('A', 300)]);

        $this->rechazado($r->getStatusCode());
        $this->assertSame(0, $this->contar('cursos'));
    }

    public function test_inyeccion_sql_en_nombre_se_guarda_como_texto(): void
    {
        $payload = "'; DROP TABLE carreras; --";

        $r = $this->submit('/catalogos/carreras/nuevo', ['nombre' => $payload]);

        $this->assertSame(302, $r->getStatusCode());
        $this->assertSame(1, $this->contar('carreras'));
        $this->assertSame($payload, $this->pdo->query('SELECT nombre FROM carreras')->fetchColumn());
    }

    // ---------- Editar ----------

    public function test_editar_cambia_el_nombre(): void
    {
        $id = $this->insertar('secciones', ['nombre' => 'A']);

        $r = $this->submit("/catalogos/secciones/{$id}/editar", ['nombre' => 'Z']);

        $this->assertSame(302, $r->getStatusCode());
        $this->assertSame('Z', $this->nombreDe('secciones', $id));
    }

    public function test_editar_a_un_nombre_existente_es_rechazado(): void
    {
        $this->insertar('secciones', ['nombre' => 'A']);
        $b = $this->insertar('secciones', ['nombre' => 'B']);

        $r = $this->submit("/catalogos/secciones/{$b}/editar", ['nombre' => 'A']);

        $this->rechazado($r->getStatusCode());
        $this->assertSame('B', $this->nombreDe('secciones', $b));
    }

    // ---------- Eliminar ----------

    public function test_eliminar_un_catalogo_sin_uso_funciona(): void
    {
        $id = $this->insertar('semestres', ['nombre' => 'Primer semestre 2026']);

        $r = $this->submit('/catalogos/semestres', [], [], "/catalogos/semestres/{$id}/eliminar");

        $this->assertSame(302, $r->getStatusCode());
        $this->assertSame(0, $this->contar('semestres'));
    }

    public function test_eliminar_un_catalogo_en_uso_se_bloquea_sin_error_500(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $this->crearAlumno('Ana', 'Pérez', $carrera);

        $r = $this->submit('/catalogos/carreras', [], [], "/catalogos/carreras/{$carrera}/eliminar");

        $this->assertNotSame(500, $r->getStatusCode(), 'Eliminar una carrera en uso provocó error 500');
        $this->assertSame(1, $this->contar('carreras'), 'La carrera en uso fue eliminada');
        $this->assertSame(1, $this->contar('alumnos'));
    }

    // ---------- Seguridad ----------

    public function test_xss_en_nombre_de_catalogo_se_escapa(): void
    {
        $this->insertar('carreras', ['nombre' => '<script>alert(1)</script>']);

        $html = $this->cuerpo($this->get('/catalogos/carreras'));

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_post_sin_token_csrf_es_rechazado_en_catalogos(): void
    {
        $id = $this->insertar('carreras', ['nombre' => 'Computación']);

        $this->assertSame(403, $this->http->post('/catalogos/carreras/crear', ['form_params' => ['nombre' => 'X']])->getStatusCode());
        $this->assertSame(403, $this->http->post("/catalogos/carreras/{$id}/actualizar", ['form_params' => ['nombre' => 'X']])->getStatusCode());
        $this->assertSame(403, $this->http->post("/catalogos/carreras/{$id}/eliminar")->getStatusCode());

        $this->assertSame(1, $this->contar('carreras'));
        $this->assertSame('Computación', $this->nombreDe('carreras', $id));
    }

    public function test_eliminar_no_funciona_por_get_en_catalogos(): void
    {
        $id = $this->insertar('carreras', ['nombre' => 'Computación']);

        $this->assertSame(404, $this->get("/catalogos/carreras/{$id}/eliminar")->getStatusCode());
        $this->assertSame(1, $this->contar('carreras'));
    }

    public function test_tipos_de_catalogo_fuera_de_la_lista_blanca_dan_404(): void
    {
        foreach (['usuarios', 'alumnos', 'notas', 'carreras;DROP', '..%2f..%2fconfig'] as $tipo) {
            $this->assertSame(404, $this->get("/catalogos/{$tipo}")->getStatusCode(), "Tipo aceptado: {$tipo}");
        }
    }

    public function test_catalogo_inexistente_da_404(): void
    {
        $this->assertSame(404, $this->get('/catalogos/carreras/99999/editar')->getStatusCode());
    }

    // ---------- Los 4 catálogos responden ----------

    public function test_los_cuatro_catalogos_listan_sus_datos(): void
    {
        $this->insertar('carreras', ['nombre' => 'Carrera Uno']);
        $this->insertar('cursos', ['nombre' => 'Curso Uno']);
        $this->insertar('secciones', ['nombre' => 'Seccion Uno']);
        $this->insertar('semestres', ['nombre' => 'Semestre Uno']);

        foreach (['carreras' => 'Carrera Uno', 'cursos' => 'Curso Uno', 'secciones' => 'Seccion Uno', 'semestres' => 'Semestre Uno'] as $tipo => $texto) {
            $r = $this->get("/catalogos/{$tipo}");
            $this->assertSame(200, $r->getStatusCode(), "Listado de {$tipo}");
            $this->assertStringContainsString($texto, $this->cuerpo($r));
        }
    }
}
