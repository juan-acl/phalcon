<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Carrera extends Model
{
    public $id;
    public $nombre;
    public $created_at;
    public $updated_at;

    public function initialize(): void
    {
        $this->setSource('carreras');
        $this->hasMany('id', Alumno::class, 'carrera_id', ['alias' => 'alumnos']);
    }
}
