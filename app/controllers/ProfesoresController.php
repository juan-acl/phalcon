<?php

namespace App\Controllers;

use App\Models\Clase;
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
                $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($profesor->getMessages()));
                if (empty($mensajes)) {
                    $mensajes = ['No se pudo registrar la información del docente.'];
                }
                $this->conErrores($mensajes, $datos);
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
                $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($usuario->getMessages()));
                if (empty($mensajes)) {
                    $mensajes = ['No se pudo crear la cuenta de usuario para el docente.'];
                }
                $this->conErrores($mensajes, $datos);
                $this->view->pick('profesores/nuevo');
                return;
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            error_log('Error al crear docente: ' . $e->getMessage());
            $this->conErrores(['No fue posible registrar al docente debido a un conflicto con el usuario o correo ingresados.'], $datos);
            $this->view->pick('profesores/nuevo');
            return;
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
            $errores[] = 'El formato del correo electrónico es inválido (ejemplo: docente@escuela.edu)';
        }

        if ($email !== '' && empty($errores)) {
            $existeE = Profesor::findFirst([
                'conditions' => 'email = :e: AND id != :id:',
                'bind'       => ['e' => $email, 'id' => $id],
            ]);
            if ($existeE) {
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

        try {
            if (!$profesor->save()) {
                $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($profesor->getMessages()));
                if (empty($mensajes)) {
                    $mensajes = ['No se pudo actualizar la información del docente.'];
                }
                $this->conErrores($mensajes, $datos);
                $this->view->setVar('profesor', $profesor);
                $this->view->pick('profesores/editar');
                return;
            }
        } catch (\Throwable $e) {
            error_log('Error al actualizar docente: ' . $e->getMessage());
            $this->conErrores(['No se pudo actualizar el docente. Es posible que el correo electrónico ya esté asignado a otro docente.'], $datos);
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

        $cantClases = Clase::count(['conditions' => 'profesor_id = :id:', 'bind' => ['id' => $id]]);
        if ($cantClases > 0) {
            $this->flashSession->error("No se puede eliminar al docente porque tiene {$cantClases} clase(s) asignada(s). Debe reasignar o retirar sus clases antes de darlo de baja.");
            return $this->irA('profesores');
        }

        try {
            if (!$profesor->delete()) {
                $this->flashSession->error('No se pudo eliminar el docente. Verifique si tiene dependencias en otros módulos.');
            } else {
                $this->flashSession->success('Docente y usuario asociado eliminados correctamente.');
            }
        } catch (\Throwable $e) {
            error_log('Error al eliminar profesor: ' . $e->getMessage());
            $this->flashSession->error('No se puede eliminar al docente porque cuenta con actividades académicas o calificaciones asociadas.');
        }

        return $this->irA('profesores');
    }
}
