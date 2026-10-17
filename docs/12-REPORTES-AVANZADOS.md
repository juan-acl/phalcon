# 12 — Reportes avanzados y reglas de precisión

Objetivo: reportes precisos (mejores/peores notas, riesgo, estadísticas, profesores, auditoría), todos con pantalla + PDF + CSV, respetando roles y el kit de servicios (ExportService, AccesoService, NotaService).

## 1. Lo obligatorio del enunciado (verificar primero)
| Requisito | Estado esperado |
|---|---|
| a. Reporte general de alumnos por carrera | Pantalla + PDF + CSV |
| b. Reporte de notas por carrera, curso y sección | Filtros combinables, pantalla + PDF + CSV |
| c. Consulta en pantalla de un alumno con TODA su información e historial de notas | Datos, foto, carrera, historial por semestre, promedio |
| Ingreso de cursos aprobados por estudiante: semestre (catálogo), sección (catálogo), curso (catálogo), nota | Formulario con 3 `select` desde catálogos + nota; validación 0–100; sin duplicar alumno+curso+semestre |

## 2. Reglas de precisión (valen para TODOS los reportes)
1. **Universo de notas:** solo `estado = 'publicada'`. Los borradores solo aparecen en el reporte de "pendientes" (director/profesor dueño).
2. **Aprobado:** `nota >= notaAprobacion` (config, 61). Nunca escribir el número suelto.
3. **Promedio:** promedio simple de las notas del universo, `ROUND(AVG(nota), 2)`. Si no hay notas → mostrar "Sin datos", nunca 0.
4. **Ranking:** `DENSE_RANK()` y "Top N **incluyendo empates**" (si el puesto 10 empata, aparecen todos los empatados). Desempate de orden: apellidos, nombres. Mínimo de cursos para entrar a un ranking de promedios (por defecto 3, configurable) para no premiar a quien tiene 1 sola nota.
5. **No mezclar conteos de varias tablas en un solo JOIN** (duplica filas y falsea totales): usar subconsultas o una consulta por métrica.
6. **Misma consulta** para pantalla, PDF y CSV. Alcance por rol con `AccesoService::alcanceNotas()/alcanceAlumnos()`; el profesor ve solo lo suyo, el alumno no accede a rankings de otros.
7. **Filtros:** enteros casteados, `N` entre 1 y 100, ordenamiento solo por lista blanca, fechas validadas (`Y-m-d`), semestre por defecto = el abierto más reciente.
8. **Encabezado de cada reporte:** título, filtros aplicados, fecha/hora, usuario, umbral usado y universo ("solo notas publicadas").
9. **Tope de filas** (`ExportService::MAX_FILAS`) en PDF/CSV y paginación en pantalla.
10. **Cifras cruzadas:** los totales de un reporte deben coincidir con los de otro (p. ej. suma de aprobados + reprobados = notas del universo). Se verifica con pruebas.
11. La mediana no existe en MySQL: calcularla en PHP sobre las notas del grupo.
12. En PDF/CSV, toda celda de texto escapada (HTML) y protegida contra fórmulas (ya está en el kit).

