<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

/**
 * Todos los controladores (menos ErrorController) extienden de este.
 * Aquí se aplican, para TODAS las rutas: login básico (opcional) y verificación CSRF en POST.
 */
abstract class BaseController extends Controller
{
    public function beforeExecuteRoute(Dispatcher $dispatcher): bool
    {
        $auth = $this->config['auth'];
        if ($auth['enabled']) {
            $cred = $this->request->getBasicAuth();
            $ok = $cred
                && hash_equals($auth['user'], (string) ($cred['username'] ?? ''))
                && $auth['passHash'] !== ''
                && password_verify((string) ($cred['password'] ?? ''), $auth['passHash']);
            if (!$ok) {
                $dispatcher->forward(['controller' => 'error', 'action' => 'unauthorized']);
                return false;
            }
        }

        if ($this->request->isPost() && !$this->security->checkToken()) {
            $dispatcher->forward(['controller' => 'error', 'action' => 'forbidden']);
            return false;
        }

        return true;
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
