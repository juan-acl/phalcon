<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\HttpTestCase;

class ValidacionTest extends HttpTestCase
{
    /** Cuando hay errores de validación el sistema NO redirige (no guarda) y NO falla con 500. */
    private function assertRechazado(int $status): void
    {
        $this->assertContains($status, [200, 422], "Se esperaba que el formulario se volviera a mostrar, llegó {$status}");
    }

    private function datosNota(): array
    {
        return [
            'alumno'   => $this->crearAlumno('Luis', 'Gómez'),
            'curso'    => $this->insertar('cursos', ['nombre' => 'Física']),
            'seccion'  => $this->insertar('secciones', ['nombre' => 'B']),
            'semestre' => $this->insertar('semestres', ['nombre' => 'Segundo semestre 2026']),
        ];
    }

    public function test_alumno_sin_campos_obligatorios_es_rechazado(): void
    {
        $r = $this->submit('/alumnos/nuevo', []);
        $this->assertRechazado($r->getStatusCode());
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_fecha_futura_es_rechazada(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Computación']);
        $r = $this->submit('/alumnos/nuevo', [
            'nombres' => 'X', 'apellidos' => 'Y',
            'fecha_nacimiento' => date('Y-m-d', strtotime('+1 year')),
            'carrera_id' => $carrera,
        ]);
        $this->assertRechazado($r->getStatusCode());
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public function test_carrera_inexistente_es_rechazada(): void
    {
        $r = $this->submit('/alumnos/nuevo', [
            'nombres' => 'X', 'apellidos' => 'Y',
            'fecha_nacimiento' => '2008-01-01', 'carrera_id' => 99999,
        ]);
        $this->assertRechazado($r->getStatusCode());
        $this->assertSame(0, $this->contar('alumnos'));
    }

    public static function notasInvalidas(): array
    {
        return [[-1], [101], [150], ['abc'], ['']];
    }

    #[DataProvider('notasInvalidas')]
    public function test_notas_fuera_de_rango_o_no_numericas_son_rechazadas($valor): void
    {
        $d = $this->datosNota();
        $r = $this->submit("/alumnos/{$d['alumno']}/notas/nueva", [
            'curso_id' => $d['curso'], 'semestre_id' => $d['semestre'],
            'seccion_id' => $d['seccion'], 'nota' => $valor,
        ]);
        $this->assertRechazado($r->getStatusCode());
        $this->assertSame(0, $this->contar('notas'));
    }

    public function test_no_se_permite_nota_duplicada_para_mismo_curso_y_semestre(): void
    {
        $d = $this->datosNota();
        $campos = [
            'curso_id' => $d['curso'], 'semestre_id' => $d['semestre'],
            'seccion_id' => $d['seccion'], 'nota' => '70',
        ];

        $this->assertSame(302, $this->submit("/alumnos/{$d['alumno']}/notas/nueva", $campos)->getStatusCode());
        $segunda = $this->submit("/alumnos/{$d['alumno']}/notas/nueva", $campos);

        $this->assertNotSame(500, $segunda->getStatusCode());
        $this->assertSame(1, $this->contar('notas'));
    }

    public function test_alumno_inexistente_da_404(): void
    {
        $this->assertSame(404, $this->get('/alumnos/99999')->getStatusCode());
    }

    public function test_id_no_numerico_da_404(): void
    {
        $this->assertSame(404, $this->get('/alumnos/abc')->getStatusCode());
    }
}
