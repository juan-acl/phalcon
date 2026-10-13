<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Library\Reportes\ReporteService;

class AlumnoController extends BaseController
{
    public function indexAction()
    {
        return $this->irA('mi/notas');
    }

    public function perfilAction()
    {
        $id = $this->alumnoId();
        if (!$id) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $alumno = Alumno::findFirstById($id);
        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $inscripciones = Inscripcion::find([
            'conditions' => 'alumno_id = :aid:',
            'bind'       => ['aid' => $id],
            'order'      => 'clase_id DESC',
        ]);

        $this->view->setVar('alumno', $alumno);
        $this->view->setVar('inscripciones', $inscripciones);
        $this->view->pick('alumno/perfil');
    }

    public function notasAction()
    {
        $id = $this->alumnoId();
        if (!$id) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $alumno = Alumno::findFirstById($id);
        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $umbral = (int) $this->config['app']['notaAprobacion'];
        $service = new ReporteService($this->db, $umbral);
        $historial = $service->historial($id, true); // true = solo notas publicadas

        $totalNotas = count($historial);
        $sumaNotas  = 0;
        $aprobados  = 0;
        $reprobados = 0;

        foreach ($historial as $row) {
            $val = (float) $row['nota'];
            $sumaNotas += $val;
            if ($val >= $umbral) {
                $aprobados++;
            } else {
                $reprobados++;
            }
        }

        $promedio = $totalNotas > 0 ? round($sumaNotas / $totalNotas, 2) : 0;

        $this->view->setVar('alumno', $alumno);
        $this->view->setVar('historial', $historial);
        $this->view->setVar('totalNotas', $totalNotas);
        $this->view->setVar('aprobados', $aprobados);
        $this->view->setVar('reprobados', $reprobados);
        $this->view->setVar('promedio', $promedio);
        $this->view->setVar('umbral', $umbral);
        $this->view->pick('alumno/notas');
    }

    public function mifichaAction()
    {
        return $this->perfilAction();
    }

    public function misnotasAction()
    {
        return $this->notasAction();
    }

    public function descargarfichaAction()
    {
        $id = $this->alumnoId();
        if (!$id) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }
        return $this->irA("alumnos/{$id}/ficha?formato=pdf");
    }
}
