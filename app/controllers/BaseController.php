<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

/**
 * Todos los controladores (menos ErrorController) extienden de este.
 * Aplica verificación CSRF en POST, autenticación por sesión y control de acceso (ACL).
 */
abstract class BaseController extends Controller
{
    public function beforeExecuteRoute(Dispatcher $dispatcher): bool
    {
        // 1. Verificación CSRF en todas las peticiones POST
        if ($this->request->isPost() && !$this->security->checkToken()) {
            $dispatcher->forward(['controller' => 'error', 'action' => 'forbidden']);
            return false;
        }

        // 2. Obtener usuario en sesión y rol
        $auth = $this->session->get('auth');
        $rol  = $auth ? ($auth['rol'] ?? 'invitado') : 'invitado';
        $this->view->setVar('auth', $auth);

        $controller = strtolower($dispatcher->getControllerName());
        $action     = strtolower($dispatcher->getActionName());

        // 3. Verificación de permisos por ACL
        /** @var \App\Library\Auth\AclService $aclService */
        $aclService = $this->di->get('aclService');
        if (!$aclService->isAllowed($rol, $controller, $action)) {
            if ($rol === 'invitado') {
                $this->response->redirect('login');
                return false;
            }
            $dispatcher->forward(['controller' => 'error', 'action' => 'forbidden']);
            return false;
        }

        // 4. Forzar cambio de clave si está marcado debe_cambiar_clave
        if ($auth && !empty($auth['debe_cambiar_clave'])) {
            $enClave  = ($controller === 'cuenta' && $action === 'clave');
            $enLogout = ($controller === 'auth' && $action === 'logout');
            if (!$enClave && !$enLogout) {
                $this->response->redirect('cuenta/clave');
                return false;
            }
        }

        return true;
    }

    /** Devuelve el arreglo de sesión del usuario autenticado o null si es invitado. */
    protected function usuarioActual(): ?array
    {
        return $this->session->get('auth');
    }

    protected function esDirector(): bool
    {
        $auth = $this->usuarioActual();
        return ($auth['rol'] ?? '') === 'director';
    }

    protected function esProfesor(): bool
    {
        $auth = $this->usuarioActual();
        return ($auth['rol'] ?? '') === 'profesor';
    }

    protected function esAlumno(): bool
    {
        $auth = $this->usuarioActual();
        return ($auth['rol'] ?? '') === 'alumno';
    }

    protected function alumnoId(): ?int
    {
        $auth = $this->usuarioActual();
        return isset($auth['alumno_id']) ? (int) $auth['alumno_id'] : null;
    }

    protected function profesorId(): ?int
    {
        $auth = $this->usuarioActual();
        return isset($auth['profesor_id']) ? (int) $auth['profesor_id'] : null;
    }

    /** Redirige a una ruta interna (ej. 'alumnos'). */
    protected function irA(string $ruta)
    {
        return $this->response->redirect($ruta);
    }

    /** Vuelve a mostrar un formulario con errores de validación (HTTP 422). */
    protected function conErrores(array $mensajes, array $datos = []): void
    {
        $this->response->setStatusCode(422, 'Unprocessable Entity');
        $this->view->setVar('errores', $mensajes);
        $this->view->setVar('old', $datos);
    }
}
