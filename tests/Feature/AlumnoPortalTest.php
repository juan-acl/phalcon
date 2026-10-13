<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

class AlumnoPortalTest extends HttpTestCase
{
    public function test_alumno_puede_ver_su_perfil(): void
    {
        $this->loginComo('alumno');
        $resp = $this->get('/mi/perfil');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Mis Clases Inscritas', $body);
        $this->assertStringContainsString('Carnet', $body);
    }

    public function test_alumno_puede_ver_sus_notas_publicadas(): void
    {
        $carreraId = $this->insertar('carreras', ['nombre' => 'Carrera Test ' . bin2hex(random_bytes(2))]);
        $alumnoId = $this->insertar('alumnos', [
            'nombres'          => 'Estudiante',
            'apellidos'        => 'Notas',
            'fecha_nacimiento' => '2008-01-01',
            'carrera_id'       => $carreraId,
        ]);
        $this->loginComo('alumno', ['alumno_id' => $alumnoId]);

        $cursoId = $this->insertar('cursos', ['nombre' => 'Historia ' . bin2hex(random_bytes(2))]);
        $seccionId = $this->insertar('secciones', ['nombre' => 'H']);
        $semestreId = $this->insertar('semestres', ['nombre' => 'Semestre H ' . bin2hex(random_bytes(2)), 'cerrado' => 0]);

        $this->insertar('notas', [
            'alumno_id'    => $alumnoId,
            'curso_id'     => $cursoId,
            'seccion_id'   => $seccionId,
            'semestre_id'  => $semestreId,
            'nota'         => 92.00,
            'estado'       => 'publicada',
            'publicada_at' => date('Y-m-d H:i:s'),
        ]);

        $resp = $this->get('/mi/notas');
        $this->assertSame(200, $resp->getStatusCode());
        $body = (string) $resp->getBody();
        $this->assertStringContainsString('Mis Calificaciones Oficiales', $body);
        $this->assertStringContainsString('92', $body);
        $this->assertStringContainsString('Aprobado', $body);
    }

    public function test_alumno_no_puede_ver_ficha_de_otro_alumno_retorna_404(): void
    {
        $propioId = $this->crearAlumno('Propio', 'Alumno');
        $otroId = $this->crearAlumno('Otro', 'Estudiante');

        $this->loginComo('alumno', ['alumno_id' => $propioId]);
        $resp = $this->get("/alumnos/{$otroId}");
        $this->assertSame(404, $resp->getStatusCode(), 'Ver alumno ajeno debe retornar 404');

        $respFicha = $this->get("/alumnos/{$otroId}/ficha?formato=pdf");
        $this->assertSame(404, $respFicha->getStatusCode(), 'Descargar ficha ajena debe retornar 404');
    }

    public function test_alumno_no_puede_acceder_a_modulos_administrativos(): void
    {
        $this->loginComo('alumno');

        $rutas = ['/tablero', '/profesores', '/clases', '/inscripciones', '/auditoria'];
        foreach ($rutas as $ruta) {
            $resp = $this->get($ruta);
            $this->assertSame(403, $resp->getStatusCode(), "Alumno intentando entrar a {$ruta} debe recibir 403");
        }
    }
}