## 3. Catálogo de reportes nuevos
| ID | Reporte | Contenido | Filtros | Quién |
|---|---|---|---|---|
| R1 | **Mejores alumnos** (cuadro de honor) | Posición, alumno, carrera, promedio, # cursos, mejor nota | semestre, carrera, curso, sección, N, mín. cursos | Director; profesor (sus estudiantes) |
| R2 | **Alumnos con menor rendimiento / en riesgo** | Posición ascendente, promedio, # cursos reprobados, cursos reprobados | semestre, carrera, sección, solo "promedio < umbral" o "≥1 reprobado" | Director; profesor (sus estudiantes) |
| R3 | **Mejores y peores notas individuales** | Las N notas más altas y las N más bajas: alumno, curso, sección, semestre, nota | semestre, curso, sección, carrera, N | Director; profesor (sus clases) |
| R4 | **Estadísticas por curso y sección** | Alumnos, promedio, mediana, mínima, máxima, desviación, % aprobación, reprobados, distribución por rangos (0–60, 61–70, 71–80, 81–90, 91–100) con barras | semestre, carrera, curso, sección | Director; profesor (sus clases) |
| R5 | **Comparativo por sección** | Promedio y % aprobación por sección del mismo curso | semestre, curso | Director; profesor (sus clases) |
| R6 | **Desempeño por profesor** | Profesor, # clases, # alumnos, notas publicadas, **borradores pendientes**, promedio, % aprobación, # correcciones realizadas | semestre, profesor | Director; el profesor ve solo su fila ("Mi desempeño") |
| R7 | **Auditoría de notas** | Fecha, usuario (rol), alumno, curso, semestre, sección, acción, nota anterior → nueva, diferencia, motivo; resumen por usuario y por acción; marca de cambios grandes (≥ 20 puntos) y de cambios en semestre cerrado | fecha desde/hasta, usuario, alumno, acción, semestre | Solo director |
| R8 | **Notas pendientes de publicar** | Clases con borradores, cantidad, profesor, antigüedad | semestre, profesor | Director; profesor (sus clases) |
| R9 | **Evolución del alumno** | Promedio por semestre y variación; cursos aprobados/reprobados; avance | alumno | Director; profesor (si es su estudiante); alumno (solo el propio, vía /mi/) |
| R10 | **Alumnos sin inscripción o sin notas** | Alumnos sin clases en el semestre / con clases sin notas | semestre, carrera | Director |
| R11 | **Tablero del director (KPIs)** | Totales, promedio general, % aprobación por carrera, top 5 / bottom 5, borradores pendientes, últimas 10 correcciones | semestre | Director |

Rutas sugeridas (todas GET, con `?formato=pdf|csv`): `/reportes/mejores`, `/reportes/riesgo`, `/reportes/extremos`, `/reportes/estadisticas`, `/reportes/secciones`, `/reportes/profesores`, `/auditoria` (ya existe), `/reportes/pendientes`, `/alumnos/{id}/evolucion`, `/reportes/sin-notas`, `/director/tablero`.
Permisos (`Permisos.php`): el profesor recibe `mejores, riesgo, extremos, estadisticas, secciones, pendientes, profesores` **con alcance**; `auditoria`, `sinNotas` y `tablero` solo director.

## 4. Consultas clave (MySQL 8 / MariaDB 10.2+; alias `n`, `a`; `{alcance}` = fragmento de AccesoService)
Usar nombres de parámetro distintos en cada aparición (`:u1`, `:u2`) o castear a entero e incrustar el valor (nunca texto del usuario).

**R1 Top N con empates**
```sql
SELECT * FROM (
  SELECT a.id, a.nombres, a.apellidos, ca.nombre AS carrera,
         ROUND(AVG(n.nota), 2) AS promedio, COUNT(*) AS cursos, MAX(n.nota) AS mejor_nota,
         DENSE_RANK() OVER (ORDER BY AVG(n.nota) DESC) AS posicion
    FROM notas n
    JOIN alumnos a   ON a.id = n.alumno_id
    JOIN carreras ca ON ca.id = a.carrera_id
   WHERE n.estado = 'publicada' {filtros opcionales: n.semestre_id, a.carrera_id, n.curso_id, n.seccion_id} {alcance}
   GROUP BY a.id, a.nombres, a.apellidos, ca.nombre
  HAVING COUNT(*) >= :min_cursos
) t
WHERE t.posicion <= :n
ORDER BY t.posicion, t.apellidos, t.nombres
```
**R2 En riesgo:** igual con `ORDER BY AVG(n.nota) ASC`, más `SUM(n.nota < :umbral) AS reprobados` y condición `HAVING AVG(n.nota) < :umbral OR SUM(n.nota < :umbral2) >= 1`.

