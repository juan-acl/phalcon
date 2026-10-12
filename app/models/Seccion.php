<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Seccion extends Model
{
    public $id;
    public $nombre;
    public $created_at;
    public $updated_at;

    public function initialize(): void
    {
        $this->setSource('secciones');
        $this->hasMany('id', Nota::class, 'seccion_id', ['alias' => 'notas']);
    }
}
