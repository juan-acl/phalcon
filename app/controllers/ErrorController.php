<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;

/** NO extiende BaseController (evita bucles con la verificación CSRF/login). */
class ErrorController extends Controller
{
    public function notFoundAction(): void
    {
        $this->response->setStatusCode(404, 'Not Found');
    }

    public function forbiddenAction(): void
    {
        $this->response->setStatusCode(403, 'Forbidden');
    }

    public function unauthorizedAction(): void
    {
        $this->response->setStatusCode(401, 'Unauthorized');
        $this->response->setHeader('WWW-Authenticate', 'Basic realm="Sistema escolar"');
    }
}
