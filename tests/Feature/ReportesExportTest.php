<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas de exportación de reportes a PDF y CSV (Etapa 1).
 * Valida: tipos MIME, cabeceras Content-Disposition fijas, estructura de PDF (%PDF-),
 * delimitador y BOM UTF-8 en CSV, protección contra inyección de fórmulas CSV,
 * rechazo de formatos no permitidos e inmunidad contra SSRF/imágenes externas.
 */
class ReportesExportTest extends HttpTestCase
{
    private function datosPrueba(): array
    {
        $carrera1 = $this->insertar('carreras', ['nombre' => 'Computación']);
        $carrera2 = $this->insertar('carreras', ['nombre' => 'Electricidad']);
        $curso = $this->insertar('cursos', ['nombre' => 'Matemática']);
        $sem = $this->insertar('semestres', ['nombre' => 'Primer Semestre']);
        $sec = $this->insertar('secciones', ['nombre' => 'A']);

        $a1 = $this->crearAlumno('Carlos', 'López', $carrera1);
        $a2 = $this->crearAlumno('María', 'Gómez', $carrera2);

        $this->insertar('notas', [
            'alumno_id'   => $a1,
            'curso_id'    => $curso,
            'semestre_id' => $sem,
            'seccion_id'  => $sec,
            'nota'        => 85,
        ]);
        $this->insertar('notas', [
            'alumno_id'   => $a2,
            'curso_id'    => $curso,
            'semestre_id' => $sem,
            'seccion_id'  => $sec,
            'nota'        => 50,
        ]);

        return compact('carrera1', 'carrera2', 'curso', 'sem', 'sec', 'a1', 'a2');
    }

