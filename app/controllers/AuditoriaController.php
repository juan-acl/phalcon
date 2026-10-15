<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\NotaAuditoria;

class AuditoriaController extends BaseController
{
    public function indexAction(): void
    {
        $accion     = trim((string) $this->request->getQuery('accion', 'string', ''));
        $alumnoId   = (int) $this->request->getQuery('alumno_id', 'int', 0);
        $fechaDesde = trim((string) $this->request->getQuery('fecha_desde', 'string', ''));
        $fechaHasta = trim((string) $this->request->getQuery('fecha_hasta', 'string', ''));

        $conds = [];
        $binds = [];

        if ($accion !== '') {
            $conds[] = 'accion = :accion:';
            $binds['accion'] = $accion;
        }

        if ($alumnoId > 0) {
            $conds[] = 'alumno_id = :aid:';
            $binds['aid'] = $alumnoId;
        }

        if ($fechaDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
            $conds[] = 'created_at >= :fdesde:';
            $binds['fdesde'] = $fechaDesde . ' 00:00:00';
        }

        if ($fechaHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
            $conds[] = 'created_at <= :fhasta:';
            $binds['fhasta'] = $fechaHasta . ' 23:59:59';
        }

        $pagina = max(1, (int) $this->request->getQuery('pagina', 'int', 1));
        $limite = 15;

        $params = [
            'order' => 'created_at DESC, id DESC',
        ];

        if (!empty($conds)) {
            $params['conditions'] = implode(' AND ', $conds);
            $params['bind'] = $binds;
        }

        $paginator = new \Phalcon\Paginator\Adapter\Model([
            'model'      => NotaAuditoria::class,
            'parameters' => $params,
            'limit'      => $limite,
            'page'       => $pagina,
        ]);
        $page = $paginator->paginate();

        $alumnos = Alumno::find(['order' => 'apellidos ASC, nombres ASC']);

        $this->view->setVar('page', $page);
        $this->view->setVar('auditorias', $page->getItems());
        $this->view->setVar('alumnos', $alumnos);
        $this->view->setVar('filtros', [
            'accion'      => $accion,
            'alumno_id'   => $alumnoId,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ]);
        $this->view->pick('auditoria/index');
    }
}
