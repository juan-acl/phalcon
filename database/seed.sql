-- Datos de demostración. Ejecutar DESPUÉS de schema.sql:
--   mysql -u root -p escuela < database/seed.sql
INSERT INTO carreras (nombre) VALUES
 ('Bachillerato en Computación'),('Bachillerato en Ciencias y Letras'),('Bachillerato en Mercadotecnia');

INSERT INTO cursos (nombre) VALUES
 ('Matemática'),('Comunicación y Lenguaje'),('Física'),('Química'),
 ('Estudios Sociales'),('Inglés'),('Informática'),('Contabilidad');

INSERT INTO secciones (nombre) VALUES ('A'),('B'),('C');

INSERT INTO semestres (nombre, cerrado) VALUES
 ('Primer semestre 2025', 0),('Segundo semestre 2025', 0),('Primer semestre 2026', 0);

INSERT INTO profesores (nombres, apellidos, email) VALUES
 ('Carlos Eduardo', 'Mendoza Soto', 'carlos.mendoza@escuela.edu'),
 ('Laura Patricia', 'García Ruiz', 'laura.garcia@escuela.edu');

INSERT INTO alumnos (nombres, apellidos, fecha_nacimiento, carrera_id) VALUES
 ('Ana María','Pérez López','2008-05-14',1),
 ('Luis Fernando','Gómez Ramírez','2007-11-02',1),
 ('María José','Hernández Castillo','2008-02-21',2),
 ('Carlos Andrés','Morales Díaz','2007-08-30',2),
 ('Sofía Alejandra','Ramos Cifuentes','2008-01-17',3),
 ('Diego Alejandro','Chávez Ordóñez','2007-06-09',3),
 ('Valeria Lucía','Méndez Torres','2008-09-25',1),
 ('José Pablo','Barrios Solís','2007-12-12',2),
 ('Daniela Michelle','López Aguilar','2008-04-03',3),
 ('Kevin Estuardo','Salazar Reyes','2007-10-19',1);

-- 4 cursos aprobados por alumno (notas entre 61 y 100), determinístico
INSERT INTO notas (alumno_id, curso_id, semestre_id, seccion_id, nota, estado, publicada_at)
SELECT a.id, c.id, 1, 1 + MOD(a.id, 3), 61 + MOD(a.id * 17 + c.id * 29, 40), 'publicada', NOW()
FROM alumnos a JOIN cursos c ON c.id <= 4;

-- Generar clases e inscripciones a partir de las notas
INSERT IGNORE INTO clases (curso_id, seccion_id, semestre_id, profesor_id)
SELECT DISTINCT curso_id, seccion_id, semestre_id, 1 + MOD(curso_id, 2) FROM notas;

INSERT IGNORE INTO inscripciones (alumno_id, clase_id)
SELECT DISTINCT n.alumno_id, c.id
  FROM notas n
  JOIN clases c ON c.curso_id = n.curso_id AND c.seccion_id = n.seccion_id AND c.semestre_id = n.semestre_id;

-- Usuarios de demostración (director: Director123!, profesores: Profesor123!, alumnos: Alumno123!)
INSERT IGNORE INTO usuarios (usuario, password_hash, rol, profesor_id, alumno_id, activo, debe_cambiar_clave) VALUES
 ('director',  '$2y$10$zmqtq0P/lra6Gj7oa4VCluW57xAXJwjlkyprmuGj/qmwnHXrg44eW', 'director', NULL, NULL, 1, 0),
 ('profesor1', '$2y$10$o1Tn0rlf5LdokHE4AkrdAewbFC9/WMyQExqEshfgmuTmx/2ZO6apu', 'profesor', 1,    NULL, 1, 0),
 ('profesor2', '$2y$10$o1Tn0rlf5LdokHE4AkrdAewbFC9/WMyQExqEshfgmuTmx/2ZO6apu', 'profesor', 2,    NULL, 1, 0),
 ('alumno1',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 1,    1, 0),
 ('alumno2',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 2,    1, 0),
 ('alumno3',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 3,    1, 0),
 ('alumno4',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 4,    1, 0),
 ('alumno5',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 5,    1, 0),
 ('alumno6',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 6,    1, 0),
 ('alumno7',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 7,    1, 0),
 ('alumno8',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 8,    1, 0),
 ('alumno9',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 9,    1, 0),
 ('alumno10',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 10,   1, 0);
