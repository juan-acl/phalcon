<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

class ManejoErroresTest extends HttpTestCase
{
    public function test_pagina_404_muestra_motivo_claro_sin_codigo_crudo(): void
    {
        $resp = $this->get('/ruta-inexistente-xyz-987');
        $this->assertSame(404, $resp->getStatusCode());
        $body = (string) $resp->getBody();

        // No debe mostrar un encabezado técnico "404"
        $this->assertStringNotContainsString('<h1>404</h1>', $body);

        // Debe explicar con claridad el motivo y sugerencias
        $this->assertStringContainsString('Página o Recurso No Encontrado', $body);
        $this->assertStringContainsString('Motivo del inconveniente:', $body);
        $this->assertStringContainsString('Sugerencias:', $body);
        $this->assertStringContainsString('Regresar', $body);
        $this->assertStringContainsString('Ir al Inicio', $body);
    }

    public function test_acceso_prohibido_403_muestra_motivo_claro_sin_codigo_crudo(): void
    {
        // Petición POST sin token CSRF produce 403
        $resp = $this->http->post('/alumnos/crear', [
            'form_params' => ['nombres' => 'Prueba'],
        ]);
        $this->assertSame(403, $resp->getStatusCode());
        $body = (string) $resp->getBody();

        // No debe mostrar "403" como título crudo
        $this->assertStringNotContainsString('<h1>403</h1>', $body);

        // Debe explicar con claridad el motivo
        $this->assertStringContainsString('Acceso Restringido o Formulario Expirado', $body);
        $this->assertStringContainsString('Motivo del inconveniente:', $body);
        $this->assertStringContainsString('Qué debes hacer:', $body);
    }

    public function test_eliminar_clase_con_alumnos_muestra_motivo_claro(): void
    {
        $carreraId  = $this->insertar('carreras', ['nombre' => 'Carrera Error Test']);
        $alumnoId   = $this->insertar('alumnos', [
            'nombres'          => 'Alumno',
            'apellidos'        => 'Inscrito',
            'fecha_nacimiento' => '2005-05-15',
            'carrera_id'       => $carreraId,
        ]);
        $cursoId    = $this->insertar('cursos', ['nombre' => 'Curso Error Test']);
        $seccionId  = $this->insertar('secciones', ['nombre' => 'Seccion Error Test']);
        $semestreId = $this->insertar('semestres', ['nombre' => 'Semestre Error Test']);

        $claseId = $this->insertar('clases', [
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
        ]);

        $this->insertar('inscripciones', [
            'alumno_id' => $alumnoId,
            'clase_id'  => $claseId,
        ]);

        // Intentar eliminar clase con alumnos inscritos
        $resp = $this->submit('/clases', [], [], "/clases/{$claseId}/eliminar");
        $this->assertSame(302, $resp->getStatusCode());

        // Ver el listado con el mensaje flash
        $respListado = $this->get('/clases');
        $body = (string) $respListado->getBody();

        // Debe explicar el motivo académico amigable
        $this->assertStringContainsString('No se puede eliminar la clase porque tiene 1 estudiante(s) inscrito(s)', $body);
        $this->assertStringContainsString('Debe anular las inscripciones primero', $body);

        // NUNCA debe filtrar errores SQL o códigos SQLSTATE
        $this->assertStringNotContainsString('SQLSTATE', $body);
        $this->assertStringNotContainsString('Integrity constraint violation', $body);
        $this->assertStringNotContainsString('foreign key constraint', $body);
    }

    public function test_eliminar_catalogo_en_uso_muestra_motivo_especifico_sin_sql(): void
    {
        $carreraId = $this->insertar('carreras', ['nombre' => 'Arquitectura Test']);
        $this->insertar('alumnos', [
            'nombres'          => 'Estudiante',
            'apellidos'        => 'Matriculado',
            'fecha_nacimiento' => '2006-01-01',
            'carrera_id'       => $carreraId,
        ]);

        $resp = $this->submit('/catalogos/carreras', [], [], "/catalogos/carreras/{$carreraId}/eliminar");
        $this->assertSame(302, $resp->getStatusCode());

        $respListado = $this->get('/catalogos/carreras');
        $body = (string) $respListado->getBody();

        $this->assertStringContainsString('No se puede eliminar la carrera porque tiene 1 estudiante(s) matriculado(s)', $body);
        $this->assertStringNotContainsString('SQLSTATE', $body);
    }

    public function test_eliminar_docente_con_clases_muestra_motivo_especifico(): void
    {
        $profId = $this->insertar('profesores', [
            'nombres'   => 'Docente',
            'apellidos' => 'Asignado',
            'email'     => 'docente.asignado@test.edu',
        ]);
        $cursoId    = $this->insertar('cursos', ['nombre' => 'Química Test']);
        $seccionId  = $this->insertar('secciones', ['nombre' => 'Sec Docente Test']);
        $semestreId = $this->insertar('semestres', ['nombre' => 'Sem Docente Test']);

        $this->insertar('clases', [
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'profesor_id' => $profId,
        ]);

        $resp = $this->submit('/profesores', [], [], "/profesores/{$profId}/eliminar");
        $this->assertSame(302, $resp->getStatusCode());

        $respListado = $this->get('/profesores');
        $body = (string) $respListado->getBody();

        $this->assertStringContainsString('tiene 1 clase(s) asignada(s)', $body);
        $this->assertStringNotContainsString('SQLSTATE', $body);
    }
}
