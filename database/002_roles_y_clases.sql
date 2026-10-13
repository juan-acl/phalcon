-- Migración 002: login, roles, clases, inscripciones, auditoría de notas.
-- Para una base YA existente (escuela / registro_alumnos). Respaldar antes: mysqldump -u root -p escuela > respaldo.sql
-- Para instalaciones nuevas y pruebas: incorporar estas tablas a database/schema.sql
-- con el orden de creación: carreras, cursos, secciones, semestres, profesores, alumnos,
-- usuarios, clases, inscripciones, notas, notas_auditoria (y los DROP en orden inverso).

ALTER TABLE semestres ADD COLUMN cerrado TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS profesores (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(150) NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: MySQL no permite CHECK sobre columnas con FK CASCADE/SET NULL; la coherencia
-- rol <-> alumno_id/profesor_id se valida en la aplicación (y en las pruebas).
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('director','profesor','alumno') NOT NULL,
  alumno_id INT UNSIGNED NULL UNIQUE,
  profesor_id INT UNSIGNED NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  debe_cambiar_clave TINYINT(1) NOT NULL DEFAULT 1,
  intentos_fallidos INT UNSIGNED NOT NULL DEFAULT 0,
  bloqueado_hasta DATETIME NULL,
  ultimo_acceso DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_alumno   FOREIGN KEY (alumno_id)   REFERENCES alumnos(id)   ON DELETE CASCADE,
  CONSTRAINT fk_usuarios_profesor FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Una clase = un curso, en una sección, en un semestre, dictado por un profesor.
CREATE TABLE IF NOT EXISTS clases (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  curso_id INT UNSIGNED NOT NULL,
  seccion_id INT UNSIGNED NOT NULL,
  semestre_id INT UNSIGNED NOT NULL,
  profesor_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_clase UNIQUE (curso_id, seccion_id, semestre_id),
  CONSTRAINT fk_clases_curso    FOREIGN KEY (curso_id)    REFERENCES cursos(id)    ON DELETE RESTRICT,
  CONSTRAINT fk_clases_seccion  FOREIGN KEY (seccion_id)  REFERENCES secciones(id) ON DELETE RESTRICT,
  CONSTRAINT fk_clases_semestre FOREIGN KEY (semestre_id) REFERENCES semestres(id) ON DELETE RESTRICT,
  CONSTRAINT fk_clases_profesor FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Qué alumnos cursan cada clase (así el profesor ve a sus estudiantes antes de que existan notas).
CREATE TABLE IF NOT EXISTS inscripciones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  alumno_id INT UNSIGNED NOT NULL,
  clase_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_inscripcion UNIQUE (alumno_id, clase_id),
  CONSTRAINT fk_insc_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
  CONSTRAINT fk_insc_clase  FOREIGN KEY (clase_id)  REFERENCES clases(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notas: estado de publicación y quién/cuándo la modificó. Las notas existentes quedan 'publicada'.
ALTER TABLE notas
  ADD COLUMN estado ENUM('borrador','publicada') NOT NULL DEFAULT 'publicada',
  ADD COLUMN publicada_at DATETIME NULL,
  ADD COLUMN updated_by INT UNSIGNED NULL,
  ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  ADD CONSTRAINT fk_notas_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios(id) ON DELETE SET NULL;

-- Historial de cambios de notas. Sin FK a notas/alumnos a propósito: el historial sobrevive a los borrados.
CREATE TABLE IF NOT EXISTS notas_auditoria (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nota_id INT UNSIGNED NULL,
  alumno_id INT UNSIGNED NOT NULL,
  curso_id INT UNSIGNED NOT NULL,
  semestre_id INT UNSIGNED NOT NULL,
  seccion_id INT UNSIGNED NOT NULL,
  accion ENUM('crear','editar','publicar','eliminar') NOT NULL,
  nota_anterior DECIMAL(5,2) NULL,
  nota_nueva DECIMAL(5,2) NULL,
  motivo VARCHAR(255) NULL,
  usuario_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_aud_nota (nota_id),
  INDEX idx_aud_alumno (alumno_id),
  CONSTRAINT fk_aud_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos existentes -> clases e inscripciones (sin profesor asignado todavía)
INSERT IGNORE INTO clases (curso_id, seccion_id, semestre_id)
SELECT DISTINCT curso_id, seccion_id, semestre_id FROM notas;

INSERT IGNORE INTO inscripciones (alumno_id, clase_id)
SELECT DISTINCT n.alumno_id, c.id
  FROM notas n
  JOIN clases c ON c.curso_id = n.curso_id AND c.seccion_id = n.seccion_id AND c.semestre_id = n.semestre_id;
