<?php

namespace App\Controllers;

use App\Models\Clase;
use App\Models\Inscripcion;
use App\Models\Nota;
use App\Models\NotaAuditoria;

class ProfesorController extends BaseController
{
    public function indexAction()
    {
        return $this->irA('profesor/clases');
    }

    public function clasesAction(): void
    {
        $profId = $this->profesorId();
        $clases = [];

        if ($profId || $this->esDirector()) {
            $params = [
                'order' => 'semestre_id DESC, curso_id ASC',
            ];
            if (!$this->esDirector()) {
                $params['conditions'] = 'profesor_id = :pid:';
                $params['bind']       = ['pid' => $profId];
            }
            $clases = Clase::find($params);
        }

        $resumenClases = [];
        foreach ($clases as $cl) {
            $totalInscritos = Inscripcion::count([
                'conditions' => 'clase_id = :cid:',
                'bind'       => ['cid' => $cl->id],
            ]);

            $notas = Nota::find([
                'conditions' => 'curso_id = :c: AND seccion_id = :sec: AND semestre_id = :sem:',
                'bind'       => [
                    'c'   => $cl->curso_id,
                    'sec' => $cl->seccion_id,
                    'sem' => $cl->semestre_id,
                ],
            ]);

            $totalNotas = count($notas);
            $suma = 0;
            $publicadas = 0;
            foreach ($notas as $n) {
                $suma += (float) $n->nota;
                if ($n->estado === 'publicada') {
                    $publicadas++;
                }
            }

            $promedio = $totalNotas > 0 ? round($suma / $totalNotas, 2) : 0;

            $resumenClases[] = [
                'clase'          => $cl,
                'totalInscritos' => $totalInscritos,
                'totalNotas'     => $totalNotas,
                'publicadas'     => $publicadas,
                'promedio'       => $promedio,
            ];
        }

        $this->view->setVar('resumenClases', $resumenClases);
        $this->view->pick('profesor/clases');
    }

    public function claseAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        /** @var Clase|null $clase */
        $clase = Clase::findFirstById($id);

