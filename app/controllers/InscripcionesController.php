<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Inscripcion;
use App\Models\Semestre;

class InscripcionesController extends BaseController
{
    public function indexAction(): void
    {
        $claseIdRaw   = $this->request->getQuery('clase_id', null, '');
        $semestreIdRaw = $this->request->getQuery('semestre_id', null, '');
        $q            = trim((string) $this->request->getQuery('q', null, ''));

        $claseId    = is_numeric($claseIdRaw)    && (int) $claseIdRaw    > 0 ? (int) $claseIdRaw    : 0;
        $semestreId = is_numeric($semestreIdRaw) && (int) $semestreIdRaw > 0 ? (int) $semestreIdRaw : 0;

        $conds = [];
        $binds = [];

        if ($claseId > 0) {
            $conds[] = 'clase_id = :cid:';
            $binds['cid'] = $claseId;
        }

        $params = ['order' => 'clase_id DESC, id DESC', 'limit' => 500];
        if (!empty($conds)) {
            $params['conditions'] = implode(' AND ', $conds);
            $params['bind'] = $binds;
        }

        $inscripciones = Inscripcion::find($params);

        // Filtrar por alumno (búsqueda en memoria sobre los resultados ya obtenidos)
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $filtradas = [];
            foreach ($inscripciones as $insc) {
                if ($insc->alumno) {
                    $nombre = mb_strtolower($insc->alumno->getNombreCompleto());
                    if (str_contains($nombre, $qLower)) {
                        $filtradas[] = $insc;
                    }
                }
            }
            $inscripciones = $filtradas;
        }

        // Clases filtradas por semestre para el dropdown
        $claseParams = ['order' => 'semestre_id DESC, curso_id ASC'];
        if ($semestreId > 0) {
            $claseParams['conditions'] = 'semestre_id = :sid:';
            $claseParams['bind'] = ['sid' => $semestreId];
        }

        $clases    = Clase::find($claseParams);
        $semestres = Semestre::find(['order' => 'id DESC']);

        $this->view->setVar('inscripciones', $inscripciones);
        $this->view->setVar('clases', $clases);
        $this->view->setVar('semestres', $semestres);
        $this->view->setVar('claseSeleccionada', $claseId);
        $this->view->setVar('semestreSeleccionado', $semestreId);
        $this->view->setVar('q', $q);
        $this->view->pick('inscripciones/index');
    }

    public function nuevaAction(): void
    {
        $claseIdDefecto = (int) $this->request->getQuery('clase_id', 'int', 0);
        $this->view->setVar('alumnos', Alumno::find(['order' => 'apellidos ASC, nombres ASC']));
        $this->view->setVar('clases', Clase::find(['order' => 'semestre_id DESC, curso_id ASC']));
        $this->view->setVar('claseIdDefecto', $claseIdDefecto);
        $this->view->setVar('old', ['clase_id' => $claseIdDefecto]);
        $this->view->setVar('errores', []);
        $this->view->pick('inscripciones/nueva');
    }

    public function crearAction()
    {
        $alumnoId = (int) $this->request->getPost('alumno_id', 'int', 0);
        $claseId  = (int) $this->request->getPost('clase_id', 'int', 0);

        $datos = [
            'alumno_id' => $alumnoId,
            'clase_id'  => $claseId,
        ];

        $errores = [];
        if (!Alumno::findFirstById($alumnoId)) { $errores[] = 'Seleccione un alumno válido'; }
        if (!Clase::findFirstById($claseId))   { $errores[] = 'Seleccione una clase válida'; }

        if (empty($errores)) {
            $existe = Inscripcion::findFirst([
                'conditions' => 'alumno_id = :a: AND clase_id = :c:',
                'bind'       => ['a' => $alumnoId, 'c' => $claseId],
            ]);
            if ($existe) {
                $errores[] = 'El alumno ya se encuentra inscrito en esta clase';
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->view->setVar('alumnos', Alumno::find(['order' => 'apellidos ASC, nombres ASC']));
            $this->view->setVar('clases', Clase::find(['order' => 'semestre_id DESC, curso_id ASC']));
            $this->view->setVar('claseIdDefecto', $claseId);
            $this->view->pick('inscripciones/nueva');
            return;
        }

        $insc = new Inscripcion();
        $insc->assign([
            'alumno_id' => $alumnoId,
            'clase_id'  => $claseId,
        ], ['alumno_id', 'clase_id']);

        try {
            if (!$insc->save()) {
                $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($insc->getMessages()));
                if (empty($mensajes)) {
                    $mensajes = ['No se pudo registrar la inscripción.'];
                }
                $this->conErrores($mensajes, $datos);
                $this->view->setVar('alumnos', Alumno::find(['order' => 'apellidos ASC, nombres ASC']));
                $this->view->setVar('clases', Clase::find(['order' => 'semestre_id DESC, curso_id ASC']));
                $this->view->setVar('claseIdDefecto', $claseId);
                $this->view->pick('inscripciones/nueva');
                return;
            }
        } catch (\Throwable $e) {
            error_log('Error al crear inscripción: ' . $e->getMessage());
            $this->conErrores(['El alumno ya se encuentra inscrito en esta clase o los datos son inválidos.'], $datos);
            $this->view->setVar('alumnos', Alumno::find(['order' => 'apellidos ASC, nombres ASC']));
            $this->view->setVar('clases', Clase::find(['order' => 'semestre_id DESC, curso_id ASC']));
            $this->view->setVar('claseIdDefecto', $claseId);
            $this->view->pick('inscripciones/nueva');
            return;
        }

        $this->flashSession->success('Alumno inscrito en la clase exitosamente.');
        return $this->irA('inscripciones?clase_id=' . $claseId);
    }

    public function eliminarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $insc = Inscripcion::findFirstById($id);
        if (!$insc) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $claseId = (int) $insc->clase_id;
        try {
            if (!$insc->delete()) {
                $this->flashSession->error('No se pudo anular la inscripción. Verifique si el estudiante tiene notas registradas en esta clase.');
            } else {
                $this->flashSession->success('Inscripción anulada correctamente.');
            }
        } catch (\Throwable $e) {
            error_log('Error al anular inscripción: ' . $e->getMessage());
            $this->flashSession->error('No se puede anular la inscripción porque el estudiante ya cuenta con calificaciones registradas en esta clase.');
        }

        return $this->irA('inscripciones?clase_id=' . $claseId);
    }
}
