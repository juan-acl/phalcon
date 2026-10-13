<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Profesor extends Model
{
    public $id;
    public $nombres;
    public $apellidos;
    public $email;
    public $created_at;

    public function initialize(): void
    {
        $this->setSource('profesores');
        $this->hasMany('id', Clase::class, 'profesor_id', ['alias' => 'clases']);
        $this->hasOne('id', Usuario::class, 'profesor_id', ['alias' => 'usuario']);
    }

    public function getNombreCompleto(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
}
