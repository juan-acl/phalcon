<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Usuario extends Model
{
    public $id;
    public $usuario;
    public $password_hash;
    public $rol;
    public $alumno_id;
    public $profesor_id;
    public $activo;
    public $debe_cambiar_clave;
    public $intentos_fallidos;
    public $bloqueado_hasta;
    public $ultimo_acceso;
    public $created_at;

    public function initialize(): void
    {
        $this->setSource('usuarios');
        $this->belongsTo('alumno_id', Alumno::class, 'id', ['alias' => 'alumno']);
        $this->belongsTo('profesor_id', Profesor::class, 'id', ['alias' => 'profesor']);
        $this->hasMany('id', NotaAuditoria::class, 'usuario_id', ['alias' => 'auditorias']);
    }

    public function esDirector(): bool
    {
        return $this->rol === 'director';
    }

    public function esProfesor(): bool
    {
        return $this->rol === 'profesor';
    }

    public function esAlumno(): bool
    {
        return $this->rol === 'alumno';
    }

    public function estaBloqueado(): bool
    {
        if ($this->bloqueado_hasta === null) {
            return false;
        }
        return strtotime((string) $this->bloqueado_hasta) > time();
    }

    public function verificarPassword(string $password): bool
    {
        return password_verify($password, (string) $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
