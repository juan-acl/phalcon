<?php

namespace App\Controllers;

use App\Models\Clase;
use App\Models\Curso;
use App\Models\Profesor;
use App\Models\Seccion;
use App\Models\Semestre;

class ClasesController extends BaseController
{
    public function indexAction(): void
    {
        $clases = Clase::find(['order' => 'semestre_id DESC, curso_id ASC, seccion_id ASC']);
        $this->view->setVar('clases', $clases);
        $this->view->pick('clases/index');
    }

    public function nuevaAction(): void
    {
        $this->prepararCatalogos();
        $this->view->setVar('old', []);
        $this->view->setVar('errores', []);
        $this->view->pick('clases/nueva');
    }

    public function crearAction()
    {
        $cursoId    = (int) $this->request->getPost('curso_id', 'int', 0);
        $seccionId  = (int) $this->request->getPost('seccion_id', 'int', 0);
        $semestreId = (int) $this->request->getPost('semestre_id', 'int', 0);
        $profesorId = $this->request->getPost('profesor_id', 'int', null);
        if ($profesorId !== null && (int) $profesorId === 0) {
            $profesorId = null;
        }

        $datos = [
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'profesor_id' => $profesorId,
        ];

        $errores = [];
        if (!Curso::findFirstById($cursoId))       { $errores[] = 'Seleccione un curso válido'; }
        if (!Seccion::findFirstById($seccionId))   { $errores[] = 'Seleccione una sección válida'; }
        if (!Semestre::findFirstById($semestreId)) { $errores[] = 'Seleccione un semestre válido'; }

        if ($profesorId !== null && !Profesor::findFirstById((int) $profesorId)) {
            $errores[] = 'El docente seleccionado no existe';
        }

        if (empty($errores)) {
            $existe = Clase::findFirst([
                'conditions' => 'curso_id = :c: AND seccion_id = :sec: AND semestre_id = :sem:',
                'bind'       => ['c' => $cursoId, 'sec' => $seccionId, 'sem' => $semestreId],
            ]);
            if ($existe) {
                $errores[] = 'Ya existe una clase registrada para esa combinación de curso, sección y semestre';
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->prepararCatalogos();
            $this->view->pick('clases/nueva');
            return;
        }

        $clase = new Clase();
        $clase->assign([
            'curso_id'    => $cursoId,
            'seccion_id'  => $seccionId,
            'semestre_id' => $semestreId,
            'profesor_id' => $profesorId ? (int) $profesorId : null,
        ], ['curso_id', 'seccion_id', 'semestre_id', 'profesor_id']);

        if (!$clase->save()) {
            $this->conErrores(array_map(fn($m) => $m->getMessage(), iterator_to_array($clase->getMessages())), $datos);
            $this->prepararCatalogos();
            $this->view->pick('clases/nueva');
            return;
        }

        $this->flashSession->success('Clase académica creada exitosamente.');
        return $this->irA('clases');
    }

    public function editarAction(): void
    {
        $id = (int) $this->dispatcher->getParam('id');
        $clase = Clase::findFirstById($id);
        if (!$clase) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $this->prepararCatalogos();
        $this->view->setVar('clase', $clase);
        $this->view->setVar('old', [
            'profesor_id' => $clase->profesor_id,
        ]);
        $this->view->setVar('errores', []);
        $this->view->pick('clases/editar');
    }

    public function actualizarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $clase = Clase::findFirstById($id);
        if (!$clase) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $profesorId = $this->request->getPost('profesor_id', 'int', null);
        if ($profesorId !== null && (int) $profesorId === 0) {
            $profesorId = null;
        }

        if ($profesorId !== null && !Profesor::findFirstById((int) $profesorId)) {
            $this->conErrores(['El docente seleccionado no existe']);
            $this->prepararCatalogos();
            $this->view->setVar('clase', $clase);
            $this->view->pick('clases/editar');
            return;
        }

        $clase->profesor_id = $profesorId ? (int) $profesorId : null;
        if (!$clase->save()) {
            $this->conErrores(array_map(fn($m) => $m->getMessage(), iterator_to_array($clase->getMessages())));
            $this->prepararCatalogos();
            $this->view->setVar('clase', $clase);
            $this->view->pick('clases/editar');
            return;
        }

        $this->flashSession->success('Docente asignado a la clase actualizado correctamente.');
        return $this->irA('clases');
    }

    public function eliminarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $clase = Clase::findFirstById($id);
        if (!$clase) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if (!$clase->delete()) {
            $this->flashSession->error('No se pudo eliminar la clase.');
        } else {
            $this->flashSession->success('Clase eliminada correctamente.');
        }

        return $this->irA('clases');
    }

    private function prepararCatalogos(): void
    {
        $this->view->setVar('cursos', Curso::find(['order' => 'nombre ASC']));
        $this->view->setVar('secciones', Seccion::find(['order' => 'nombre ASC']));
        $this->view->setVar('semestres', Semestre::find(['order' => 'nombre ASC']));
        $this->view->setVar('profesores', Profesor::find(['order' => 'apellidos ASC, nombres ASC']));
    }
}
