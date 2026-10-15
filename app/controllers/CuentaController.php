<?php

namespace App\Controllers;

use App\Models\Usuario;

class CuentaController extends BaseController
{
    public function claveAction()
    {
        $auth = $this->session->get('auth');
        if (!$auth) {
            return $this->irA('login');
        }

        if (!$this->request->isPost()) {
            return;
        }

        $actual       = (string) $this->request->getPost('password_actual', 'string', '');
        $nueva        = (string) $this->request->getPost('password_nueva', 'string', '');
        $confirmacion = (string) $this->request->getPost('password_confirmacion', 'string', '');

        $errores = [];
        if ($actual === '') {
            $errores[] = 'Debe ingresar su contraseña actual';
        }
        if ($nueva === '') {
            $errores[] = 'Debe ingresar la nueva contraseña';
        } elseif (strlen($nueva) < 8) {
            $errores[] = 'La nueva contraseña debe contener al menos 8 caracteres.';
        }
        if ($confirmacion === '') {
            $errores[] = 'Debe confirmar la nueva contraseña';
        } elseif ($nueva !== '' && $nueva !== $confirmacion) {
            $errores[] = 'La confirmación de la contraseña no coincide.';
        }

        /** @var Usuario|null $usuario */
        $usuario = Usuario::findFirstById((int) $auth['id']);
        if (!$usuario) {
            return $this->irA('login');
        }

        if (empty($errores) && !$usuario->verificarPassword($actual)) {
            $errores[] = 'La contraseña actual es incorrecta.';
        }

        if (empty($errores) && $actual === $nueva) {
            $errores[] = 'La nueva contraseña debe ser diferente a la contraseña actual.';
        }

        if (!empty($errores)) {
            $this->conErrores($errores);
            return;
        }

        $usuario->setPassword($nueva);
        $usuario->debe_cambiar_clave = 0;
        $usuario->save();

        $auth['debe_cambiar_clave'] = false;
        $this->session->set('auth', $auth);

        $this->flashSession->success('Contraseña actualizada correctamente.');

        if ($auth['rol'] === 'alumno' && !empty($auth['alumno_id'])) {
            return $this->irA('alumnos/' . $auth['alumno_id'] . '/ficha');
        }
        if ($auth['rol'] === 'profesor') {
            return $this->irA('reportes/notas');
        }
        return $this->irA('alumnos');
    }
}
