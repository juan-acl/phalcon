<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas de edición de notas, motivo obligatorio, bloqueo concurrente,
 * auditoría y reglas de negocio con semestres cerrados.
 */
class EdicionNotasTest extends HttpTestCase
{
    private function crearNotaBase(int $valor = 75, int $semestreCerrado = 0): array
    {
        $carreraId  = $this->insertar('carreras', ['nombre' => 'Carrera ' . bin2hex(random_bytes(3))]);
        $alumnoId   = $this->insertar('alumnos', [
            'nombres'          => 'Estudiante',
            'apellidos'        => 'Evaluado',
            'fecha_nacimiento' => '2008-01-01',
            'carrera_id'       => $carreraId,
        ]);
        $cursoId    = $this->insertar('cursos', ['nombre' => 'Curso ' . bin2hex(random_bytes(3))]);
        $seccionId  = $this->insertar('secciones', ['nombre' => 'Sec ' . bin2hex(random_bytes(3))]);
        $semestreId = $this->insertar('semestres', [
            'nombre'  => 'Semestre ' . bin2hex(random_bytes(3)),
            'cerrado' => $semestreCerrado,
        ]);

        $notaId = $this->insertar('notas', [
            'alumno_id'   => $alumnoId,
            'curso_id'    => $cursoId,
            'semestre_id' => $semestreId,
            'seccion_id'  => $seccionId,
            'nota'        => $valor,
            'estado'      => 'publicada',
        ]);

        return [
            'alumno_id'   => $alumnoId,
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'nota_id'     => $notaId,
        ];
    }

    public function test_edicion_de_nota_exige_motivo_minimo_10_caracteres(): void
    {
        $info = $this->crearNotaBase(70);

        // 1. Sin motivo
        $r = $this->submit("/notas/{$info['nota_id']}/editar", [
            'nota'   => '85',
            'estado' => 'publicada',
            'motivo' => '',
        ]);
        $this->assertSame(422, $r->getStatusCode());
        $this->assertStringContainsString('motivo', $this->cuerpo($r));

        // 2. Motivo demasiado corto (< 10 caracteres)
        $r = $this->submit("/notas/{$info['nota_id']}/editar", [
            'nota'   => '85',
            'estado' => 'publicada',
            'motivo' => 'Error',
        ]);
        $this->assertSame(422, $r->getStatusCode());

        // Verificar que la nota en base de datos NO cambió
        $notaDb = $this->pdo->query("SELECT nota FROM notas WHERE id = {$info['nota_id']}")->fetchColumn();
        $this->assertEquals(70, $notaDb);
    }

