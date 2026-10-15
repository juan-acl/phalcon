<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

class ProfesorPortalTest extends HttpTestCase
{
    private function crearDatosPruebaProfesor(): array
    {
        $cursoId = $this->insertar('cursos', ['nombre' => 'Química I ' . bin2hex(random_bytes(2))]);
        $seccionId = $this->insertar('secciones', ['nombre' => 'Q' . bin2hex(random_bytes(1))]);
        $semestreId = $this->insertar('semestres', ['nombre' => 'Semestre Q ' . bin2hex(random_bytes(2)), 'cerrado' => 0]);
        $carreraId = $this->insertar('carreras', ['nombre' => 'Carrera Q ' . bin2hex(random_bytes(2))]);

        $profId = $this->insertar('profesores', [
            'nombres'   => 'Profesor',
            'apellidos' => 'Química',
            'email'     => 'prof_q_' . bin2hex(random_bytes(2)) . '@escuela.test',
        ]);

        $alumnoId = $this->insertar('alumnos', [
            'nombres'          => 'Estudiante',
            'apellidos'        => 'Quimica',
            'fecha_nacimiento' => '2008-03-15',
            'carrera_id'       => $carreraId,
        ]);

        $claseId = $this->insertar('clases', [
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'profesor_id' => $profId,
        ]);

        $this->insertar('inscripciones', [
            'clase_id'  => $claseId,
            'alumno_id' => $alumnoId,
        ]);

        return [
            'profId'     => $profId,
            'alumnoId'   => $alumnoId,
            'claseId'    => $claseId,
            'cursoId'    => $cursoId,
            'seccionId'  => $seccionId,
            'semestreId' => $semestreId,
            'carreraId'  => $carreraId,
        ];
    }

    public function test_profesor_puede_ver_sus_clases_asignadas(): void
    {
        $datos = $this->crearDatosPruebaProfesor();
        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);

