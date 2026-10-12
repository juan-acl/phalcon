<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas de comportamiento: umbral de aprobación (61), filtros de reportes y manejo de fotos.
 * Copiar a tests/Feature/ComportamientoTest.php
 *
 * SUPUESTOS (ajustar aquí si la app usa otros):
 *  - Reporte de notas: GET /reportes/notas?carrera_id=&curso_id=&seccion_id=
 *  - Reporte por carrera: GET /reportes/alumnos-por-carrera?carrera_id=
 *  - Las tablas HTML muestran una fila (<tr>) por nota/alumno con el texto "Aprobado"/"Reprobado".
 *  - El botón Eliminar de un alumno está en el listado /alumnos; el de una nota, en la ficha /alumnos/{id}.
 */
class ComportamientoTest extends HttpTestCase
{
    private const APROBADO  = '/aprobad/i';   // texto que muestra la app para nota >= 61
    private const REPROBADO = '/reprobad/i';  // texto para nota < 61

    // ---------- Utilidades ----------

    /** Devuelve el <tr> que contiene $texto (falla si no existe). */
    private function fila(string $html, string $texto): string
    {
        foreach (preg_split('/<tr\b/i', $html) as $trozo) {
            if (str_contains($trozo, $texto)) {
                return $trozo;
            }
        }
        $this->fail("No se encontró una fila con '{$texto}'");
    }

    private function nota(int $alumno, int $curso, int $semestre, int $seccion, $valor): int
    {
        return $this->insertar('notas', [
            'alumno_id' => $alumno, 'curso_id' => $curso,
            'semestre_id' => $semestre, 'seccion_id' => $seccion, 'nota' => $valor,
        ]);
    }

    private function datosAlumno(int $carrera, array $extra = []): array
    {
        return array_merge([
            'nombres' => 'Foto', 'apellidos' => 'Uno',
            'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carrera,
        ], $extra);
    }

    // ---------- Umbral de aprobación ----------

    public function test_60_reprueba_y_61_aprueba_en_el_reporte_de_notas(): void
    {
        $carrera  = $this->insertar('carreras', ['nombre' => 'Computación']);
        $curso    = $this->insertar('cursos', ['nombre' => 'Matemática']);
        $semestre = $this->insertar('semestres', ['nombre' => 'Sem 1']);
        $seccion  = $this->insertar('secciones', ['nombre' => 'A']);

        $casos = ['Sesentacero' => 60, 'Sesentanueve' => 60.99, 'Sesentayuno' => 61, 'Cien' => 100];
        foreach ($casos as $apellido => $valor) {
            $a = $this->crearAlumno('N', $apellido, $carrera);
            $this->nota($a, $curso, $semestre, $seccion, $valor);
        }

        $html = $this->cuerpo($this->get('/reportes/notas'));

        foreach (['Sesentacero', 'Sesentanueve'] as $apellido) {
            $fila = $this->fila($html, $apellido);
            $this->assertMatchesRegularExpression(self::REPROBADO, $fila, "{$apellido} debería figurar reprobado");
        }
        foreach (['Sesentayuno', 'Cien'] as $apellido) {
            $fila = $this->fila($html, $apellido);
            $this->assertMatchesRegularExpression(self::APROBADO, $fila, "{$apellido} debería figurar aprobado");
            $this->assertDoesNotMatchRegularExpression(self::REPROBADO, $fila, "{$apellido} no debería figurar reprobado");
        }
    }

    public function test_60_reprueba_y_61_aprueba_en_la_ficha_del_alumno(): void
    {
        $carrera  = $this->insertar('carreras', ['nombre' => 'Computación']);
        $semestre = $this->insertar('semestres', ['nombre' => 'Sem 1']);
        $seccion  = $this->insertar('secciones', ['nombre' => 'A']);
        $cursoMal = $this->insertar('cursos', ['nombre' => 'Cursoalfa']);
        $cursoBien = $this->insertar('cursos', ['nombre' => 'Cursobeta']);

        $alumno = $this->crearAlumno('Ana', 'Pérez', $carrera);
        $this->nota($alumno, $cursoMal, $semestre, $seccion, 60);
        $this->nota($alumno, $cursoBien, $semestre, $seccion, 61);

        $html = $this->cuerpo($this->get("/alumnos/{$alumno}"));

        $this->assertMatchesRegularExpression(self::REPROBADO, $this->fila($html, 'Cursoalfa'), 'La nota 60 debería figurar reprobada');
        $this->assertMatchesRegularExpression(self::APROBADO, $this->fila($html, 'Cursobeta'), 'La nota 61 debería figurar aprobada');
    }

