<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

class AuditoriaExportTest extends HttpTestCase
{
    private function datosAuditoria(): array
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Bachillerato en Computación']);
        $curso   = $this->insertar('cursos', ['nombre' => 'Programación I']);
        $sem     = $this->insertar('semestres', ['nombre' => 'Primer Semestre 2026', 'cerrado' => 1]);
        $sec     = $this->insertar('secciones', ['nombre' => 'A']);
        $alumno  = $this->crearAlumno('Juan', 'Pérez', $carrera);

        $directorId = $this->pdo->query("SELECT id FROM usuarios WHERE rol = 'director' LIMIT 1")->fetchColumn();

        // 1. Inserción normal
        $this->insertar('notas_auditoria', [
            'alumno_id'     => $alumno,
            'curso_id'      => $curso,
            'semestre_id'   => $sem,
            'seccion_id'    => $sec,
            'accion'        => 'crear',
            'nota_anterior' => null,
            'nota_nueva'    => 75.00,
            'motivo'        => 'Ingreso de calificación ordinaria',
            'usuario_id'    => $directorId,
            'created_at'    => '2026-03-20 10:00:00',
        ]);

        // 2. Modificación con cambio mayor (diferencia >= 20 pts)
        $this->insertar('notas_auditoria', [
            'alumno_id'     => $alumno,
            'curso_id'      => $curso,
            'semestre_id'   => $sem,
            'seccion_id'    => $sec,
            'accion'        => 'editar',
            'nota_anterior' => 50.00,
            'nota_nueva'    => 85.00, // +35 pts
            'motivo'        => 'Ajuste de evaluación <script>alert("xss")</script>',
            'usuario_id'    => $directorId,
            'created_at'    => '2026-03-22 14:00:00',
        ]);

        return compact('carrera', 'curso', 'sem', 'sec', 'alumno');
    }

    public function test_director_ve_pantalla_auditoria_con_kpis_y_botones_export(): void
    {
        $this->datosAuditoria();
        $this->loginComo('director');

        $r = $this->get('/auditoria');
        $this->assertSame(200, $r->getStatusCode());

        $html = (string) $r->getBody();
        $this->assertStringContainsString('Auditoría de Calificaciones', $html);
        $this->assertStringContainsString('Descargar PDF', $html);
        $this->assertStringContainsString('Descargar CSV', $html);
        $this->assertStringContainsString('Total Eventos', $html);
        $this->assertStringContainsString('&ge; 20 Puntos', $html);
        $this->assertStringContainsString('Semestre Cerrado', $html);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_director_puede_exportar_auditoria_csv(): void
    {
        $this->datosAuditoria();
        $this->loginComo('director');

        $r = $this->get('/auditoria', ['formato' => 'csv']);
        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('text/csv', $r->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('attachment;', $r->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('reporte-auditoria-notas-', $r->getHeaderLine('Content-Disposition'));

        $cuerpo = (string) $r->getBody();
        // BOM UTF-8
        $this->assertStringStartsWith("\xEF\xBB\xBF", $cuerpo);
        // Separador coma
        $this->assertStringContainsString(',', $cuerpo);
        $this->assertStringContainsString('"Fecha y Hora","Usuario Operador"', $cuerpo);
        $this->assertStringContainsString('Programación I', $cuerpo);
        $this->assertStringContainsString('Pérez, Juan', $cuerpo);
        $this->assertStringContainsString('35.00', $cuerpo);
        $this->assertStringContainsString('SÍ (>= 20 pts)', $cuerpo);
    }

    public function test_director_puede_exportar_auditoria_pdf(): void
    {
        $this->datosAuditoria();
        $this->loginComo('director');

        $r = $this->get('/auditoria', ['formato' => 'pdf']);
        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('application/pdf', $r->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('attachment;', $r->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('reporte-auditoria-notas-', $r->getHeaderLine('Content-Disposition'));

        $cuerpo = (string) $r->getBody();
        $this->assertStringStartsWith('%PDF-', $cuerpo);
        $this->assertGreaterThan(1024, strlen($cuerpo));
    }

    public function test_formato_invalido_devuelve_404(): void
    {
        $this->loginComo('director');
        $r = $this->get('/auditoria', ['formato' => 'exe']);
        $this->assertSame(404, $r->getStatusCode());
    }

    public function test_profesor_y_alumno_tienen_acceso_denegado_a_auditoria(): void
    {
        $this->loginComo('profesor');
        $rProf = $this->get('/auditoria');
        $this->assertSame(403, $rProf->getStatusCode());

        $rProfCsv = $this->get('/auditoria', ['formato' => 'csv']);
        $this->assertSame(403, $rProfCsv->getStatusCode());

        $this->loginComo('alumno');
        $rAlum = $this->get('/auditoria');
        $this->assertSame(403, $rAlum->getStatusCode());

        $rAlumPdf = $this->get('/auditoria', ['formato' => 'pdf']);
        $this->assertSame(403, $rAlumPdf->getStatusCode());
    }
}
