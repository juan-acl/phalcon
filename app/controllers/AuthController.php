<?php

namespace App\Controllers;

use App\Models\Usuario;

class AuthController extends BaseController
{
    public function loginAction()
    {
        if (!$this->request->isPost()) {
            return;
        }

        $usuarioTxt = trim((string) $this->request->getPost('usuario', 'string', ''));
        $password   = (string) $this->request->getPost('password', 'string', '');

        if ($usuarioTxt === '' || $password === '') {
            $errores = [];
            if ($usuarioTxt === '') {
                $errores[] = 'Por favor ingrese su nombre de usuario';
            }
            if ($password === '') {
                $errores[] = 'Por favor ingrese su contraseña';
            }
            $this->conErrores($errores, ['usuario' => $usuarioTxt]);
            return;
        }

        /** @var Usuario|null $usuario */
        $usuario = Usuario::findFirst([
            'conditions' => 'usuario = :u:',
            'bind'       => ['u' => $usuarioTxt],
        ]);

        if (!$usuario) {
            // Protección contra timing attacks: ejecución equivalente de hash
            password_verify('dummy_pass', '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUU1234567890123');
            $this->conErrores(['Usuario o contraseña incorrectos.'], ['usuario' => $usuarioTxt]);
            return;
        }

        if ($usuario->estaBloqueado()) {
            $this->conErrores(
                ['La cuenta se encuentra bloqueada temporalmente por demasiados intentos fallidos. Intente más tarde.'],
                ['usuario' => $usuarioTxt]
            );
            return;
        }

        if (!$usuario->activo) {
            $this->conErrores(['Usuario o contraseña incorrectos.'], ['usuario' => $usuarioTxt]);
            return;
        }

        if (!$usuario->verificarPassword($password)) {
            $usuario->intentos_fallidos = (int) $usuario->intentos_fallidos + 1;
            if ($usuario->intentos_fallidos >= 5) {
                $usuario->bloqueado_hasta = date('Y-m-d H:i:s', time() + 15 * 60);
            }
            $usuario->save();

            $this->conErrores(['Usuario o contraseña incorrectos.'], ['usuario' => $usuarioTxt]);
            return;
        }

        // Credenciales válidas: reiniciar contador de fallos
        $usuario->intentos_fallidos = 0;
        $usuario->bloqueado_hasta   = null;
        $usuario->ultimo_acceso     = date('Y-m-d H:i:s');
        $usuario->save();

        // Limpiar sesión anterior si existía para cambio limpio de usuario
        $this->session->remove('auth');
        // Evitar fijación de sesión: regenerar ID
        $this->session->regenerateId(true);

        $authData = [
            'id'                 => (int) $usuario->id,
            'usuario'            => (string) $usuario->usuario,
            'rol'                => (string) $usuario->rol,
            'alumno_id'          => $usuario->alumno_id ? (int) $usuario->alumno_id : null,
            'profesor_id'        => $usuario->profesor_id ? (int) $usuario->profesor_id : null,
            'debe_cambiar_clave' => (bool) $usuario->debe_cambiar_clave,
        ];
        $this->session->set('auth', $authData);

        if ($usuario->debe_cambiar_clave) {
            $this->flashSession->notice('Por seguridad institucional, debe actualizar su contraseña inicial.');
            return $this->irA('cuenta/clave');
        }

        return $this->redirigirPorRol($usuario->rol, $authData);
    }

    public function logoutAction()
    {
        $this->session->remove('auth');
        $this->session->destroy();
        return $this->irA('login');
    }

    private function redirigirPorRol(string $rol, array $auth)
    {
        if ($rol === 'alumno') {
            return $this->irA('mi/notas');
        }
        if ($rol === 'profesor') {
            return $this->irA('profesor/clases');
        }
        return $this->irA('alumnos');
    }
}
