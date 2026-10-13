<?php

namespace Tests\Feature;

use Tests\Support\HttpTestCase;

/**
 * Pruebas completas del módulo de Autenticación, Sesiones y Control de Acceso (ACL).
 */
class AuthTest extends HttpTestCase
{
    public function test_invitado_es_redirigido_a_login_en_rutas_protegidas(): void
    {
        $this->loginComo('invitado');

        foreach (['/alumnos', '/alumnos/nuevo', '/catalogos/carreras', '/reportes/notas', '/notas/buscar'] as $ruta) {
            $r = $this->get($ruta);
            $this->assertSame(302, $r->getStatusCode(), "Invitado no fue redirigido en {$ruta}");
            $this->assertStringContainsString('login', $r->getHeaderLine('Location'));
        }
    }

    public function test_pagina_principal_y_login_son_accesibles_para_invitados(): void
    {
        $this->loginComo('invitado');

        $r = $this->get('/');
        $this->assertSame(200, $r->getStatusCode());

        $r = $this->get('/login');
        $this->assertSame(200, $r->getStatusCode());
    }

    public function test_login_con_credenciales_invalidas_muestra_error_422(): void
    {
        $this->loginComo('invitado');

        // Usuario inexistente
        $r = $this->submit('/login', ['usuario' => 'no_existe', 'password' => 'Cualquiera123!']);
        $this->assertSame(422, $r->getStatusCode());
        $this->assertStringContainsString('incorrectos', $this->cuerpo($r));

        // Director con contraseña incorrecta
        $r = $this->submit('/login', ['usuario' => 'director', 'password' => 'ClaveEquivocada!']);
        $this->assertSame(422, $r->getStatusCode());
        $this->assertStringContainsString('incorrectos', $this->cuerpo($r));
    }

    public function test_bloqueo_por_cinco_intentos_fallidos(): void
    {
        $this->loginComo('invitado');

        for ($i = 1; $i <= 4; $i++) {
            $r = $this->submit('/login', ['usuario' => 'director', 'password' => 'Falsa' . $i]);
            $this->assertSame(422, $r->getStatusCode());
        }

        // Intento 5: se bloquea
        $r = $this->submit('/login', ['usuario' => 'director', 'password' => 'Falsa5']);
        $this->assertSame(422, $r->getStatusCode());

        // Intento 6 (incluso con la clave correcta): debe responder que la cuenta está bloqueada
        $r = $this->submit('/login', ['usuario' => 'director', 'password' => 'Director123!']);
        $this->assertSame(422, $r->getStatusCode());
        $this->assertStringContainsString('bloqueada', $this->cuerpo($r));
    }

    public function test_usuario_inactivo_no_puede_acceder(): void
    {
        $this->loginComo('invitado');

        $this->insertar('usuarios', [
            'usuario'            => 'inactivo',
            'password_hash'      => password_hash('Inactivo123!', PASSWORD_DEFAULT),
            'rol'                => 'director',
            'activo'             => 0,
            'debe_cambiar_clave' => 0,
        ]);

        $r = $this->submit('/login', ['usuario' => 'inactivo', 'password' => 'Inactivo123!']);
        $this->assertSame(422, $r->getStatusCode());
        $this->assertStringContainsString('incorrectos', $this->cuerpo($r));
    }

    public function test_login_exitoso_y_logout_por_post(): void
    {
        $this->loginComo('invitado');

        $r = $this->submit('/login', ['usuario' => 'director', 'password' => 'Director123!']);
        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString('alumnos', $r->getHeaderLine('Location'));

        // Ya autenticado: puede ver /alumnos
        $r = $this->get('/alumnos');
        $this->assertSame(200, $r->getStatusCode());

        // Logout por POST
        $r = $this->submit('/alumnos', [], [], '/logout');
        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString('login', $r->getHeaderLine('Location'));

        // Tras cerrar sesión, intentar entrar a /alumnos redirige a login
        $r = $this->get('/alumnos');
        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString('login', $r->getHeaderLine('Location'));
    }

    public function test_usuario_forzado_a_cambiar_clave_es_redirigido_hasta_que_la_actualiza(): void
    {
        $this->loginComo('invitado');

        $this->insertar('usuarios', [
            'usuario'            => 'primeravez',
            'password_hash'      => password_hash('Temporal123!', PASSWORD_DEFAULT),
            'rol'                => 'director',
            'activo'             => 1,
            'debe_cambiar_clave' => 1,
        ]);

        // Login
        $r = $this->submit('/login', ['usuario' => 'primeravez', 'password' => 'Temporal123!']);
        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString('cuenta/clave', $r->getHeaderLine('Location'));

        // Intento de navegar a /alumnos antes de cambiar clave -> redirigido a /cuenta/clave
        $r = $this->get('/alumnos');
        $this->assertSame(302, $r->getStatusCode());
        $this->assertStringContainsString('cuenta/clave', $r->getHeaderLine('Location'));

        // Cambiar clave con datos válidos
        $r = $this->submit('/cuenta/clave', [
            'password_actual'       => 'Temporal123!',
            'password_nueva'        => 'NuevaSegura456!',
            'password_confirmacion' => 'NuevaSegura456!',
        ]);
        $this->assertSame(302, $r->getStatusCode());

        // Ahora sí tiene acceso libre
        $r = $this->get('/alumnos');
        $this->assertSame(200, $r->getStatusCode());
    }

    public function test_acl_roles_profesor_y_alumno_bloquean_rutas_no_autorizadas(): void
    {
        // 1. Alumno no puede gestionar catálogos ni crear alumnos
        $this->loginComo('alumno');
        $this->assertSame(403, $this->get('/catalogos/carreras')->getStatusCode());
        $this->assertSame(403, $this->get('/alumnos/nuevo')->getStatusCode());

        // 2. Profesor no puede gestionar catálogos de carreras
        $this->loginComo('profesor');
        $this->assertSame(403, $this->get('/catalogos/carreras/nuevo')->getStatusCode());
    }
}
