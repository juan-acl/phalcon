<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Alumno extends Model
{
    public $id;
    public $nombres;
    public $apellidos;
    public $fecha_nacimiento;
    public $foto;
    public $carrera_id;
    public $created_at;
    public $updated_at;

    public function initialize(): void
    {
        $this->setSource('alumnos');
        $this->belongsTo('carrera_id', Carrera::class, 'id', ['alias' => 'carrera']);
        $this->hasMany('id', Nota::class, 'alumno_id', ['alias' => 'notas']);
        $this->hasMany('id', Inscripcion::class, 'alumno_id', ['alias' => 'inscripciones']);
        $this->hasOne('id', Usuario::class, 'alumno_id', ['alias' => 'usuario']);
    }

    public function getNombreCompleto(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
}
