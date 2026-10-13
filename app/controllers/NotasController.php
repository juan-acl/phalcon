<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Nota;
use App\Models\NotaAuditoria;
use App\Models\Seccion;
use App\Models\Semestre;

/**
 * Controlador para la búsqueda de alumnos, registro, edición y auditoría de notas.
 */
class NotasController extends BaseController
{
    public function buscarAction(): void
    {
        $q = trim((string) $this->request->getQuery('q', null, ''));
        $this->view->setVar('q', $q);

        if ($q !== '') {
            $alumnos = Alumno::find([
                'conditions' => 'nombres LIKE :q: OR apellidos LIKE :q:',
                'bind'       => ['q' => '%' . $q . '%'],
                'order'      => 'apellidos ASC, nombres ASC',
                'limit'      => 50,
            ]);
        } else {
            $alumnos = Alumno::find([
                'order' => 'apellidos ASC, nombres ASC',
                'limit' => 20,
            ]);
        }

        $this->view->setVar('alumnos', $alumnos);
        $this->view->pick('notas/buscar');
    }

    public function nuevaAction(): void
    {
        $alumnoId = (int) $this->dispatcher->getParam('id');
        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $alumnoId],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $this->view->setVar('alumno', $alumno);
        $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
        $this->view->setVar('semestres', Semestre::find(['order' => 'nombre ASC']));
        $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
        $this->view->setVar('old', []);
        $this->view->setVar('errores', []);
        $this->view->pick('notas/nueva');
    }

    public function crearAction()
    {
        $alumnoId = (int) $this->dispatcher->getParam('id');
        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $alumnoId],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $cursoId = (int) $this->request->getPost('curso_id', null, 0);
        $semestreId = (int) $this->request->getPost('semestre_id', null, 0);
        $seccionId = (int) $this->request->getPost('seccion_id', null, 0);
        $notaRaw = $this->request->getPost('nota', null, '');
        $notaStr = is_string($notaRaw) ? trim($notaRaw) : (string) $notaRaw;
        $estado = $this->request->getPost('estado', 'string', 'publicada');
        if (!in_array($estado, ['borrador', 'publicada'], true)) {
            $estado = 'publicada';
        }

        $datos = [
            'curso_id'    => $cursoId,
            'semestre_id' => $semestreId,
            'seccion_id'  => $seccionId,
            'nota'        => $notaStr,
            'estado'      => $estado,
        ];

        $errores = [];

        if ($notaStr === '' || !is_numeric($notaStr) || (float) $notaStr < 0 || (float) $notaStr > 100) {
            $errores[] = 'La nota debe ser un número entre 0 y 100';
        }

        $curso = Curso::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $cursoId],
        ]);
        if (!$curso) {
            $errores[] = 'Selecciona un curso válido';
        }

        $semestre = Semestre::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $semestreId],
        ]);
        if (!$semestre) {
            $errores[] = 'Selecciona un semestre válido';
        } elseif ($semestre->cerrado && !$this->esDirector()) {
            $errores[] = 'El semestre está cerrado y no admite nuevas calificaciones';
        }

        $seccion = Seccion::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $seccionId],
        ]);
        if (!$seccion) {
            $errores[] = 'Selecciona una sección válida';
        }

        if (empty($errores)) {
            $existe = Nota::findFirst([
                'conditions' => 'alumno_id = :a: AND curso_id = :c: AND semestre_id = :s:',
                'bind'       => ['a' => $alumnoId, 'c' => $cursoId, 's' => $semestreId],
            ]);
            if ($existe) {
                $errores[] = 'El alumno ya tiene nota en ese curso y semestre';
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->view->setVar('alumno', $alumno);
            $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
            $this->view->setVar('semestres', Semestre::find(['order' => 'nombre ASC']));
            $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
            $this->view->pick('notas/nueva');
            return;
        }

        $auth = $this->usuarioActual();
        $usuarioId = $auth ? (int) $auth['id'] : null;

        $db = $this->di->get('db');
        $db->begin();
        try {
            $nota = new Nota();
            $nota->assign([
                'alumno_id'    => $alumnoId,
                'curso_id'     => $cursoId,
                'semestre_id'  => $semestreId,
                'seccion_id'   => $seccionId,
                'nota'         => (float) $notaStr,
                'estado'       => $estado,
                'publicada_at' => $estado === 'publicada' ? date('Y-m-d H:i:s') : null,
                'updated_by'   => $usuarioId,
            ], ['alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'nota', 'estado', 'publicada_at', 'updated_by']);

            if (!$nota->save()) {
                $db->rollback();
                $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($nota->getMessages()));
                $this->conErrores($mensajes, $datos);
                $this->view->setVar('alumno', $alumno);
                $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
                $this->view->setVar('semestres', Semestre::find(['order' => 'nombre ASC']));
                $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
                $this->view->pick('notas/nueva');
                return;
            }

            $auditoria = new NotaAuditoria();
            $auditoria->assign([
                'nota_id'       => (int) $nota->id,
                'alumno_id'     => $alumnoId,
                'curso_id'      => $cursoId,
                'semestre_id'   => $semestreId,
                'seccion_id'    => $seccionId,
                'accion'        => 'crear',
                'nota_anterior' => null,
                'nota_nueva'    => (float) $notaStr,
                'motivo'        => 'Ingreso inicial de calificación',
                'usuario_id'    => $usuarioId,
            ], ['nota_id', 'alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'accion', 'nota_anterior', 'nota_nueva', 'motivo', 'usuario_id']);
            $auditoria->save();

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }

        $this->flashSession->success('Nota registrada correctamente.');
        return $this->irA('alumnos/' . $alumnoId);
    }

    public function editarAction(): void
    {
        $id = (int) $this->dispatcher->getParam('id');
        /** @var Nota|null $nota */
        $nota = Nota::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$nota) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if ($this->esProfesor()) {
            $profId = $this->profesorId();
            /** @var \App\Library\Auth\AccesoService $acceso */
            $acceso = $this->di->get('accesoService');
            if (!$profId || !$acceso->profesorPuedeEditarNota($profId, $id)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        // Si el semestre está cerrado y no es director: error de acceso
        if ($nota->semestre->cerrado && !$this->esDirector()) {
            $this->flashSession->error('El semestre se encuentra cerrado. Solo la Dirección puede modificar notas.');
            $this->irA('alumnos/' . $nota->alumno_id);
            return;
        }

        $auditorias = NotaAuditoria::find([
            'conditions' => '[nota_id] = :id:',
            'bind'       => ['id' => $id],
            'order'      => 'created_at DESC',
        ]);

        $this->view->setVar('nota', $nota);
        $this->view->setVar('alumno', $nota->alumno);
        $this->view->setVar('curso', $nota->curso);
        $this->view->setVar('semestre', $nota->semestre);
        $this->view->setVar('seccion', $nota->seccion);
        $this->view->setVar('auditorias', $auditorias);
        $this->view->setVar('old', [
            'nota'   => $nota->nota,
            'estado' => $nota->estado,
        ]);
        $this->view->setVar('errores', []);
        $this->view->pick('notas/editar');
    }

    public function actualizarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        /** @var Nota|null $nota */
        $nota = Nota::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$nota) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if ($this->esProfesor()) {
            $profId = $this->profesorId();
            /** @var \App\Library\Auth\AccesoService $acceso */
            $acceso = $this->di->get('accesoService');
            if (!$profId || !$acceso->profesorPuedeEditarNota($profId, $id)) {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        if ($nota->semestre->cerrado && !$this->esDirector()) {
            $this->conErrores(['El semestre está cerrado y no admite modificaciones de docentes.']);
            $this->prepararVistaEdicion($nota);
            return;
        }

        $notaRaw        = $this->request->getPost('nota', null, '');
        $notaStr        = is_string($notaRaw) ? trim($notaRaw) : (string) $notaRaw;
        $motivo         = trim((string) $this->request->getPost('motivo', 'string', ''));
        $estado         = (string) $this->request->getPost('estado', 'string', 'publicada');
        $tokenOptimista = (string) $this->request->getPost('updated_at_token', 'string', '');

        if (!in_array($estado, ['borrador', 'publicada'], true)) {
            $estado = 'publicada';
        }

        $errores = [];

        // Validación de bloqueo optimista
        $tokenActual = $nota->updated_at ? (string) $nota->updated_at : '';
        if ($tokenActual !== '' && $tokenOptimista !== '' && $tokenOptimista !== $tokenActual) {
            $errores[] = 'La nota fue modificada por otro usuario mientras editaba. Por favor recargue para verificar.';
        }

        // Validación de nota
        if ($notaStr === '' || !is_numeric($notaStr) || (float) $notaStr < 0 || (float) $notaStr > 100) {
            $errores[] = 'La nota debe ser un número entre 0 y 100';
        }

        // Motivo obligatorio de al menos 10 caracteres
        if (mb_strlen($motivo) < 10) {
            $errores[] = 'El motivo de modificación es obligatorio y debe tener al menos 10 caracteres';
        }

        $datos = [
            'nota'   => $notaStr,
            'estado' => $estado,
            'motivo' => $motivo,
        ];

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->prepararVistaEdicion($nota);
            return;
        }

        $auth = $this->usuarioActual();
        $usuarioId = $auth ? (int) $auth['id'] : null;
        $notaAnterior = $nota->nota;

        $db = $this->di->get('db');
        $db->begin();
        try {
            $nota->nota = (float) $notaStr;
            $nota->estado = $estado;
            if ($estado === 'publicada' && empty($nota->publicada_at)) {
                $nota->publicada_at = date('Y-m-d H:i:s');
            }
            $nota->updated_by = $usuarioId;

            if (!$nota->save()) {
                $db->rollback();
                $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($nota->getMessages()));
                $this->conErrores($mensajes, $datos);
                $this->prepararVistaEdicion($nota);
                return;
            }

            $auditoria = new NotaAuditoria();
            $auditoria->assign([
                'nota_id'       => (int) $nota->id,
                'alumno_id'     => (int) $nota->alumno_id,
                'curso_id'      => (int) $nota->curso_id,
                'semestre_id'   => (int) $nota->semestre_id,
                'seccion_id'    => (int) $nota->seccion_id,
                'accion'        => 'editar',
                'nota_anterior' => (float) $notaAnterior,
                'nota_nueva'    => (float) $notaStr,
                'motivo'        => $motivo,
                'usuario_id'    => $usuarioId,
            ], ['nota_id', 'alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'accion', 'nota_anterior', 'nota_nueva', 'motivo', 'usuario_id']);
            $auditoria->save();

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }

        $this->flashSession->success('Nota actualizada y registrada en la auditoría correctamente.');
        return $this->irA('alumnos/' . $nota->alumno_id);
    }

    public function eliminarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        /** @var Nota|null $nota */
        $nota = Nota::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$nota) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if ($nota->semestre->cerrado && !$this->esDirector()) {
            $this->flashSession->error('El semestre está cerrado y no admite eliminación de notas.');
            return $this->irA('alumnos/' . $nota->alumno_id);
        }

        $alumnoId = (int) $nota->alumno_id;
        $auth = $this->usuarioActual();
        $usuarioId = $auth ? (int) $auth['id'] : null;

        $db = $this->di->get('db');
        $db->begin();
        try {
            $auditoria = new NotaAuditoria();
            $auditoria->assign([
                'nota_id'       => (int) $nota->id,
                'alumno_id'     => $alumnoId,
                'curso_id'      => (int) $nota->curso_id,
                'semestre_id'   => (int) $nota->semestre_id,
                'seccion_id'    => (int) $nota->seccion_id,
                'accion'        => 'eliminar',
                'nota_anterior' => (float) $nota->nota,
                'nota_nueva'    => null,
                'motivo'        => 'Calificación eliminada del sistema',
                'usuario_id'    => $usuarioId,
            ], ['nota_id', 'alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'accion', 'nota_anterior', 'nota_nueva', 'motivo', 'usuario_id']);
            $auditoria->save();

            if (!$nota->delete()) {
                $db->rollback();
                $this->flashSession->error('No se pudo eliminar la nota.');
                return $this->irA('alumnos/' . $alumnoId);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }

        $this->flashSession->success('Nota eliminada y registrada en auditoría correctamente.');
        return $this->irA('alumnos/' . $alumnoId);
    }

    private function prepararVistaEdicion(Nota $nota): void
    {
        $auditorias = NotaAuditoria::find([
            'conditions' => '[nota_id] = :id:',
            'bind'       => ['id' => $nota->id],
            'order'      => 'created_at DESC',
        ]);
        $this->view->setVar('nota', $nota);
        $this->view->setVar('alumno', $nota->alumno);
        $this->view->setVar('curso', $nota->curso);
        $this->view->setVar('semestre', $nota->semestre);
        $this->view->setVar('seccion', $nota->seccion);
        $this->view->setVar('auditorias', $auditorias);
        $this->view->pick('notas/editar');
    }
}