        $resp = $this->get('/profesor/clases');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Mis Clases Asignadas', $body);
        $this->assertStringContainsString((string) $datos['claseId'], $body);
    }

    public function test_profesor_puede_ver_su_clase_y_alumnos_inscritos(): void
    {
        $datos = $this->crearDatosPruebaProfesor();
        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);

        $resp = $this->get("/profesor/clases/{$datos['claseId']}");
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Quimica', $body);
        $this->assertStringContainsString('Nómina de Alumnos y Calificaciones', $body);
    }

    public function test_profesor_no_puede_ver_clase_ajena_retorna_404(): void
    {
        $datos = $this->crearDatosPruebaProfesor();
        $otroProfId = $this->insertar('profesores', [
            'nombres'   => 'Otro',
            'apellidos' => 'Docente',
            'email'     => 'otro_' . bin2hex(random_bytes(2)) . '@escuela.test',
        ]);
        $otroCursoId = $this->insertar('cursos', ['nombre' => 'Curso Ajeno ' . bin2hex(random_bytes(2))]);
        $claseAjenaId = $this->insertar('clases', [
            'curso_id'    => $otroCursoId,
            'seccion_id'  => $datos['seccionId'],
            'semestre_id' => $datos['semestreId'],
            'profesor_id' => $otroProfId,
        ]);

        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);
        $resp = $this->get("/profesor/clases/{$claseAjenaId}");
        $this->assertSame(404, $resp->getStatusCode(), 'Acceder a clase de otro profesor debe responder 404');
    }

    public function test_profesor_puede_guardar_borrador_y_luego_publicar(): void
    {
        $datos = $this->crearDatosPruebaProfesor();
        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);

        // Guardar nota como borrador usando submit
        $campoNota = 'notas[' . $datos['alumnoId'] . ']';
        $respGuardar = $this->submit(
            "/profesor/clases/{$datos['claseId']}",
            [$campoNota => '78.50'],
            [],
            "/profesor/clases/{$datos['claseId']}/notas"
        );
        $this->assertSame(302, $respGuardar->getStatusCode());

        $this->assertDatabaseHas('notas', [
            'alumno_id' => $datos['alumnoId'],
            'curso_id'  => $datos['cursoId'],
            'nota'      => 78.50,
            'estado'    => 'borrador',
        ]);

        // Alumno consulta sus notas -> no debe ver la nota en borrador
        $this->loginComo('alumno', ['alumno_id' => $datos['alumnoId']]);
        $respAlu = $this->get('/mi/notas');
        $this->assertSame(200, $respAlu->getStatusCode());
        $this->assertStringNotContainsString('78.5', (string) $respAlu->getBody());

        // Profesor publica las calificaciones
        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);
        $respPub = $this->submit(
            "/profesor/clases/{$datos['claseId']}",
            [],
            [],
            "/profesor/clases/{$datos['claseId']}/publicar"
        );
        $this->assertSame(302, $respPub->getStatusCode());

        $this->assertDatabaseHas('notas', [
            'alumno_id' => $datos['alumnoId'],
            'curso_id'  => $datos['cursoId'],
            'estado'    => 'publicada',
        ]);

        // Alumno vuelve a consultar -> ahora SÍ la ve como publicada y aprobada
        $this->loginComo('alumno', ['alumno_id' => $datos['alumnoId']]);
        $respAlu2 = $this->get('/mi/notas');
        $this->assertSame(200, $respAlu2->getStatusCode());
        $bodyAlu = (string) $respAlu2->getBody();
        $this->assertStringContainsString('78.5', $bodyAlu);
        $this->assertStringContainsString('Aprobado', $bodyAlu);
    }

    public function test_profesor_no_puede_editar_nota_de_otra_clase_retorna_404(): void
    {
        $datos = $this->crearDatosPruebaProfesor();
        $otroCursoId = $this->insertar('cursos', ['nombre' => 'Curso Externo ' . bin2hex(random_bytes(2))]);
        $notaAjenaId = $this->insertar('notas', [
            'alumno_id'   => $datos['alumnoId'],
            'curso_id'    => $otroCursoId,
            'semestre_id' => $datos['semestreId'],
            'seccion_id'  => $datos['seccionId'],
            'nota'        => 50,
            'estado'      => 'publicada',
        ]);

        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);
        $resp = $this->get("/notas/{$notaAjenaId}/editar");
        $this->assertSame(404, $resp->getStatusCode(), 'Editar nota de un curso que no imparte debe responder 404');
    }

    public function test_profesor_en_buscar_notas_solo_ve_sus_alumnos(): void
    {
        $datos = $this->crearDatosPruebaProfesor();

        // Alumno ajeno no inscrito con este profesor
        $alumnoAjenoId = $this->insertar('alumnos', [
            'nombres'          => 'Alumno',
            'apellidos'        => 'AjenoDesconocido',
            'fecha_nacimiento' => '2008-01-01',
            'carrera_id'       => $datos['carreraId'],
        ]);

        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);
        $resp = $this->get('/notas/buscar');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();

        // Debe ver a su alumno inscrito pero NO al alumno ajeno
        $this->assertStringContainsString('Quimica', $body);
        $this->assertStringNotContainsString('AjenoDesconocido', $body);
    }

    public function test_profesor_no_puede_ingresar_nota_en_curso_no_asignado(): void
    {
        $datos = $this->crearDatosPruebaProfesor();
        $otroCursoId = $this->insertar('cursos', ['nombre' => 'Física No Asignada']);

        $this->loginComo('profesor', ['profesor_id' => $datos['profId']]);
        $resp = $this->submit("/alumnos/{$datos['alumnoId']}/notas/nueva", [
            'curso_id'    => $otroCursoId,
            'seccion_id'  => $datos['seccionId'],
            'semestre_id' => $datos['semestreId'],
            'nota'        => '80',
            'estado'      => 'publicada',
        ]);

        $this->assertSame(422, $resp->getStatusCode());
        $this->assertStringContainsString('Usted no tiene asignada esta asignatura', (string) $resp->getBody());
        $this->assertSame(0, $this->contar('notas', "curso_id = {$otroCursoId}"));
    }
}
