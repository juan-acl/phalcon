<?php

namespace App\Library\Auth;

use Phalcon\Acl\Adapter\Memory as AclMemory;
use Phalcon\Acl\Enum;

class AclService
{
    private AclMemory $acl;

    public function __construct()
    {
        $this->acl = new AclMemory();
        $this->acl->setDefaultAction(Enum::DENY);

        $roles = ['director', 'profesor', 'alumno', 'invitado'];
        foreach ($roles as $rol) {
            $this->acl->addRole($rol);
        }

        // Componentes y acciones (en minúsculas)
        $recursos = [
            'index'     => ['index'],
            'auth'      => ['login', 'logout'],
            'cuenta'    => ['clave'],
            'alumnos'   => ['index', 'nuevo', 'crear', 'ver', 'ficha', 'editar', 'actualizar', 'eliminar'],
            'notas'     => ['buscar', 'nueva', 'crear', 'editar', 'actualizar', 'eliminar'],
            'catalogos' => ['index', 'nuevo', 'crear', 'editar', 'actualizar', 'eliminar', 'cerrar', 'reabrir'],
            'reportes'      => ['alumnosporcarrera', 'notas'],
            'error'         => ['notfound', 'servererror', 'unauthorized', 'forbidden'],
            'profesor'      => ['index', 'clases', 'clase', 'notas', 'guardarnota', 'publicar'],
            'alumno'        => ['index', 'mificha', 'misnotas', 'descargarficha', 'perfil', 'notas'],
            'tablero'       => ['index'],
            'profesores'    => ['index', 'nuevo', 'crear', 'editar', 'actualizar', 'eliminar'],
            'clases'        => ['index', 'nueva', 'crear', 'editar', 'actualizar', 'eliminar'],
            'inscripciones' => ['index', 'nueva', 'crear', 'eliminar'],
            'auditoria'     => ['index'],
        ];

        foreach ($recursos as $componente => $acciones) {
            $this->acl->addComponent($componente, $acciones);
        }

        // Director: acceso total
        $this->acl->allow('director', '*', '*');

        // Invitado: página de inicio, login, logout y páginas de error
        $this->acl->allow('invitado', 'index', 'index');
        $this->acl->allow('invitado', 'auth', ['login', 'logout']);
        $this->acl->allow('invitado', 'error', '*');

        // Profesor
        $this->acl->allow('profesor', 'index', 'index');
        $this->acl->allow('profesor', 'auth', ['login', 'logout']);
        $this->acl->allow('profesor', 'cuenta', 'clave');
        $this->acl->allow('profesor', 'reportes', ['alumnosporcarrera', 'notas']);
        $this->acl->allow('profesor', 'alumnos', ['ver', 'ficha']);
        $this->acl->allow('profesor', 'notas', ['buscar', 'nueva', 'crear', 'editar', 'actualizar']);
        $this->acl->allow('profesor', 'profesor', '*');
        $this->acl->allow('profesor', 'error', '*');

        // Alumno
        $this->acl->allow('alumno', 'index', 'index');
        $this->acl->allow('alumno', 'auth', ['login', 'logout']);
        $this->acl->allow('alumno', 'cuenta', 'clave');
        $this->acl->allow('alumno', 'alumnos', ['ver', 'ficha']);
        $this->acl->allow('alumno', 'alumno', '*');
        $this->acl->allow('alumno', 'error', '*');
    }

    public function isAllowed(string $rol, string $componente, string $accion): bool
    {
        $rol = strtolower(trim($rol));
        $componente = strtolower(trim($componente));
        $accion = strtolower(trim($accion));

        if (!$this->acl->isRole($rol)) {
            $rol = 'invitado';
        }

        if ($rol === 'director') {
            return true;
        }

        if (!$this->acl->isComponent($componente)) {
            return false;
        }

        return (bool) $this->acl->isAllowed($rol, $componente, $accion);
    }
}
