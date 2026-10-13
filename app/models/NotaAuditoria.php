<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class NotaAuditoria extends Model
{
    public $id;
    public $nota_id;
    public $alumno_id;
    public $curso_id;
    public $semestre_id;
    public $seccion_id;
    public $accion;
    public $nota_anterior;
    public $nota_nueva;
    public $motivo;
    public $usuario_id;
    public $created_at;

    public function initialize(): void
    {
        $this->setSource('notas_auditoria');
        $this->belongsTo('usuario_id', Usuario::class, 'id', ['alias' => 'usuario']);
        $this->belongsTo('alumno_id', Alumno::class, 'id', ['alias' => 'alumno']);
        $this->belongsTo('curso_id', Curso::class, 'id', ['alias' => 'curso']);
        $this->belongsTo('semestre_id', Semestre::class, 'id', ['alias' => 'semestre']);
        $this->belongsTo('seccion_id', Seccion::class, 'id', ['alias' => 'seccion']);
    }
}
