<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Nota extends Model
{
    public $id;
    public $alumno_id;
    public $curso_id;
    public $semestre_id;
    public $seccion_id;
    public $nota;
    public $created_at;
    public $updated_at;

    public function initialize(): void
    {
        $this->setSource('notas');
        $this->belongsTo('alumno_id', Alumno::class, 'id', ['alias' => 'alumno']);
        $this->belongsTo('curso_id', Curso::class, 'id', ['alias' => 'curso']);
        $this->belongsTo('semestre_id', Semestre::class, 'id', ['alias' => 'semestre']);
        $this->belongsTo('seccion_id', Seccion::class, 'id', ['alias' => 'seccion']);
    }
}
