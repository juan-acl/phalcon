<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Nota;
use App\Models\Seccion;
use App\Models\Semestre;

/**
 * Controlador para la búsqueda de alumnos y registro de notas.
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

        $datos = [
            'curso_id'    => $cursoId,
            'semestre_id' => $semestreId,
            'seccion_id'  => $seccionId,
            'nota'        => $notaStr,
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

        $nota = new Nota();
        $nota->assign([
            'alumno_id'   => $alumnoId,
            'curso_id'    => $cursoId,
            'semestre_id' => $semestreId,
            'seccion_id'  => $seccionId,
            'nota'        => (float) $notaStr,
        ], ['alumno_id', 'curso_id', 'semestre_id', 'seccion_id', 'nota']); // lista blanca

        if (!$nota->save()) {
            $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($nota->getMessages()));
            $this->conErrores($mensajes, $datos);
            $this->view->setVar('alumno', $alumno);
            $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
            $this->view->setVar('semestres', Semestre::find(['order' => 'nombre ASC']));
            $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
            $this->view->pick('notas/nueva');
            return;
        }

        $this->flashSession->success('Nota registrada correctamente.');
        return $this->irA('alumnos/' . $alumnoId);
    }

    public function eliminarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $nota = Nota::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$nota) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $alumnoId = (int) $nota->alumno_id;

        if (!$nota->delete()) {
            $this->flashSession->error('No se pudo eliminar la nota.');
        } else {
            $this->flashSession->success('Nota eliminada correctamente.');
        }

        return $this->irA('alumnos/' . $alumnoId);
    }
}
