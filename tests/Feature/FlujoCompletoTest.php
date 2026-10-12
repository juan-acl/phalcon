<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Sigue la información de punta a punta:
 * formulario -> validación -> base de datos -> vista -> reporte.
 * Las rutas salen de docs/02-ARQUITECTURA.md.
 */
class FlujoCompletoTest extends HttpTestCase
{
    private function idPorNombre(string $tabla, string $nombre): int
    {
        $st = $this->pdo->prepare("SELECT id FROM `$tabla` WHERE nombre = ?");
        $st->execute([$nombre]);
        return (int) $st->fetchColumn();
    }

    public function test_la_informacion_viaja_completa_por_el_sistema(): void
    {
        // 1. Catálogos
        $catalogos = [
            'carreras'  => 'Computación',
            'cursos'    => 'Matemática I',
            'secciones' => 'A',
            'semestres' => 'Primer semestre 2026',
        ];
        foreach ($catalogos as $tipo => $nombre) {
            $r = $this->submit("/catalogos/{$tipo}/nuevo", ['nombre' => $nombre]);
            $this->assertSame(302, $r->getStatusCode(), "Crear en {$tipo}");
            $this->assertGreaterThan(0, $this->idPorNombre($tipo, $nombre), "No se guardó en {$tipo}");
        }

        // 2. Alumno con foto
        $r = $this->submit('/alumnos/nuevo', [
            'nombres' => 'Ana María',
            'apellidos' => 'Pérez López',
            'fecha_nacimiento' => '2008-05-14',
            'carrera_id' => $this->idPorNombre('carreras', 'Computación'),
        ], ['foto' => $this->png('mi foto.png')]);
        $this->assertSame(302, $r->getStatusCode());

        $alumno = $this->pdo->query('SELECT * FROM alumnos')->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('Pérez López', $alumno['apellidos']);
        $this->assertNotEmpty($alumno['foto']);
        $this->assertFileExists($this->raiz . '/public/' . $alumno['foto']);

        // 3. Nota
        $r = $this->submit("/alumnos/{$alumno['id']}/notas/nueva", [
            'curso_id' => $this->idPorNombre('cursos', 'Matemática I'),
            'semestre_id' => $this->idPorNombre('semestres', 'Primer semestre 2026'),
            'seccion_id' => $this->idPorNombre('secciones', 'A'),
            'nota' => '85',
        ]);
        $this->assertSame(302, $r->getStatusCode());
        $this->assertSame(1, $this->contar('notas'));
        $this->assertEquals(85, $this->pdo->query('SELECT nota FROM notas')->fetchColumn());

        // 4. Consulta del alumno: datos + historial
        $r = $this->get("/alumnos/{$alumno['id']}");
        $this->assertSame(200, $r->getStatusCode());
        $html = $this->cuerpo($r);
        foreach (['Ana María', 'Pérez López', 'Matemática I', '85'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }

        // 5. Reportes
        $html = $this->cuerpo($this->get('/reportes/alumnos-por-carrera'));
        $this->assertStringContainsString('Computación', $html);
        $this->assertStringContainsString('Pérez López', $html);

        $r = $this->get('/reportes/notas', ['carrera_id' => $this->idPorNombre('carreras', 'Computación')]);
        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('Matemática I', $this->cuerpo($r));
    }
}
