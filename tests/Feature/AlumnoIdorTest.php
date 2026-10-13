<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas de seguridad IDOR (Insecure Direct Object Reference) para el rol Alumno:
 * un alumno solo puede acceder a sus propios datos y boleta académica.
 */
class AlumnoIdorTest extends HttpTestCase
{
    public function test_alumno_puede_ver_su_propia_ficha_y_descargar_boleta_pdf(): void
    {
        $carreraId = $this->insertar('carreras', ['nombre' => 'Carrera ' . bin2hex(random_bytes(3))]);
        $alumnoId  = $this->insertar('alumnos', [
            'nombres'          => 'Estudiante',
            'apellidos'        => 'Propio',
            'fecha_nacimiento' => '2008-01-01',
            'carrera_id'       => $carreraId,
        ]);

        $this->loginComo('alumno', ['alumno_id' => $alumnoId]);

        // Ficha HTML
        $rHtml = $this->get("/alumnos/{$alumnoId}");
        $this->assertSame(200, $rHtml->getStatusCode());
        $this->assertStringContainsString('Estudiante', $this->cuerpo($rHtml));

        // Boleta PDF
        $rPdf = $this->get("/alumnos/{$alumnoId}/ficha?formato=pdf");
        $this->assertSame(200, $rPdf->getStatusCode());
        $this->assertSame('application/pdf', $rPdf->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('%PDF-', (string) $rPdf->getBody());
    }

    public function test_alumno_intentando_ver_otro_alumno_recibe_404_estricto_idor(): void
    {
        $carreraId = $this->insertar('carreras', ['nombre' => 'Carrera ' . bin2hex(random_bytes(3))]);
        $alumnoA = $this->insertar('alumnos', [
            'nombres'          => 'Alumno',
            'apellidos'        => 'Legitimo',
            'fecha_nacimiento' => '2008-01-01',
            'carrera_id'       => $carreraId,
        ]);
        $alumnoB = $this->insertar('alumnos', [
            'nombres'          => 'Alumno',
            'apellidos'        => 'Victima',
            'fecha_nacimiento' => '2008-01-01',
            'carrera_id'       => $carreraId,
        ]);

        // Autenticado como Alumno A
        $this->loginComo('alumno', ['alumno_id' => $alumnoA]);

        // Intentar ver la ficha de Alumno B: debe responder 404 (para no revelar que existe)
        $rHtml = $this->get("/alumnos/{$alumnoB}");
        $this->assertSame(404, $rHtml->getStatusCode(), 'Se reveló o permitió acceso a ficha de otro alumno (IDOR)');

        // Intentar descargar el PDF de Alumno B: debe responder 404
        $rPdf = $this->get("/alumnos/{$alumnoB}/ficha?formato=pdf");
        $this->assertSame(404, $rPdf->getStatusCode(), 'Se permitió descargar el PDF de otro alumno (IDOR)');
    }

    public function test_alumno_no_puede_acceder_a_modulos_administrativos(): void
    {
        $alumnoId = $this->crearAlumno('Estudiante', 'Comun');
        $this->loginComo('alumno', ['alumno_id' => $alumnoId]);

        // Rutas denegadas por ACL (403)
        $this->assertSame(403, $this->get('/alumnos')->getStatusCode());
        $this->assertSame(403, $this->get('/alumnos/nuevo')->getStatusCode());
        $this->assertSame(403, $this->get("/alumnos/{$alumnoId}/editar")->getStatusCode());
        $this->assertSame(403, $this->get('/catalogos/carreras')->getStatusCode());
        $this->assertSame(403, $this->get('/catalogos/cursos')->getStatusCode());
        $this->assertSame(403, $this->get('/catalogos/secciones')->getStatusCode());
        $this->assertSame(403, $this->get('/catalogos/semestres')->getStatusCode());
        $this->assertSame(403, $this->get('/reportes/alumnos-por-carrera')->getStatusCode());
        $this->assertSame(403, $this->get('/reportes/notas')->getStatusCode());
        $this->assertSame(403, $this->get('/notas/buscar')->getStatusCode());
    }
}
