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
 ('Laura Patricia', 'García Ruiz', 'laura.garcia@escuela.edu'),
 ('Rodrigo Antonio', 'Morales Paz', 'rodrigo.morales@escuela.edu'),
 ('Claudia Maribel', 'Hernández Santos', 'claudia.hernandez@escuela.edu');

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
 ('Kevin Estuardo','Salazar Reyes','2007-10-19',1),
 ('Andrea Nicole','Alvarado Guzmán','2008-03-12',2),
 ('Bryan Alexander','Monterroso Velásquez','2007-09-08',1),
 ('Camila Fernanda','Ochoa Figueroa','2008-07-23',3),
 ('David Eduardo','Castellanos Silva','2007-12-05',2),
 ('Estefanía Rocío','Herrera Paredes','2008-06-18',1),
 ('Francisco Javier','Vásquez Fuentes','2007-04-30',3),
 ('Gabriela Elizabeth','Estrada Miranda','2008-11-14',2),
 ('Héctor Manuel','Sánchez Cordero','2007-08-22',1),
 ('Ingrid Johana','Palacios Carrillo','2008-01-29',3),
 ('Jorge Mario','Ruano Del Valle','2007-10-05',2),
 ('Karen Melissa','Samayoa Pinto','2008-08-11',1),
 ('Leonardo Daniel','Bautista Lemus','2007-05-16',3),
 ('Mariana Sofía','Corado Cifuentes','2008-12-03',2),
 ('Nelson Fernando','Cifuentes Gil','2007-07-27',1),
 ('Paola Renee','Zúñiga Cabrera','2008-09-09',3);

-- Cursos con notas para los estudiantes
INSERT INTO notas (alumno_id, curso_id, semestre_id, seccion_id, nota, estado, publicada_at)
SELECT a.id, c.id, 1, 1 + MOD(a.id, 3), 61 + MOD(a.id * 17 + c.id * 29, 40), 'publicada', NOW()
FROM alumnos a JOIN cursos c ON c.id <= 4;

-- Generar todas las clases con profesores asignados para todas las materias y secciones
INSERT IGNORE INTO clases (curso_id, seccion_id, semestre_id, profesor_id)
SELECT c.id, s.id, 1, 1 + FLOOR((c.id - 1) / 2)
  FROM cursos c
 CROSS JOIN secciones s;

-- Generar inscripciones en las clases correspondientes a las notas
INSERT IGNORE INTO inscripciones (alumno_id, clase_id)
SELECT DISTINCT n.alumno_id, cl.id
  FROM notas n
  JOIN clases cl ON cl.curso_id = n.curso_id AND cl.seccion_id = n.seccion_id AND cl.semestre_id = n.semestre_id;

-- Usuarios de demostración (director: Director123!, profesores: Profesor123!, alumnos: Alumno123!)
INSERT IGNORE INTO usuarios (usuario, password_hash, rol, profesor_id, alumno_id, activo, debe_cambiar_clave) VALUES
 ('director',  '$2y$10$zmqtq0P/lra6Gj7oa4VCluW57xAXJwjlkyprmuGj/qmwnHXrg44eW', 'director', NULL, NULL, 1, 0),
 ('profesor1', '$2y$10$o1Tn0rlf5LdokHE4AkrdAewbFC9/WMyQExqEshfgmuTmx/2ZO6apu', 'profesor', 1,    NULL, 1, 0),
 ('profesor2', '$2y$10$o1Tn0rlf5LdokHE4AkrdAewbFC9/WMyQExqEshfgmuTmx/2ZO6apu', 'profesor', 2,    NULL, 1, 0),
 ('profesor3', '$2y$10$o1Tn0rlf5LdokHE4AkrdAewbFC9/WMyQExqEshfgmuTmx/2ZO6apu', 'profesor', 3,    NULL, 1, 0),
 ('profesor4', '$2y$10$o1Tn0rlf5LdokHE4AkrdAewbFC9/WMyQExqEshfgmuTmx/2ZO6apu', 'profesor', 4,    NULL, 1, 0),
 ('alumno1',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 1,    1, 0),
 ('alumno2',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 2,    1, 0),
 ('alumno3',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 3,    1, 0),
 ('alumno4',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 4,    1, 0),
 ('alumno5',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 5,    1, 0),
 ('alumno6',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 6,    1, 0),
 ('alumno7',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 7,    1, 0),
 ('alumno8',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 8,    1, 0),
 ('alumno9',   '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 9,    1, 0),
 ('alumno10',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 10,   1, 0),
 ('alumno11',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 11,   1, 0),
 ('alumno12',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 12,   1, 0),
 ('alumno13',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 13,   1, 0),
 ('alumno14',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 14,   1, 0),
 ('alumno15',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 15,   1, 0),
 ('alumno16',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 16,   1, 0),
 ('alumno17',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 17,   1, 0),
 ('alumno18',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 18,   1, 0),
 ('alumno19',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 19,   1, 0),
 ('alumno20',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 20,   1, 0),
 ('alumno21',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 21,   1, 0),
 ('alumno22',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 22,   1, 0),
 ('alumno23',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 23,   1, 0),
 ('alumno24',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 24,   1, 0),
 ('alumno25',  '$2y$10$PX//pc5C5rs8yOG4vAsZsOdiGNKC6UziaI2Biv1lT5oksgHNpSp4W', 'alumno',   NULL, 25,   1, 0);
