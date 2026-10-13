<?php

namespace App\Library\Auth;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Enum;

/**
 * Control por fila (pertenencia): verifica si el usuario autenticado
 * tiene autorización para acceder o modificar un registro específico.
 */
class AccesoService
{
    public function __construct(private AdapterInterface $db)
    {
    }

    /** ¿El profesor imparte esta clase? */
    public function profesorPuedeVerClase(int $profesorId, int $claseId): bool
    {
        $sql = 'SELECT 1 FROM clases WHERE id = :cid AND profesor_id = :pid LIMIT 1';
        $res = $this->db->fetchOne($sql, Enum::FETCH_ASSOC, ['cid' => $claseId, 'pid' => $profesorId]);
        return !empty($res);
    }

    /** ¿El alumno está inscrito en alguna clase impartida por este profesor? */
    public function profesorPuedeVerAlumno(int $profesorId, int $alumnoId): bool
    {
        $sql = 'SELECT 1 FROM inscripciones i
                  JOIN clases c ON c.id = i.clase_id
                 WHERE i.alumno_id = :aid AND c.profesor_id = :pid LIMIT 1';
        $res = $this->db->fetchOne($sql, Enum::FETCH_ASSOC, ['aid' => $alumnoId, 'pid' => $profesorId]);
        return !empty($res);
    }

    /** ¿La nota pertenece a una clase dictada por este profesor? */
    public function profesorPuedeEditarNota(int $profesorId, int $notaId): bool
    {
        $sql = 'SELECT n.id FROM notas n
                  JOIN clases c ON c.curso_id = n.curso_id
                               AND c.seccion_id = n.seccion_id
                               AND c.semestre_id = n.semestre_id
                 WHERE n.id = :nid AND c.profesor_id = :pid LIMIT 1';
        $res = $this->db->fetchOne($sql, Enum::FETCH_ASSOC, ['nid' => $notaId, 'pid' => $profesorId]);
        return !empty($res);
    }

    /** El alumno solo puede ver su propio alumno_id */
    public function alumnoPuedeVerFicha(int $alumnoSessionId, int $alumnoRequestedId): bool
    {
        return $alumnoSessionId === $alumnoRequestedId;
    }
}
