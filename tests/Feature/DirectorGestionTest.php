<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

class DirectorGestionTest extends HttpTestCase
{
    public function test_director_puede_ver_tablero(): void
    {
        $this->loginComo('director');
        $resp = $this->get('/tablero');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Tablero de Control', $body);
        $this->assertStringContainsString('Estudiantes Registrados', $body);
    }

    public function test_director_puede_crear_y_editar_profesor(): void
    {
        $this->loginComo('director');

        $resp = $this->submit('/profesores/nuevo', [
            'nombres'   => 'Carlos',
            'apellidos' => 'Mendoza',
            'email'     => 'cmendoza@escuela.edu.gt',
            'usuario'   => 'cmendoza_' . bin2hex(random_bytes(2)),
            'password'  => 'Password123!',
            'telefono'  => '55554444',
        ], [], '/profesores/crear');

        $this->assertSame(302, $resp->getStatusCode());
        $this->assertDatabaseHas('profesores', [
            'email'     => 'cmendoza@escuela.edu.gt',
            'apellidos' => 'Mendoza',
        ]);

        $profId = (int) $this->pdo->query("SELECT id FROM profesores WHERE email = 'cmendoza@escuela.edu.gt'")->fetchColumn();

        $respEdit = $this->submit("/profesores/{$profId}/editar", [
            'nombres'   => 'Carlos Alberto',
            'apellidos' => 'Mendoza Paz',
            'email'     => 'cmendoza@escuela.edu.gt',
            'telefono'  => '77778888',
        ], [], "/profesores/{$profId}/actualizar");

        $this->assertSame(302, $respEdit->getStatusCode());
        $this->assertDatabaseHas('profesores', [
            'id'        => $profId,
            'nombres'   => 'Carlos Alberto',
            'apellidos' => 'Mendoza Paz',
        ]);
    }

    public function test_director_puede_crear_clase_e_inscribir_alumno(): void
    {
        $this->loginComo('director');

        $cursoId = $this->insertar('cursos', ['nombre' => 'Física General ' . bin2hex(random_bytes(2))]);
        $seccionId = $this->insertar('secciones', ['nombre' => 'Z']);
        $semestreId = $this->insertar('semestres', ['nombre' => 'Ciclo 2026-X ' . bin2hex(random_bytes(2)), 'cerrado' => 0]);
        $profId = $this->insertar('profesores', [
            'nombres'   => 'Roberto',
            'apellidos' => 'Gómez',
            'email'     => 'rgomez_' . bin2hex(random_bytes(2)) . '@escuela.edu.gt',
        ]);
        $carreraId = $this->insertar('carreras', ['nombre' => 'Bachillerato en Ciencias ' . bin2hex(random_bytes(2))]);
        $alumnoId = $this->insertar('alumnos', [
            'nombres'          => 'Estudiante',
            'apellidos'        => 'Ejemplo',
            'fecha_nacimiento' => '2008-05-10',
            'carrera_id'       => $carreraId,
        ]);

        // Crear clase
        $respClase = $this->submit('/clases/nueva', [
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'profesor_id' => $profId,
        ], [], '/clases/crear');

        $this->assertSame(302, $respClase->getStatusCode());
        $this->assertDatabaseHas('clases', [
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'profesor_id' => $profId,
        ]);

        $claseId = (int) $this->pdo->query("SELECT id FROM clases WHERE curso_id = {$cursoId} AND seccion_id = {$seccionId}")->fetchColumn();

        // Inscribir alumno
        $respInsc = $this->submit('/inscripciones/nueva', [
            'clase_id'  => $claseId,
            'alumno_id' => $alumnoId,
        ], [], '/inscripciones/crear');

        $this->assertSame(302, $respInsc->getStatusCode());
        $this->assertDatabaseHas('inscripciones', [
            'clase_id'  => $claseId,
            'alumno_id' => $alumnoId,
        ]);
    }

    public function test_director_puede_cerrar_y_reabrir_semestre(): void
    {
        $this->loginComo('director');
        $semId = $this->insertar('semestres', ['nombre' => 'Semestre Cierre Test ' . bin2hex(random_bytes(2)), 'cerrado' => 0]);

        // Cerrar semestre usando submit sobre catalogos/semestres
        $respCerrar = $this->submit('/catalogos/semestres', [], [], "/catalogos/semestres/{$semId}/cerrar");
        $this->assertSame(302, $respCerrar->getStatusCode());
        $this->assertDatabaseHas('semestres', ['id' => $semId, 'cerrado' => 1]);

        // Reabrir semestre
        $respReabrir = $this->submit('/catalogos/semestres', [], [], "/catalogos/semestres/{$semId}/reabrir");
        $this->assertSame(302, $respReabrir->getStatusCode());
        $this->assertDatabaseHas('semestres', ['id' => $semId, 'cerrado' => 0]);
    }

    public function test_director_puede_ver_auditoria_y_filtrar(): void
    {
        $this->loginComo('director');
        $resp = $this->get('/auditoria');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Auditoría de Calificaciones', $body);
        $this->assertStringContainsString('Acción', $body);
    }
}
