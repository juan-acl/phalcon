<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Curso;
use App\Models\Nota;
use App\Models\NotaAuditoria;
use App\Models\Profesor;

class TableroController extends BaseController
{
    public function indexAction(): void
    {
        $umbral = (int) $this->config['app']['notaAprobacion'];

        $totalAlumnos    = (int) Alumno::count();
        $totalProfesores = (int) Profesor::count();
        $totalClases     = (int) Clase::count();
        $totalCursos     = (int) Curso::count();
        $totalNotas      = (int) Nota::count();

        $promedioGeneral = '0.00';
        $porcentajeAprobacion = 0;

        if ($totalNotas > 0) {
            $avg = $this->db->fetchColumn('SELECT AVG(nota) FROM notas');
            $promedioGeneral = number_format((float) $avg, 2);

            $aprobados = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM notas WHERE nota >= :aprob', ['aprob' => $umbral]);
            $porcentajeAprobacion = round(($aprobados / $totalNotas) * 100, 1);
        }

        // Últimas 10 acciones registradas en la bitácora de auditoría
        $ultimasAuditorias = NotaAuditoria::find([
            'order' => 'created_at DESC',
            'limit' => 10,
        ]);

        $this->view->setVar('totalAlumnos', $totalAlumnos);
        $this->view->setVar('totalProfesores', $totalProfesores);
        $this->view->setVar('totalClases', $totalClases);
        $this->view->setVar('totalCursos', $totalCursos);
        $this->view->setVar('totalNotas', $totalNotas);
        $this->view->setVar('promedioGeneral', $promedioGeneral);
        $this->view->setVar('porcentajeAprobacion', $porcentajeAprobacion);
        $this->view->setVar('ultimasAuditorias', $ultimasAuditorias);
        $this->view->pick('tablero/index');
    }
}