    // ---------- Filtros de reportes ----------

    /** Alfauno: carrera A / curso A / sección 1 · Betados: B / A / 2 · Gamatres: A / B / 2 */
    private function escenarioReportes(): array
    {
        $cA = $this->insertar('carreras', ['nombre' => 'Carrera A']);
        $cB = $this->insertar('carreras', ['nombre' => 'Carrera B']);
        $uA = $this->insertar('cursos', ['nombre' => 'Curso A']);
        $uB = $this->insertar('cursos', ['nombre' => 'Curso B']);
        $s1 = $this->insertar('secciones', ['nombre' => 'S1']);
        $s2 = $this->insertar('secciones', ['nombre' => 'S2']);
        $sem = $this->insertar('semestres', ['nombre' => 'Sem 1']);

        $this->nota($this->crearAlumno('N', 'Alfauno', $cA), $uA, $sem, $s1, 80);
        $this->nota($this->crearAlumno('N', 'Betados', $cB), $uA, $sem, $s2, 70);
        $this->nota($this->crearAlumno('N', 'Gamatres', $cA), $uB, $sem, $s2, 90);

        return compact('cA', 'cB', 'uA', 'uB', 's1', 's2');
    }

    private function assertContiene(array $filtros, array $deben, array $noDeben): void
    {
        $r = $this->get('/reportes/notas', $filtros);
        $this->assertSame(200, $r->getStatusCode());
        $html = $this->cuerpo($r);
        foreach ($deben as $x) {
            $this->assertStringContainsString($x, $html, "Faltó {$x} con filtros " . json_encode($filtros));
        }
        foreach ($noDeben as $x) {
            $this->assertStringNotContainsString($x, $html, "Sobró {$x} con filtros " . json_encode($filtros));
        }
    }

    public function test_reporte_de_notas_filtra_por_carrera(): void
    {
        $e = $this->escenarioReportes();
        $this->assertContiene(['carrera_id' => $e['cA']], ['Alfauno', 'Gamatres'], ['Betados']);
        $this->assertContiene(['carrera_id' => $e['cB']], ['Betados'], ['Alfauno', 'Gamatres']);
    }

    public function test_reporte_de_notas_filtra_por_curso(): void
    {
        $e = $this->escenarioReportes();
        $this->assertContiene(['curso_id' => $e['uA']], ['Alfauno', 'Betados'], ['Gamatres']);
    }

    public function test_reporte_de_notas_filtra_por_seccion(): void
    {
        $e = $this->escenarioReportes();
        $this->assertContiene(['seccion_id' => $e['s2']], ['Betados', 'Gamatres'], ['Alfauno']);
    }

    public function test_reporte_de_notas_combina_filtros(): void
    {
        $e = $this->escenarioReportes();
        $this->assertContiene(['carrera_id' => $e['cA'], 'curso_id' => $e['uA']], ['Alfauno'], ['Betados', 'Gamatres']);
        $this->assertContiene(['carrera_id' => $e['cB'], 'seccion_id' => $e['s1']], [], ['Alfauno', 'Betados', 'Gamatres']);
    }

    public function test_reporte_de_notas_sin_filtros_muestra_todo(): void
    {
        $this->escenarioReportes();
        $this->assertContiene([], ['Alfauno', 'Betados', 'Gamatres'], []);
    }

    public function test_reporte_de_alumnos_por_carrera_filtra(): void
    {
        $e = $this->escenarioReportes();

        $html = $this->cuerpo($this->get('/reportes/alumnos-por-carrera', ['carrera_id' => $e['cA']]));
        $this->assertStringContainsString('Alfauno', $html);
        $this->assertStringContainsString('Gamatres', $html);
        $this->assertStringNotContainsString('Betados', $html);
    }

    // ---------- Fotos ----------

    private function alumnoConFoto(): array
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Carrera Foto']);
        $r = $this->submit('/alumnos/nuevo', $this->datosAlumno($carrera), ['foto' => $this->png()]);
        $this->assertSame(302, $r->getStatusCode(), 'No se pudo crear el alumno con foto');

