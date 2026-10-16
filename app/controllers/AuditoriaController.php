<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\NotaAuditoria;
use App\Models\Semestre;
use App\Models\Usuario;

class AuditoriaController extends BaseController
{
    public function indexAction()
    {
        $formatoRaw = $this->request->getQuery('formato');
        if ($formatoRaw !== null) {
            if (!is_string($formatoRaw) || !in_array(strtolower($formatoRaw), ['pdf', 'csv'], true)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        $accion       = trim((string) $this->request->getQuery('accion', 'string', ''));
        $alumnoId     = (int) $this->request->getQuery('alumno_id', 'int', 0);
        $usuarioId    = (int) $this->request->getQuery('usuario_id', 'int', 0);
        $semestreId   = (int) $this->request->getQuery('semestre_id', 'int', 0);
        $fechaDesde   = trim((string) $this->request->getQuery('fecha_desde', 'string', ''));
        $fechaHasta   = trim((string) $this->request->getQuery('fecha_hasta', 'string', ''));
        $cambioGrande = (int) $this->request->getQuery('cambio_grande', 'int', 0);

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

        if ($usuarioId > 0) {
            $conds[] = 'usuario_id = :uid:';
            $binds['uid'] = $usuarioId;
        }

        if ($semestreId > 0) {
            $conds[] = 'semestre_id = :semid:';
            $binds['semid'] = $semestreId;
        }

        if ($fechaDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
            $conds[] = 'created_at >= :fdesde:';
            $binds['fdesde'] = $fechaDesde . ' 00:00:00';
        }

        if ($fechaHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
            $conds[] = 'created_at <= :fhasta:';
            $binds['fhasta'] = $fechaHasta . ' 23:59:59';
        }

        $params = [
            'order' => 'created_at DESC, id DESC',
        ];

        if (!empty($conds)) {
            $params['conditions'] = implode(' AND ', $conds);
            $params['bind'] = $binds;
        }

        // Obtener todos los registros coincidentes para cálculo de resumen o exportación
        $todos = NotaAuditoria::find($params);

        // Procesar registros enriquecidos
        $procesados = [];
        $resumen = [
            'total'            => 0,
            'crear'            => 0,
            'editar'           => 0,
            'eliminar'         => 0,
            'cambios_grandes'  => 0,
            'semestre_cerrado' => 0,
        ];

        foreach ($todos as $aud) {
            $notaAnt = $aud->nota_anterior !== null ? (float) $aud->nota_anterior : null;
            $notaNva = $aud->nota_nueva !== null ? (float) $aud->nota_nueva : null;
            $dif = ($notaAnt !== null && $notaNva !== null) ? round($notaNva - $notaAnt, 2) : null;
            $esGrande = ($dif !== null && abs($dif) >= 20.0);
            $esCerrado = $aud->semestre && (int) $aud->semestre->cerrado === 1;

            // Filtro opcional en memoria si se solicitó solo cambios grandes
            if ($cambioGrande === 1 && !$esGrande) {
                continue;
            }

            $resumen['total']++;
            if ($aud->accion === 'crear') {
                $resumen['crear']++;
            } elseif ($aud->accion === 'editar') {
                $resumen['editar']++;
            } elseif ($aud->accion === 'eliminar') {
                $resumen['eliminar']++;
            }

            if ($esGrande) {
                $resumen['cambios_grandes']++;
            }
            if ($esCerrado) {
                $resumen['semestre_cerrado']++;
            }

            $difStr = '-';
            if ($dif !== null) {
                $difStr = $dif > 0 ? ('+' . number_format($dif, 2)) : number_format($dif, 2);
            }

            $procesados[] = [
                'id'                  => $aud->id,
                'created_at'          => $aud->created_at,
                'usuario_nombre'      => $aud->usuario ? $aud->usuario->usuario : 'Sistema',
                'usuario_rol'         => $aud->usuario ? ucfirst($aud->usuario->rol) : 'Automático',
                'alumno_nombre'       => $aud->alumno ? ($aud->alumno->apellidos . ', ' . $aud->alumno->nombres) : ('Alumno #' . $aud->alumno_id),
                'curso_nombre'        => $aud->curso ? $aud->curso->nombre : ('Curso #' . $aud->curso_id),
                'seccion_nombre'      => $aud->seccion ? $aud->seccion->nombre : (string) $aud->seccion_id,
                'semestre_nombre'     => $aud->semestre ? $aud->semestre->nombre : (string) $aud->semestre_id,
                'es_semestre_cerrado' => $esCerrado,
                'semestre_estado'     => $esCerrado ? 'Cerrado' : 'Abierto',
                'accion'              => $aud->accion,
                'nota_anterior'       => $notaAnt !== null ? number_format($notaAnt, 2) : '-',
                'nota_nueva'          => $notaNva !== null ? number_format($notaNva, 2) : '-',
                'diferencia'          => $dif,
                'diferencia_str'      => $difStr,
                'es_cambio_grande'    => $esGrande,
                'cambio_mayor_texto'  => $esGrande ? 'SÍ (>= 20 pts)' : 'No',
                'motivo'              => $aud->motivo ?: 'Sin motivo registrado',
            ];
        }

        // Parámetros de consulta limpios para enlaces y descargas
        $queryParamsArray = array_filter([
            'accion'        => $accion !== '' ? $accion : null,
            'alumno_id'     => $alumnoId > 0 ? $alumnoId : null,
            'usuario_id'    => $usuarioId > 0 ? $usuarioId : null,
            'semestre_id'   => $semestreId > 0 ? $semestreId : null,
            'fecha_desde'   => $fechaDesde !== '' ? $fechaDesde : null,
            'fecha_hasta'   => $fechaHasta !== '' ? $fechaHasta : null,
            'cambio_grande' => $cambioGrande === 1 ? 1 : null,
        ], fn($v) => $v !== null);

        $queryString = http_build_query($queryParamsArray);

        // Exportación solicitada
        if ($formatoRaw !== null) {
            $formato = strtolower($formatoRaw);
            $nombreArchivo = 'reporte-auditoria-notas-' . date('Y-m-d');

            if ($formato === 'csv') {
                $columnas = [
                    'created_at'         => 'Fecha y Hora',
                    'usuario_nombre'     => 'Usuario Operador',
                    'usuario_rol'        => 'Rol',
                    'alumno_nombre'      => 'Estudiante',
                    'curso_nombre'       => 'Asignatura',
                    'seccion_nombre'     => 'Sección',
                    'semestre_nombre'    => 'Semestre',
                    'semestre_estado'    => 'Estado Semestre',
                    'accion'             => 'Acción Realizada',
                    'nota_anterior'      => 'Nota Anterior',
                    'nota_nueva'         => 'Nota Nueva',
                    'diferencia_str'     => 'Diferencia',
                    'cambio_mayor_texto' => 'Cambio Mayor (>= 20 pts)',
                    'motivo'             => 'Motivo / Justificación',
                ];

                $filasExport = array_slice($procesados, 0, 1000);
                return $this->exportService->csv($filasExport, $columnas, $nombreArchivo);
            }

            if ($formato === 'pdf') {
                $filtroAlumno = $alumnoId > 0 ? Alumno::findFirst($alumnoId) : null;
                $filtroUsuario = $usuarioId > 0 ? Usuario::findFirst($usuarioId) : null;
                $filtroSemestre = $semestreId > 0 ? Semestre::findFirst($semestreId) : null;

                $fechasTexto = 'Histórico completo';
                if ($fechaDesde !== '' && $fechaHasta !== '') {
                    $fechasTexto = "Del {$fechaDesde} al {$fechaHasta}";
                } elseif ($fechaDesde !== '') {
                    $fechasTexto = "Desde {$fechaDesde}";
                } elseif ($fechaHasta !== '') {
                    $fechasTexto = "Hasta {$fechaHasta}";
                }

                $filtrosAplicados = [
                    'accion'   => $accion !== '' ? ucfirst($accion) : 'Todas las acciones',
                    'alumno'   => $filtroAlumno ? ($filtroAlumno->apellidos . ', ' . $filtroAlumno->nombres) : 'Todos los alumnos',
                    'usuario'  => $filtroUsuario ? ($filtroUsuario->usuario . ' (' . ucfirst($filtroUsuario->rol) . ')') : 'Todos los operadores',
                    'semestre' => $filtroSemestre ? $filtroSemestre->nombre : 'Todos los semestres',
                    'fechas'   => $fechasTexto,
                ];

                $filasPdf = array_slice($procesados, 0, 500);

                $html = $this->simpleView->render('reportes/pdf/auditoria', [
                    'auditorias'       => $filasPdf,
                    'resumen'          => $resumen,
                    'filtrosAplicados' => $filtrosAplicados,
                    'fechaHora'        => date('d/m/Y H:i'),
                    'usuario'          => $this->session->get('auth_usuario') ?: 'Dirección General',
                ]);

                return $this->exportService->pdf($html, $nombreArchivo, 'landscape');
            }
        }

        // Paginación en pantalla (15 elementos por página)
        $pagina = max(1, (int) $this->request->getQuery('pagina', 'int', 1));
        $limite = 15;
        $totalItems = count($procesados);
        $totalPaginas = max(1, (int) ceil($totalItems / $limite));
        $paginaActual = min($pagina, $totalPaginas);
        $offset = ($paginaActual - 1) * $limite;
        $itemsPagina = array_slice($procesados, $offset, $limite);

        $page = (object) [
            'current'     => $paginaActual,
            'last'        => $totalPaginas,
            'previous'    => max(1, $paginaActual - 1),
            'next'        => min($totalPaginas, $paginaActual + 1),
            'limit'       => $limite,
            'total_items' => $totalItems,
        ];

        $this->view->setVar('page', $page);
        $this->view->setVar('auditorias', $itemsPagina);
        $this->view->setVar('resumen', $resumen);
        $this->view->setVar('alumnos', Alumno::find(['order' => 'apellidos ASC, nombres ASC']));
        $this->view->setVar('usuarios', Usuario::find(['order' => 'usuario ASC']));
        $this->view->setVar('semestres', Semestre::find(['order' => 'id DESC']));
        $this->view->setVar('queryString', $queryString);
        $this->view->setVar('filtros', [
            'accion'        => $accion,
            'alumno_id'     => $alumnoId,
            'usuario_id'    => $usuarioId,
            'semestre_id'   => $semestreId,
            'fecha_desde'   => $fechaDesde,
            'fecha_hasta'   => $fechaHasta,
            'cambio_grande' => $cambioGrande,
        ]);

        $this->view->pick('auditoria/index');
    }
}