    public function test_edicion_de_nota_guarda_en_notas_auditoria_con_transaccion(): void
    {
        $info = $this->crearNotaBase(70);

        $r = $this->submit("/notas/{$info['nota_id']}/editar", [
            'nota'   => '88.5',
            'estado' => 'publicada',
            'motivo' => 'Corrección por revisión de examen final extraordinario',
        ]);

        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString("alumnos/{$info['alumno_id']}", $r->getHeaderLine('Location'));

        // Nota actualizada en tabla principal
        $notaDb = $this->pdo->query("SELECT nota FROM notas WHERE id = {$info['nota_id']}")->fetchColumn();
        $this->assertEquals(88.5, $notaDb);

        // Registro de auditoría creado
        $aud = $this->pdo->query("SELECT * FROM notas_auditoria WHERE nota_id = {$info['nota_id']} ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($aud);
        $this->assertSame('editar', $aud['accion']);
        $this->assertEquals(70.0, (float) $aud['nota_anterior']);
        $this->assertEquals(88.5, (float) $aud['nota_nueva']);
        $this->assertStringContainsString('revisión de examen', $aud['motivo']);
        $this->assertNotNull($aud['usuario_id']);
    }

    public function test_bloqueo_optimista_rechaza_cambio_si_la_nota_fue_modificada(): void
    {
        $info = $this->crearNotaBase(60);

        // Simular que la nota ya tiene un updated_at en BD
        $this->pdo->exec("UPDATE notas SET updated_at = '2026-10-10 10:00:00' WHERE id = {$info['nota_id']}");

        // Envía una edición pero con un token desactualizado
        $r = $this->submit("/notas/{$info['nota_id']}/editar", [
            'nota'             => '75',
            'estado'           => 'publicada',
            'motivo'           => 'Ajuste de calificación ordinaria',
            'updated_at_token' => '2026-10-09 08:00:00', // token viejo
        ]);

        $this->assertSame(422, $r->getStatusCode());
        $this->assertStringContainsString('modificada por otro usuario', $this->cuerpo($r));

        // La nota en BD sigue siendo 60
        $notaDb = $this->pdo->query("SELECT nota FROM notas WHERE id = {$info['nota_id']}")->fetchColumn();
        $this->assertEquals(60, $notaDb);
    }

    public function test_semestre_cerrado_permite_director_pero_rechaza_profesor(): void
    {
        $info = $this->crearNotaBase(65, 1); // Semestre cerrado = 1

        // 1. Director puede editar notas de semestre cerrado
        $this->loginComo('director');
        $r = $this->submit("/notas/{$info['nota_id']}/editar", [
            'nota'   => '80',
            'estado' => 'publicada',
            'motivo' => 'Corrección autorizada por resolución de dirección escolar',
        ]);
        $this->assertSame(302, $r->getStatusCode());

        $notaDb = $this->pdo->query("SELECT nota FROM notas WHERE id = {$info['nota_id']}")->fetchColumn();
        $this->assertEquals(80, $notaDb);

        // 2. Profesor intenta abrir edición de nota en semestre cerrado -> redirigido con error
        $this->loginComo('profesor');
        $r = $this->get("/notas/{$info['nota_id']}/editar");
        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString("alumnos/{$info['alumno_id']}", $r->getHeaderLine('Location'));

        $notaDb = $this->pdo->query("SELECT nota FROM notas WHERE id = {$info['nota_id']}")->fetchColumn();
        $this->assertEquals(80, $notaDb);
    }

    public function test_eliminacion_de_nota_registra_en_auditoria(): void
    {
        $this->loginComo('director');
        $info = $this->crearNotaBase(95);

        $r = $this->submit("/alumnos/{$info['alumno_id']}", [], [], "/notas/{$info['nota_id']}/eliminar");
        $this->assertSame(302, $r->getStatusCode());

        // La nota ya no existe en la tabla principal
        $existe = $this->pdo->query("SELECT COUNT(*) FROM notas WHERE id = {$info['nota_id']}")->fetchColumn();
        $this->assertSame(0, (int) $existe);

        // Pero la bitácora de auditoría conserva el registro de eliminación
        $aud = $this->pdo->query("SELECT * FROM notas_auditoria WHERE nota_id = {$info['nota_id']} AND accion = 'eliminar'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($aud);
        $this->assertEquals(95.0, (float) $aud['nota_anterior']);
        $this->assertNull($aud['nota_nueva']);
    }

    public function test_notas_en_borrador_no_son_visibles_para_rol_alumno(): void
    {
        $info = $this->crearNotaBase(90);

        // Marcar la nota como borrador
        $this->pdo->exec("UPDATE notas SET estado = 'borrador' WHERE id = {$info['nota_id']}");

        // Iniciar sesión como ese alumno
        $this->loginComo('alumno', ['alumno_id' => $info['alumno_id']]);

        // Ficha web
        $r = $this->get("/alumnos/{$info['alumno_id']}");
        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringNotContainsString('90', $this->cuerpo($r));
        $this->assertStringContainsString('aún no tiene notas', $this->cuerpo($r));

        // Boleta PDF
        $rPdf = $this->get("/alumnos/{$info['alumno_id']}/ficha?formato=pdf");
        $this->assertSame(200, $rPdf->getStatusCode());
        $this->assertSame('application/pdf', $rPdf->getHeaderLine('Content-Type'));

        // Como director sí es visible
        $this->loginComo('director');
        $rDir = $this->get("/alumnos/{$info['alumno_id']}");
        $this->assertStringContainsString('90', $this->cuerpo($rDir));
        $this->assertStringContainsString('Borrador', $this->cuerpo($rDir));
    }
}
