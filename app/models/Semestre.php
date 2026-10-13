<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Semestre extends Model
{
    public $id;
    public $nombre;
    public $cerrado;
    public $created_at;
    public $updated_at;

    public function initialize(): void
    {
        $this->setSource('semestres');
        $this->hasMany('id', Nota::class, 'semestre_id', ['alias' => 'notas']);
        $this->hasMany('id', Clase::class, 'semestre_id', ['alias' => 'clases']);
    }
}
