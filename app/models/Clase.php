<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Clase extends Model
{
    public $id;
    public $curso_id;
    public $seccion_id;
    public $semestre_id;
    public $profesor_id;
    public $created_at;

    public function initialize(): void
    {
        $this->setSource('clases');
        $this->belongsTo('curso_id', Curso::class, 'id', ['alias' => 'curso']);
        $this->belongsTo('seccion_id', Seccion::class, 'id', ['alias' => 'seccion']);
        $this->belongsTo('semestre_id', Semestre::class, 'id', ['alias' => 'semestre']);
        $this->belongsTo('profesor_id', Profesor::class, 'id', ['alias' => 'profesor']);
        $this->hasMany('id', Inscripcion::class, 'clase_id', ['alias' => 'inscripciones']);
    }
}
