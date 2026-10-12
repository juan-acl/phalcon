<?php

namespace App\Library\Reportes;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Enum;

/**
 * Consultas de los reportes. Los trozos de SQL son fijos (no vienen del usuario)
 * y TODOS los valores del usuario van como parámetros enlazados.
 */
final class ReporteService
{
    public function __construct(private AdapterInterface $db, private int $notaAprobacion = 61)
    {
    }

    /** Alumnos por carrera; $carreraId opcional. */
    public function alumnosPorCarrera(?int $carreraId = null): array
    {
        $sql = 'SELECT c.id AS carrera_id, c.nombre AS carrera, a.id, a.nombres, a.apellidos, a.fecha_nacimiento
                  FROM alumnos a JOIN carreras c ON c.id = a.carrera_id';
        $binds = [];
        if ($carreraId) {
            $sql .= ' WHERE c.id = :carrera';
            $binds['carrera'] = $carreraId;
        }
        $sql .= ' ORDER BY c.nombre, a.apellidos, a.nombres LIMIT 500';

        return $this->db->fetchAll($sql, Enum::FETCH_ASSOC, $binds);
    }

    /** Notas filtradas por carrera, curso y sección (cada filtro es opcional). */
    public function notas(?int $carreraId = null, ?int $cursoId = null, ?int $seccionId = null): array
    {
        $sql = 'SELECT c.nombre AS carrera, cu.nombre AS curso, s.nombre AS seccion, sm.nombre AS semestre,
                       a.id AS alumno_id, a.nombres, a.apellidos, n.nota,
                       (n.nota >= :aprob) AS aprobado
                  FROM notas n
                  JOIN alumnos a    ON a.id  = n.alumno_id
                  JOIN carreras c   ON c.id  = a.carrera_id
                  JOIN cursos cu    ON cu.id = n.curso_id
                  JOIN secciones s  ON s.id  = n.seccion_id
                  JOIN semestres sm ON sm.id = n.semestre_id
                 WHERE 1 = 1';
        $binds = ['aprob' => $this->notaAprobacion];

        if ($carreraId) { $sql .= ' AND c.id = :carrera';  $binds['carrera'] = $carreraId; }
        if ($cursoId)   { $sql .= ' AND cu.id = :curso';   $binds['curso']   = $cursoId; }
        if ($seccionId) { $sql .= ' AND s.id = :seccion';  $binds['seccion'] = $seccionId; }

        $sql .= ' ORDER BY c.nombre, cu.nombre, s.nombre, a.apellidos, a.nombres LIMIT 500';

        return $this->db->fetchAll($sql, Enum::FETCH_ASSOC, $binds);
    }

    /** Historial de notas de un alumno. */
    public function historial(int $alumnoId): array
    {
        $sql = 'SELECT n.id, sm.nombre AS semestre, cu.nombre AS curso, s.nombre AS seccion, n.nota,
                       (n.nota >= :aprob) AS aprobado
                  FROM notas n
                  JOIN cursos cu    ON cu.id = n.curso_id
                  JOIN secciones s  ON s.id  = n.seccion_id
                  JOIN semestres sm ON sm.id = n.semestre_id
                 WHERE n.alumno_id = :alumno
                 ORDER BY sm.id, cu.nombre';

        return $this->db->fetchAll($sql, Enum::FETCH_ASSOC, ['aprob' => $this->notaAprobacion, 'alumno' => $alumnoId]);
    }
}