        $fila = $this->pdo->query('SELECT id, foto, carrera_id FROM alumnos')->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($fila['foto']);
        $this->assertFileExists($this->raiz . '/public/' . $fila['foto']);
        return $fila;
    }

    private function fotoActual(int $id): ?string
    {
        $st = $this->pdo->prepare('SELECT foto FROM alumnos WHERE id = ?');
        $st->execute([$id]);
        return $st->fetchColumn() ?: null;
    }

    public function test_al_cambiar_la_foto_se_elimina_la_anterior(): void
    {
        $a = $this->alumnoConFoto();
        $vieja = $a['foto'];

        $r = $this->submit("/alumnos/{$a['id']}/editar", $this->datosAlumno((int) $a['carrera_id']), ['foto' => $this->png()], "/alumnos/{$a['id']}/actualizar");
        $this->assertSame(302, $r->getStatusCode());

        $nueva = $this->fotoActual((int) $a['id']);
        $this->assertNotSame($vieja, $nueva, 'La foto no cambió');
        $this->assertFileExists($this->raiz . '/public/' . $nueva);
        $this->assertFileDoesNotExist($this->raiz . '/public/' . $vieja, 'La foto anterior quedó huérfana en uploads/');
    }

    public function test_editar_sin_nueva_foto_conserva_la_actual(): void
    {
        $a = $this->alumnoConFoto();

        $r = $this->submit("/alumnos/{$a['id']}/editar", $this->datosAlumno((int) $a['carrera_id'], ['nombres' => 'Cambiado']), [], "/alumnos/{$a['id']}/actualizar");
        $this->assertSame(302, $r->getStatusCode());

        $this->assertSame($a['foto'], $this->fotoActual((int) $a['id']));
        $this->assertFileExists($this->raiz . '/public/' . $a['foto']);
        $this->assertSame('Cambiado', $this->pdo->query('SELECT nombres FROM alumnos')->fetchColumn());
    }

    public function test_editar_con_foto_invalida_no_pierde_la_foto_actual(): void
    {
        $a = $this->alumnoConFoto();
        $malo = $this->archivo('foto.png', '<?php echo 1; ?>', 'image/png');

        $r = $this->submit("/alumnos/{$a['id']}/editar", $this->datosAlumno((int) $a['carrera_id']), ['foto' => $malo], "/alumnos/{$a['id']}/actualizar");

        $this->assertContains($r->getStatusCode(), [200, 422]);
        $this->assertSame($a['foto'], $this->fotoActual((int) $a['id']));
        $this->assertFileExists($this->raiz . '/public/' . $a['foto']);
    }

    public function test_editar_con_datos_invalidos_no_modifica_al_alumno(): void
    {
        $a = $this->alumnoConFoto();

        $r = $this->submit("/alumnos/{$a['id']}/editar", $this->datosAlumno((int) $a['carrera_id'], ['nombres' => '', 'fecha_nacimiento' => 'no-es-fecha']), [], "/alumnos/{$a['id']}/actualizar");

        $this->assertContains($r->getStatusCode(), [200, 422]);
        $this->assertSame('Foto', $this->pdo->query('SELECT nombres FROM alumnos')->fetchColumn());
    }

    public function test_al_eliminar_un_alumno_se_borran_su_foto_y_sus_notas(): void
    {
        $a = $this->alumnoConFoto();
        $curso = $this->insertar('cursos', ['nombre' => 'Física']);
        $sem = $this->insertar('semestres', ['nombre' => 'Sem 1']);
        $sec = $this->insertar('secciones', ['nombre' => 'A']);
        $this->nota((int) $a['id'], $curso, $sem, $sec, 75);

        $r = $this->submit('/alumnos', [], [], "/alumnos/{$a['id']}/eliminar");

        $this->assertSame(302, $r->getStatusCode());
        $this->assertSame(0, $this->contar('alumnos'));
        $this->assertSame(0, $this->contar('notas'), 'Las notas del alumno no se eliminaron');
        $this->assertFileDoesNotExist($this->raiz . '/public/' . $a['foto'], 'La foto quedó huérfana en uploads/');
    }

    // ---------- Notas: eliminar ----------

    public function test_eliminar_una_nota_funciona_y_requiere_token(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $alumno = $this->crearAlumno('Ana', 'Pérez', $carrera);
        $curso = $this->insertar('cursos', ['nombre' => 'Física']);
        $sem = $this->insertar('semestres', ['nombre' => 'Sem 1']);
        $sec = $this->insertar('secciones', ['nombre' => 'A']);
        $nota = $this->nota($alumno, $curso, $sem, $sec, 75);

        // sin token: rechazado y la nota sigue
        $this->assertSame(403, $this->http->post("/notas/{$nota}/eliminar")->getStatusCode());
        $this->assertSame(1, $this->contar('notas'));

        // con token (desde el botón de la ficha)
        $r = $this->submit("/alumnos/{$alumno}", [], [], "/notas/{$nota}/eliminar");
        $this->assertSame(302, $r->getStatusCode());
        $this->assertSame(0, $this->contar('notas'));
        $this->assertSame(1, $this->contar('alumnos'), 'Eliminar la nota no debe eliminar al alumno');
    }
}
