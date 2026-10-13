<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Inscripcion extends Model
{
    public $id;
    public $alumno_id;
    public $clase_id;
    public $created_at;

    public function initialize(): void
    {
        $this->setSource('inscripciones');
        $this->belongsTo('alumno_id', Alumno::class, 'id', ['alias' => 'alumno']);
        $this->belongsTo('clase_id', Clase::class, 'id', ['alias' => 'clase']);
    }
}