        if (!$clase) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if (!$this->esDirector()) {
            $profId = $this->profesorId();
            /** @var \App\Library\Auth\AccesoService $acceso */
            $acceso = $this->di->get('accesoService');
            if (!$profId || !$acceso->profesorPuedeVerClase($profId, $id)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        $inscripciones = Inscripcion::find([
            'conditions' => 'clase_id = :cid:',
            'bind'       => ['cid' => $id],
            'order'      => 'id ASC',
        ]);

        $filas = [];
        $totalNotas = 0;
        $sumaNotas  = 0;
        $aprobados  = 0;
        $reprobados = 0;
        $borradores = 0;
        $publicadas = 0;
        $umbral = (int) $this->config['app']['notaAprobacion'];

        foreach ($inscripciones as $insc) {
            $alumno = $insc->alumno;
            $nota = Nota::findFirst([
                'conditions' => 'alumno_id = :a: AND curso_id = :c: AND seccion_id = :sec: AND semestre_id = :sem:',
                'bind'       => [
                    'a'   => $insc->alumno_id,
                    'c'   => $clase->curso_id,
                    'sec' => $clase->seccion_id,
                    'sem' => $clase->semestre_id,
                ],
            ]);

            if ($nota) {
                $totalNotas++;
                $val = (float) $nota->nota;
                $sumaNotas += $val;
                if ($val >= $umbral) {
                    $aprobados++;
                } else {
                    $reprobados++;
                }
                if ($nota->estado === 'publicada') {
                    $publicadas++;
                } else {
                    $borradores++;
                }
            }

            $filas[] = [
                'inscripcion' => $insc,
                'alumno'      => $alumno,
                'nota'        => $nota,
            ];
        }

        // Ordenar alfabéticamente por apellidos
        usort($filas, function ($a, $b) {
            $apA = $a['alumno']->apellidos ?? '';
            $apB = $b['alumno']->apellidos ?? '';
            return strcasecmp($apA, $apB);
        });

        $promedio = $totalNotas > 0 ? round($sumaNotas / $totalNotas, 2) : 0;

        $this->view->setVar('clase', $clase);
        $this->view->setVar('filas', $filas);
        $this->view->setVar('totalInscritos', count($inscripciones));
        $this->view->setVar('totalNotas', $totalNotas);
        $this->view->setVar('aprobados', $aprobados);
        $this->view->setVar('reprobados', $reprobados);
        $this->view->setVar('borradores', $borradores);
        $this->view->setVar('publicadas', $publicadas);
        $this->view->setVar('promedio', $promedio);
        $this->view->setVar('umbral', $umbral);
        $this->view->pick('profesor/clase');
    }

    public function notasAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        /** @var Clase|null $clase */
        $clase = Clase::findFirstById($id);

        if (!$clase) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if (!$this->esDirector()) {
            $profId = $this->profesorId();
            /** @var \App\Library\Auth\AccesoService $acceso */
            $acceso = $this->di->get('accesoService');
            if (!$profId || !$acceso->profesorPuedeVerClase($profId, $id)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        if ($clase->semestre->cerrado && !$this->esDirector()) {
            $this->flashSession->error('El semestre se encuentra cerrado. No se admiten modificaciones de calificaciones.');
            return $this->irA('profesor/clases/' . $id);
        }

        $notasPost = $this->request->getPost('notas');
        if (!is_array($notasPost)) {
            $this->flashSession->error('Datos de notas no válidos.');
            return $this->irA('profesor/clases/' . $id);
        }

        $auth = $this->usuarioActual();
        $usuarioId = $auth ? (int) $auth['id'] : null;

        $db = $this->di->get('db');
        $db->begin();
        $guardadas = 0;

        try {
            foreach ($notasPost as $alumnoIdStr => $valorStr) {
                $alumnoId = (int) $alumnoIdStr;
                $valorStr = trim((string) $valorStr);

                if ($valorStr === '') {
                    continue;
                }

                if (!is_numeric($valorStr)) {
                    continue;
                }

                $valor = (float) $valorStr;
                if ($valor < 0 || $valor > 100) {
                    continue;
                }

                // Verificar que el alumno está inscrito en esta clase
                $insc = Inscripcion::findFirst([
                    'conditions' => 'clase_id = :cid: AND alumno_id = :aid:',
                    'bind'       => ['cid' => $id, 'aid' => $alumnoId],
                ]);
                if (!$insc) {
                    continue;
                }

                /** @var Nota|null $nota */
                $nota = Nota::findFirst([
                    'conditions' => 'alumno_id = :a: AND curso_id = :c: AND seccion_id = :sec: AND semestre_id = :sem:',
                    'bind'       => [
                        'a'   => $alumnoId,
                        'c'   => $clase->curso_id,
                        'sec' => $clase->seccion_id,
                        'sem' => $clase->semestre_id,
                    ],
                ]);

                if ($nota) {
                    // Si ya está publicada, no se modifica vía este formulario por lote (debe usar edición individual con motivo)
                    if ($nota->estado === 'publicada') {
                        continue;
                    }

                    $notaAnterior = $nota->nota;
                    $nota->nota = $valor;
                    $nota->updated_by = $usuarioId;
                    if ($nota->save()) {
                        $guardadas++;
                        $aud = new NotaAuditoria();
                        $aud->assign([
                            'nota_id'       => (int) $nota->id,
                            'alumno_id'     => $alumnoId,
                            'curso_id'      => $clase->curso_id,
                            'semestre_id'   => $clase->semestre_id,
                            'seccion_id'    => $clase->seccion_id,
                            'accion'        => 'editar',
                            'nota_anterior' => (float) $notaAnterior,
                            'nota_nueva'    => $valor,
                            'motivo'        => 'Actualización de borrador por docente',
                            'usuario_id'    => $usuarioId,
                        ], ['nota_id', 'alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'accion', 'nota_anterior', 'nota_nueva', 'motivo', 'usuario_id']);
                        $aud->save();
                    }
                } else {
                    // Nueva nota borrador
                    $nota = new Nota();
                    $nota->assign([
                        'alumno_id'   => $alumnoId,
                        'curso_id'    => $clase->curso_id,
                        'semestre_id' => $clase->semestre_id,
                        'seccion_id'  => $clase->seccion_id,
                        'nota'        => $valor,
                        'estado'      => 'borrador',
                        'updated_by'  => $usuarioId,
                    ], ['alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'nota', 'estado', 'updated_by']);

                    if ($nota->save()) {
                        $guardadas++;
                        $aud = new NotaAuditoria();
                        $aud->assign([
                            'nota_id'       => (int) $nota->id,
                            'alumno_id'     => $alumnoId,
                            'curso_id'      => $clase->curso_id,
                            'semestre_id'   => $clase->semestre_id,
                            'seccion_id'    => $clase->seccion_id,
                            'accion'        => 'crear',
                            'nota_anterior' => null,
                            'nota_nueva'    => $valor,
                            'motivo'        => 'Ingreso de borrador por docente',
                            'usuario_id'    => $usuarioId,
                        ], ['nota_id', 'alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'accion', 'nota_anterior', 'nota_nueva', 'motivo', 'usuario_id']);
                        $aud->save();
                    }
                }
            }

            $db->commit();
            $this->flashSession->success("Se guardaron {$guardadas} notas como borrador.");
        } catch (\Exception $e) {
            $db->rollback();
            $this->flashSession->error('Error al guardar notas: ' . $e->getMessage());
        }

        return $this->irA('profesor/clases/' . $id);
    }

    public function publicarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        /** @var Clase|null $clase */
        $clase = Clase::findFirstById($id);

        if (!$clase) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if (!$this->esDirector()) {
            $profId = $this->profesorId();
            /** @var \App\Library\Auth\AccesoService $acceso */
            $acceso = $this->di->get('accesoService');
            if (!$profId || !$acceso->profesorPuedeVerClase($profId, $id)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        if ($clase->semestre->cerrado && !$this->esDirector()) {
            $this->flashSession->error('El semestre se encuentra cerrado. No se permite publicar notas.');
            return $this->irA('profesor/clases/' . $id);
        }

        $auth = $this->usuarioActual();
        $usuarioId = $auth ? (int) $auth['id'] : null;

        $borradores = Nota::find([
            'conditions' => 'curso_id = :c: AND seccion_id = :sec: AND semestre_id = :sem: AND estado = :est:',
            'bind'       => [
                'c'   => $clase->curso_id,
                'sec' => $clase->seccion_id,
                'sem' => $clase->semestre_id,
                'est' => 'borrador',
            ],
        ]);

        if (count($borradores) === 0) {
            $this->flashSession->notice('No hay calificaciones en estado borrador pendientes de publicar.');
            return $this->irA('profesor/clases/' . $id);
        }

        $db = $this->di->get('db');
        $db->begin();
        $publicadas = 0;

        try {
            $ahora = date('Y-m-d H:i:s');
            foreach ($borradores as $nota) {
                $nota->estado = 'publicada';
                $nota->publicada_at = $ahora;
                $nota->updated_by = $usuarioId;

                if ($nota->save()) {
                    $publicadas++;
                    $aud = new NotaAuditoria();
                    $aud->assign([
                        'nota_id'       => (int) $nota->id,
                        'alumno_id'     => (int) $nota->alumno_id,
                        'curso_id'      => $clase->curso_id,
                        'semestre_id'   => $clase->semestre_id,
                        'seccion_id'    => $clase->seccion_id,
                        'accion'        => 'publicar',
                        'nota_anterior' => (float) $nota->nota,
                        'nota_nueva'    => (float) $nota->nota,
                        'motivo'        => 'Publicación de calificaciones de la clase',
                        'usuario_id'    => $usuarioId,
                    ], ['nota_id', 'alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'accion', 'nota_anterior', 'nota_nueva', 'motivo', 'usuario_id']);
                    $aud->save();
                }
            }

            $db->commit();
            $this->flashSession->success("Se publicaron exitosamente {$publicadas} calificaciones. Ahora son visibles para los alumnos.");
        } catch (\Exception $e) {
            $db->rollback();
            $this->flashSession->error('Error al publicar calificaciones: ' . $e->getMessage());
        }

        return $this->irA('profesor/clases/' . $id);
    }
}