    public function test_exportar_alumnos_por_carrera_pdf(): void
    {
        $d = $this->datosPrueba();

        $r = $this->get('/reportes/alumnos-por-carrera', ['carrera_id' => $d['carrera1'], 'formato' => 'pdf']);

        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('application/pdf', $r->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('attachment;', $r->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('reporte-alumnos-por-carrera-', $r->getHeaderLine('Content-Disposition'));
        $this->assertStringEndsWith('.pdf"', $r->getHeaderLine('Content-Disposition'));

        $cuerpo = (string) $r->getBody();
        $this->assertStringStartsWith('%PDF-', $cuerpo, 'El archivo debe ser un PDF válido');
        $this->assertGreaterThan(1024, strlen($cuerpo), 'El tamaño del PDF debe ser mayor a 1 KB');
    }

    public function test_exportar_alumnos_por_carrera_csv(): void
    {
        $d = $this->datosPrueba();

        $r = $this->get('/reportes/alumnos-por-carrera', ['carrera_id' => $d['carrera1'], 'formato' => 'csv']);

        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('text/csv', $r->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('attachment;', $r->getHeaderLine('Content-Disposition'));

        $cuerpo = (string) $r->getBody();
        // BOM UTF-8
        $this->assertStringStartsWith("\xEF\xBB\xBF", $cuerpo, 'El CSV debe incluir BOM UTF-8');
        // Separador punto y coma
        $this->assertStringContainsString(';', $cuerpo, 'El CSV debe usar separador punto y coma (;)');
        $this->assertStringContainsString('Computación', $cuerpo);
        $this->assertStringContainsString('López', $cuerpo);
        $this->assertStringNotContainsString('Electricidad', $cuerpo, 'No debe incluir alumnos de otras carreras filtradas');
    }

    public function test_exportar_notas_pdf(): void
    {
        $d = $this->datosPrueba();

        $r = $this->get('/reportes/notas', [
            'carrera_id' => $d['carrera1'],
            'curso_id'   => $d['curso'],
            'seccion_id' => $d['sec'],
            'formato'    => 'pdf',
        ]);

        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('application/pdf', $r->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('attachment;', $r->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('reporte-notas-', $r->getHeaderLine('Content-Disposition'));

        $cuerpo = (string) $r->getBody();
        $this->assertStringStartsWith('%PDF-', $cuerpo);
        $this->assertGreaterThan(1024, strlen($cuerpo));
    }

    public function test_exportar_notas_csv(): void
    {
        $d = $this->datosPrueba();

        $r = $this->get('/reportes/notas', [
            'carrera_id' => $d['carrera1'],
            'formato'    => 'csv',
        ]);

        $this->assertSame(200, $r->getStatusCode());
        $this->assertStringContainsString('text/csv', $r->getHeaderLine('Content-Type'));
        $cuerpo = (string) $r->getBody();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $cuerpo);
        $this->assertStringContainsString(';', $cuerpo);
        $this->assertStringContainsString('Matemática', $cuerpo);
        $this->assertStringContainsString('Aprobado', $cuerpo);
    }

    public function test_exportar_ficha_alumno_pdf(): void
    {
        $d = $this->datosPrueba();

        // Prueba ruta /alumnos/{id}/ficha?formato=pdf
        $r1 = $this->get("/alumnos/{$d['a1']}/ficha", ['formato' => 'pdf']);
        $this->assertSame(200, $r1->getStatusCode());
        $this->assertStringContainsString('application/pdf', $r1->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('%PDF-', (string) $r1->getBody());

        // Prueba ruta /alumnos/{id}?formato=pdf
        $r2 = $this->get("/alumnos/{$d['a1']}", ['formato' => 'pdf']);
        $this->assertSame(200, $r2->getStatusCode());
        $this->assertStringContainsString('application/pdf', $r2->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('%PDF-', (string) $r2->getBody());
    }

    public function test_proteccion_contra_inyeccion_de_formulas_csv(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => 'Informática']);
        $maliciosos = [
            '=HYPERLINK("http://evil.com","click")',
            '+cmd|"/C calc"!A0',
            '-1+2',
            '@SUM(1,2)',
        ];

        foreach ($maliciosos as $i => $payload) {
            $this->insertar('alumnos', [
                'nombres'          => "Inyeccion{$i}",
                'apellidos'        => $payload,
                'fecha_nacimiento' => '2008-01-01',
                'carrera_id'       => $carrera,
            ]);
        }

        $r = $this->get('/reportes/alumnos-por-carrera', ['formato' => 'csv']);
        $this->assertSame(200, $r->getStatusCode());
        $cuerpo = (string) $r->getBody();

        foreach ($maliciosos as $payload) {
            $enCsv = str_replace('"', '""', $payload);
            $this->assertStringContainsString("'" . $enCsv, $cuerpo, "El payload {$payload} debe estar prefijado con apóstrofe en CSV");
        }
    }

    public function test_parametros_formato_invalidos_son_rechazados_sin_error_500(): void
    {
        $d = $this->datosPrueba();

        $rutas = [
            '/reportes/alumnos-por-carrera',
            '/reportes/notas',
            "/alumnos/{$d['a1']}/ficha",
            "/alumnos/{$d['a1']}",
        ];

        $formatosInvalidos = [
            'exe',
            'html',
            ['pdf'], // arreglo
            "pdf\r\nX-Injected: true", // inyección de cabeceras CRLF
            "../../etc/passwd",
        ];

        foreach ($rutas as $ruta) {
            foreach ($formatosInvalidos as $formato) {
                $r = $this->get($ruta, ['formato' => $formato]);
                $this->assertLessThan(500, $r->getStatusCode(), "Error >= 500 en {$ruta} con formato inválido");
                $this->assertFalse($r->hasHeader('X-Injected'), 'No debe haber inyección de cabeceras HTTP');
                $this->assertSame(404, $r->getStatusCode(), "Debe retornar 404 para formato no soportado en {$ruta}");
            }
        }
    }

    public function test_pdf_con_imagenes_maliciosas_o_externas_no_se_cuelga(): void
    {
        $carrera = $this->insertar('carreras', ['nombre' => '<img src="http://127.0.0.1:9/x">']);
        $alumno = $this->crearAlumno('<img src="http://127.0.0.1:9/y">', 'Apellido', $carrera);

        $t = microtime(true);
        $r = $this->get('/reportes/alumnos-por-carrera', ['formato' => 'pdf']);
        $duracion = microtime(true) - $t;

        $this->assertSame(200, $r->getStatusCode());
        $this->assertLessThan(4.0, $duracion, 'La generación de PDF no debe esperar conexiones remotas (SSRF bloqueado)');
        $this->assertStringStartsWith('%PDF-', (string) $r->getBody());
    }
}
