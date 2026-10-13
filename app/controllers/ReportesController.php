<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Library\Reportes\ReporteService;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Seccion;

/**
 * Controlador para la generación de reportes e informes académicos:
 * - General de alumnos por carrera (HTML, PDF, CSV).
 * - Notas de alumnos por carrera, curso y sección (HTML, PDF, CSV).
 */
class ReportesController extends BaseController
{
    public function alumnosPorCarreraAction()
    {
        $formatoRaw = $this->request->getQuery('formato');
        if ($formatoRaw !== null) {
            if (!is_string($formatoRaw) || !in_array(strtolower($formatoRaw), ['pdf', 'csv'], true)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        $carreraIdRaw = $this->request->getQuery('carrera_id', null, '');
        $carreraId = is_numeric($carreraIdRaw) && (int) $carreraIdRaw > 0 ? (int) $carreraIdRaw : null;

        $service = new ReporteService($this->db, (int) $this->config['app']['notaAprobacion']);
        $alumnos = $service->alumnosPorCarrera($carreraId);

        // Exportación si se solicita formato
        if ($formatoRaw !== null) {
            $formato = strtolower($formatoRaw);
            $carreraObj = $carreraId ? Carrera::findFirst($carreraId) : null;
            $carreraNombre = $carreraObj ? $carreraObj->nombre : 'Todas las carreras';
            $nombreArchivo = 'reporte-alumnos-por-carrera-' . date('Y-m-d');

            if ($formato === 'csv') {
                $columnas = [
                    'id'               => 'No. Registro',
                    'carrera'          => 'Carrera',
                    'apellidos'        => 'Apellidos',
                    'nombres'          => 'Nombres',
                    'fecha_nacimiento' => 'Fecha de Nacimiento',
                ];
                return $this->exportService->csv($alumnos, $columnas, $nombreArchivo);
            }

            if ($formato === 'pdf') {
                $html = $this->simpleView->render('reportes/pdf/alumnos-por-carrera', [
                    'alumnos'       => $alumnos,
                    'carreraNombre' => $carreraNombre,
                    'fechaHora'     => date('d/m/Y H:i'),
                    'usuario'       => 'Administración',
                ]);
                return $this->exportService->pdf($html, $nombreArchivo, 'portrait');
            }
        }

        $this->view->setVar('alumnos', $alumnos);
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
        $this->view->setVar('carreraId', $carreraId);
        $this->view->pick('reportes/alumnos-por-carrera');
    }

    public function notasAction()
    {
        $formatoRaw = $this->request->getQuery('formato');
        if ($formatoRaw !== null) {
            if (!is_string($formatoRaw) || !in_array(strtolower($formatoRaw), ['pdf', 'csv'], true)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        $carreraIdRaw = $this->request->getQuery('carrera_id', null, '');
        $carreraId = is_numeric($carreraIdRaw) && (int) $carreraIdRaw > 0 ? (int) $carreraIdRaw : null;

        $cursoIdRaw = $this->request->getQuery('curso_id', null, '');
        $cursoId = is_numeric($cursoIdRaw) && (int) $cursoIdRaw > 0 ? (int) $cursoIdRaw : null;

        $seccionIdRaw = $this->request->getQuery('seccion_id', null, '');
        $seccionId = is_numeric($seccionIdRaw) && (int) $seccionIdRaw > 0 ? (int) $seccionIdRaw : null;

        $umbral = (int) $this->config['app']['notaAprobacion'];
        $service = new ReporteService($this->db, $umbral);
        $filas = $service->notas($carreraId, $cursoId, $seccionId);

        // Exportación si se solicita formato
        if ($formatoRaw !== null) {
            $formato = strtolower($formatoRaw);
            $nombreArchivo = 'reporte-notas-' . date('Y-m-d');

            if ($formato === 'csv') {
                $columnas = [
                    'carrera'   => 'Carrera',
                    'curso'     => 'Curso',
                    'seccion'   => 'Sección',
                    'semestre'  => 'Semestre',
                    'apellidos' => 'Apellidos',
                    'nombres'   => 'Nombres',
                    'nota'      => 'Calificación',
                    'estado'    => 'Resultado',
                ];
                $filasCsv = array_map(function ($f) {
                    $f['estado'] = !empty($f['aprobado']) ? 'Aprobado' : 'Reprobado';
                    return $f;
                }, $filas);

                return $this->exportService->csv($filasCsv, $columnas, $nombreArchivo);
            }

            if ($formato === 'pdf') {
                $carreraObj = $carreraId ? Carrera::findFirst($carreraId) : null;
                $cursoObj   = $cursoId ? Curso::findFirst($cursoId) : null;
                $seccionObj = $seccionId ? Seccion::findFirst($seccionId) : null;

                $total = count($filas);
                $totalAprobados = count(array_filter($filas, fn($f) => !empty($f['aprobado'])));
                $totalReprobados = $total - $totalAprobados;
                $sumaNotas = array_sum(array_column($filas, 'nota'));
                $promedio = $total > 0 ? number_format($sumaNotas / $total, 2) : '0.00';
                $porcentajeAprobados = $total > 0 ? number_format(($totalAprobados / $total) * 100, 1) : '0';
                $porcentajeReprobados = $total > 0 ? number_format(($totalReprobados / $total) * 100, 1) : '0';

                $html = $this->simpleView->render('reportes/pdf/notas', [
                    'filas'                => $filas,
                    'filtroCarrera'        => $carreraObj ? $carreraObj->nombre : 'Todas',
                    'filtroCurso'          => $cursoObj ? $cursoObj->nombre : 'Todos',
                    'filtroSeccion'        => $seccionObj ? $seccionObj->nombre : 'Todas',
                    'umbral'               => $umbral,
                    'totalAprobados'       => $totalAprobados,
                    'totalReprobados'      => $totalReprobados,
                    'promedio'             => $promedio,
                    'porcentajeAprobados'  => $porcentajeAprobados,
                    'porcentajeReprobados' => $porcentajeReprobados,
                    'fechaHora'            => date('d/m/Y H:i'),
                    'usuario'              => 'Administración',
                ]);

                return $this->exportService->pdf($html, $nombreArchivo, 'landscape');
            }
        }

        $this->view->setVar('filas', $filas);
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
        $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
        $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
        $this->view->setVar('carreraId', $carreraId);
        $this->view->setVar('cursoId', $cursoId);
        $this->view->setVar('seccionId', $seccionId);
        $this->view->setVar('umbral', $umbral);
        $this->view->pick('reportes/notas');
    }
}
