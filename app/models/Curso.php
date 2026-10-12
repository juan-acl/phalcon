<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Curso extends Model
{
    public $id;
    public $nombre;
    public $created_at;
    public $updated_at;

    public function initialize(): void
    {
        $this->setSource('cursos');
        $this->hasMany('id', Nota::class, 'curso_id', ['alias' => 'notas']);
    }
}
