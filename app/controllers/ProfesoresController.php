<?php

namespace App\Controllers;

use App\Models\Profesor;
use App\Models\Usuario;

class ProfesoresController extends BaseController
{
    public function indexAction(): void
    {
        $profesores = Profesor::find(['order' => 'apellidos ASC, nombres ASC']);
        $this->view->setVar('profesores', $profesores);
        $this->view->pick('profesores/index');
    }

    public function nuevoAction(): void
    {
        $this->view->setVar('old', []);
        $this->view->setVar('errores', []);
        $this->view->pick('profesores/nuevo');
    }

    public function crearAction()
    {
        $nombres   = trim((string) $this->request->getPost('nombres', 'string', ''));
        $apellidos = trim((string) $this->request->getPost('apellidos', 'string', ''));
        $email     = trim((string) $this->request->getPost('email', 'string', ''));
        $usuarioNom= trim((string) $this->request->getPost('usuario', 'string', ''));
        $password  = (string) $this->request->getPost('password', 'string', '');

        $datos = [
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'email'     => $email,
            'usuario'   => $usuarioNom,
        ];

        $errores = [];
        if ($nombres === '')   { $errores[] = 'Los nombres son obligatorios'; }
        if ($apellidos === '') { $errores[] = 'Los apellidos son obligatorios'; }
        if ($usuarioNom === '') { $errores[] = 'El nombre de usuario es obligatorio'; }
        if (strlen($password) < 8) { $errores[] = 'La contraseña debe contener al menos 8 caracteres'; }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico es inválido';
        }

        if (empty($errores)) {
            $existeU = Usuario::findFirst([
                'conditions' => 'usuario = :u:',
                'bind'       => ['u' => $usuarioNom],
            ]);
            if ($existeU) {
                $errores[] = 'El nombre de usuario ya se encuentra registrado';
            }
        }

        if ($email !== '' && empty($errores)) {
            $existeE = Profesor::findFirst([
                'conditions' => 'email = :e:',
                'bind'       => ['e' => $email],
            ]);
            if ($existeE) {
                $errores[] = 'El correo electrónico ya está registrado con otro docente';
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->view->pick('profesores/nuevo');
            return;
        }

        $db = $this->di->get('db');
        $db->begin();
        try {
            $profesor = new Profesor();
            $profesor->assign([
                'nombres'   => $nombres,
                'apellidos' => $apellidos,
                'email'     => $email !== '' ? $email : null,
            ], ['nombres', 'apellidos', 'email']);

            if (!$profesor->save()) {
                $db->rollback();
                $this->conErrores(array_map(fn($m) => $m->getMessage(), iterator_to_array($profesor->getMessages())), $datos);
                $this->view->pick('profesores/nuevo');
                return;
            }

            $usuario = new Usuario();
            $usuario->assign([
                'usuario'            => $usuarioNom,
                'password_hash'      => password_hash($password, PASSWORD_DEFAULT),
                'rol'                => 'profesor',
                'profesor_id'        => (int) $profesor->id,
                'activo'             => 1,
                'debe_cambiar_clave' => 0,
            ], ['usuario', 'password_hash', 'rol', 'profesor_id', 'activo', 'debe_cambiar_clave']);

            if (!$usuario->save()) {
                $db->rollback();
                $this->conErrores(array_map(fn($m) => $m->getMessage(), iterator_to_array($usuario->getMessages())), $datos);
                $this->view->pick('profesores/nuevo');
                return;
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }

        $this->flashSession->success('Docente y usuario de acceso creados correctamente.');
        return $this->irA('profesores');
    }

    public function editarAction(): void
    {
        $id = (int) $this->dispatcher->getParam('id');
        $profesor = Profesor::findFirstById($id);
        if (!$profesor) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $this->view->setVar('profesor', $profesor);
        $this->view->setVar('old', [
            'nombres'   => $profesor->nombres,
            'apellidos' => $profesor->apellidos,
            'email'     => $profesor->email,
        ]);
        $this->view->setVar('errores', []);
        $this->view->pick('profesores/editar');
    }

    public function actualizarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $profesor = Profesor::findFirstById($id);
        if (!$profesor) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $nombres   = trim((string) $this->request->getPost('nombres', 'string', ''));
        $apellidos = trim((string) $this->request->getPost('apellidos', 'string', ''));
        $email     = trim((string) $this->request->getPost('email', 'string', ''));

        $datos = [
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'email'     => $email,
        ];

        $errores = [];
        if ($nombres === '')   { $errores[] = 'Los nombres son obligatorios'; }
        if ($apellidos === '') { $errores[] = 'Los apellidos son obligatorios'; }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico es inválido';
        }

        if ($email !== '' && empty($errores)) {
            $existe = Profesor::findFirst([
                'conditions' => 'email = :e: AND id != :id:',
                'bind'       => ['e' => $email, 'id' => $id],
            ]);
            if ($existe) {
                $errores[] = 'El correo electrónico ya está registrado con otro docente';
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->view->setVar('profesor', $profesor);
            $this->view->pick('profesores/editar');
            return;
        }

        $profesor->assign([
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'email'     => $email !== '' ? $email : null,
        ], ['nombres', 'apellidos', 'email']);

        if (!$profesor->save()) {
            $this->conErrores(array_map(fn($m) => $m->getMessage(), iterator_to_array($profesor->getMessages())), $datos);
            $this->view->setVar('profesor', $profesor);
            $this->view->pick('profesores/editar');
            return;
        }

        $this->flashSession->success('Información del docente actualizada correctamente.');
        return $this->irA('profesores');
    }

    public function eliminarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $profesor = Profesor::findFirstById($id);
        if (!$profesor) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        if (!$profesor->delete()) {
            $this->flashSession->error('No se pudo eliminar el docente.');
        } else {
            $this->flashSession->success('Docente y usuario asociado eliminados correctamente.');
        }

        return $this->irA('profesores');
    }
}
