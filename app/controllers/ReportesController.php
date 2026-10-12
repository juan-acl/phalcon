<?php

namespace App\Controllers;

use App\Library\Reportes\ReporteService;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Seccion;

/**
 * Controlador para la generación de reportes e informes académicos:
 * - General de alumnos por carrera.
 * - Notas de alumnos por carrera, curso y sección.
 */
class ReportesController extends BaseController
{
    public function alumnosPorCarreraAction(): void
    {
        $carreraIdRaw = $this->request->getQuery('carrera_id', null, '');
        $carreraId = is_numeric($carreraIdRaw) && (int) $carreraIdRaw > 0 ? (int) $carreraIdRaw : null;

        $service = new ReporteService($this->db, (int) $this->config['app']['notaAprobacion']);
        $alumnos = $service->alumnosPorCarrera($carreraId);

        $this->view->setVar('alumnos', $alumnos);
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
        $this->view->setVar('carreraId', $carreraId);
        $this->view->pick('reportes/alumnos-por-carrera');
    }

    public function notasAction(): void
    {
        $carreraIdRaw = $this->request->getQuery('carrera_id', null, '');
        $carreraId = is_numeric($carreraIdRaw) && (int) $carreraIdRaw > 0 ? (int) $carreraIdRaw : null;

        $cursoIdRaw = $this->request->getQuery('curso_id', null, '');
        $cursoId = is_numeric($cursoIdRaw) && (int) $cursoIdRaw > 0 ? (int) $cursoIdRaw : null;

        $seccionIdRaw = $this->request->getQuery('seccion_id', null, '');
        $seccionId = is_numeric($seccionIdRaw) && (int) $seccionIdRaw > 0 ? (int) $seccionIdRaw : null;

        $service = new ReporteService($this->db, (int) $this->config['app']['notaAprobacion']);
        $filas = $service->notas($carreraId, $cursoId, $seccionId);

        $this->view->setVar('filas', $filas);
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
        $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
        $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
        $this->view->setVar('carreraId', $carreraId);
        $this->view->setVar('cursoId', $cursoId);
        $this->view->setVar('seccionId', $seccionId);
        $this->view->setVar('umbral', (int) $this->config['app']['notaAprobacion']);
        $this->view->pick('reportes/notas');
    }
}