**R3 Extremos:** dos consultas (`ORDER BY n.nota DESC` / `ASC`) con `DENSE_RANK() OVER (ORDER BY n.nota DESC) <= N` para incluir empates.

**R4 Estadísticas**
```sql
SELECT cu.nombre AS curso, s.nombre AS seccion, sm.nombre AS semestre, COUNT(*) AS alumnos,
       ROUND(AVG(n.nota),2) AS promedio, MIN(n.nota) AS minima, MAX(n.nota) AS maxima,
       ROUND(STDDEV_POP(n.nota),2) AS desviacion,
       SUM(n.nota >= :u1) AS aprobados, SUM(n.nota < :u2) AS reprobados,
       ROUND(100 * SUM(n.nota >= :u3) / COUNT(*), 1) AS pct_aprobacion,
       SUM(n.nota < 61) AS r_0_60, SUM(n.nota BETWEEN 61 AND 70.99) AS r_61_70, SUM(n.nota BETWEEN 71 AND 80.99) AS r_71_80,
       SUM(n.nota BETWEEN 81 AND 90.99) AS r_81_90, SUM(n.nota >= 91) AS r_91_100
  FROM notas n JOIN cursos cu ON cu.id=n.curso_id JOIN secciones s ON s.id=n.seccion_id JOIN semestres sm ON sm.id=n.semestre_id
 WHERE n.estado = 'publicada' {filtros} {alcance}
 GROUP BY cu.id, cu.nombre, s.id, s.nombre, sm.id, sm.nombre
 ORDER BY sm.id, cu.nombre, s.nombre
```
(Los rangos se arman con el umbral de config; el 61 del ejemplo es ilustrativo.)

**R6 Profesor** — una subconsulta por métrica para evitar duplicados:
```sql
SELECT p.id, CONCAT(p.nombres,' ',p.apellidos) AS profesor,
  (SELECT COUNT(*) FROM clases c WHERE c.profesor_id = p.id {AND c.semestre_id=:s}) AS clases,
  (SELECT COUNT(*) FROM inscripciones i JOIN clases c ON c.id=i.clase_id WHERE c.profesor_id = p.id {AND c.semestre_id=:s}) AS alumnos,
  (SELECT COUNT(*) FROM notas n JOIN clases c ON c.curso_id=n.curso_id AND c.seccion_id=n.seccion_id AND c.semestre_id=n.semestre_id
    WHERE c.profesor_id = p.id AND n.estado='publicada' {AND c.semestre_id=:s}) AS notas_publicadas,
  (SELECT COUNT(*) FROM notas n JOIN clases c ON ... WHERE c.profesor_id = p.id AND n.estado='borrador' ...) AS borradores,
  (SELECT ROUND(AVG(n.nota),2) FROM notas n JOIN clases c ON ... WHERE c.profesor_id = p.id AND n.estado='publicada' ...) AS promedio,
  (SELECT ROUND(100*SUM(n.nota>=:u)/COUNT(*),1) FROM notas n JOIN clases c ON ... WHERE ... ) AS pct_aprobacion,
  (SELECT COUNT(*) FROM notas_auditoria au JOIN usuarios u ON u.id=au.usuario_id WHERE u.profesor_id = p.id AND au.accion='editar' {AND au.created_at BETWEEN ...}) AS correcciones
FROM profesores p ORDER BY p.apellidos, p.nombres
```
**R7 Auditoría:** `FROM notas_auditoria au LEFT JOIN usuarios u ON u.id=au.usuario_id LEFT JOIN alumnos a ON a.id=au.alumno_id JOIN cursos/semestres/secciones` con `(au.nota_nueva - au.nota_anterior) AS diferencia`, orden `au.created_at DESC, au.id DESC`, paginado. Marca de "cambio grande": `ABS(diferencia) >= 20`. Marca "en semestre cerrado": `s.cerrado = 1`.
