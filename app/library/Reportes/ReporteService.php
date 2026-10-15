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

    /** Alumnos por carrera; $carreraId opcional. Permite ordenamiento dinámico seguro. */
    public function alumnosPorCarrera(?int $carreraId = null, string $ordenarPor = 'id', string $direccion = 'ASC'): array
    {
        $dir = strtoupper(trim($direccion)) === 'DESC' ? 'DESC' : 'ASC';
        $ordenSql = match (strtolower(trim($ordenarPor))) {
            'carrera'          => "c.nombre {$dir}, a.apellidos {$dir}, a.nombres {$dir}",
            'apellidos'        => "a.apellidos {$dir}, a.nombres {$dir}",
            'fecha_nacimiento' => "a.fecha_nacimiento {$dir}, a.apellidos {$dir}",
            default            => "a.id {$dir}",
        };

        $sql = 'SELECT c.id AS carrera_id, c.nombre AS carrera, a.id, a.nombres, a.apellidos, a.fecha_nacimiento
                  FROM alumnos a JOIN carreras c ON c.id = a.carrera_id';
        $binds = [];
        if ($carreraId) {
            $sql .= ' WHERE c.id = :carrera';
            $binds['carrera'] = $carreraId;
        }
        $sql .= " ORDER BY {$ordenSql} LIMIT 500";

        return $this->db->fetchAll($sql, Enum::FETCH_ASSOC, $binds);
    }

    /** Notas filtradas por carrera, curso y sección con ordenamiento seguro. */
    public function notas(?int $carreraId = null, ?int $cursoId = null, ?int $seccionId = null, string $ordenarPor = 'default', string $direccion = 'ASC'): array
    {
        $dir = strtoupper(trim($direccion)) === 'DESC' ? 'DESC' : 'ASC';
        $ordenSql = match (strtolower(trim($ordenarPor))) {
            'nota'     => "n.nota {$dir}, a.apellidos ASC",
            'alumno'   => "a.apellidos {$dir}, a.nombres {$dir}",
            'curso'    => "cu.nombre {$dir}, s.nombre ASC, a.apellidos ASC",
            'carrera'  => "c.nombre {$dir}, cu.nombre ASC, a.apellidos ASC",
            'seccion'  => "s.nombre {$dir}, a.apellidos ASC",
            default    => "c.nombre {$dir}, cu.nombre {$dir}, s.nombre {$dir}, a.apellidos {$dir}, a.nombres {$dir}",
        };

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

        $sql .= " ORDER BY {$ordenSql} LIMIT 500";

        return $this->db->fetchAll($sql, Enum::FETCH_ASSOC, $binds);
    }

    /** Historial de notas de un alumno; $soloPublicadas filtra notas en borrador (para el alumno). */
    public function historial(int $alumnoId, bool $soloPublicadas = false): array
    {
        $sql = 'SELECT n.id, n.estado, n.updated_at, sm.id AS semestre_id, sm.nombre AS semestre, sm.cerrado AS semestre_cerrado,
                       cu.nombre AS curso, s.nombre AS seccion, n.nota,
                       (n.nota >= :aprob) AS aprobado,
                       cl.profesor_id
                  FROM notas n
                  JOIN cursos cu    ON cu.id = n.curso_id
                  JOIN secciones s  ON s.id  = n.seccion_id
                  JOIN semestres sm ON sm.id = n.semestre_id
                  LEFT JOIN clases cl ON cl.curso_id = n.curso_id AND cl.seccion_id = n.seccion_id AND cl.semestre_id = n.semestre_id
                 WHERE n.alumno_id = :alumno';
        $binds = ['aprob' => $this->notaAprobacion, 'alumno' => $alumnoId];

        if ($soloPublicadas) {
            $sql .= " AND n.estado = 'publicada'";
        }

        $sql .= ' ORDER BY sm.id, cu.nombre';

        return $this->db->fetchAll($sql, Enum::FETCH_ASSOC, $binds);
    }
}
