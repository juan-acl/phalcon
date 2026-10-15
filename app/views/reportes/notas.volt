<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-card-checklist text-success me-2"></i>Reporte de Calificaciones
    </h2>
    <p class="text-muted small mb-0">Filtro de notas por carrera, curso y sección con ordenamiento dinámico</p>
  </div>
  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge bg-light text-dark border">
      Umbral: {{ umbral|e }} pts
    </span>
    <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por={{ ordenarPor|default('default')|e }}&direccion={{ direccion|default('ASC')|e }}&formato=pdf" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
    </a>
    <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por={{ ordenarPor|default('default')|e }}&direccion={{ direccion|default('ASC')|e }}&formato=csv" class="btn btn-outline-success btn-sm shadow-sm">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV
    </a>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-3">
    <form method="get" action="{{ url('reportes/notas')|e }}" class="row g-3 align-items-end">
      <div class="col-12 col-sm-6 col-md-3">
        <label class="form-label small text-muted fw-semibold mb-1">Carrera</label>
        <select name="carrera_id" class="form-select">
          <option value="">Todas las carreras</option>
          {% for c in carreras %}
            <option value="{{ c.id|e }}" {% if carreraId == c.id %}selected{% endif %}>
              {{ c.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <label class="form-label small text-muted fw-semibold mb-1">Curso</label>
        <select name="curso_id" class="form-select">
          <option value="">Todos los cursos</option>
          {% for cu in cursos %}
            <option value="{{ cu.id|e }}" {% if cursoId == cu.id %}selected{% endif %}>
              {{ cu.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-6 col-sm-6 col-md-2">
        <label class="form-label small text-muted fw-semibold mb-1">Sección</label>
        <select name="seccion_id" class="form-select">
          <option value="">Todas</option>
          {% for s in secciones %}
            <option value="{{ s.id|e }}" {% if seccionId == s.id %}selected{% endif %}>
              {{ s.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-6 col-sm-6 col-md-2">
        <label class="form-label small text-muted fw-semibold mb-1">Sentido</label>
        <select name="direccion" class="form-select">
          <option value="ASC" {% if direccion == 'ASC' %}selected{% endif %}>Ascendente</option>
          <option value="DESC" {% if direccion == 'DESC' %}selected{% endif %}>Descendente</option>
        </select>
      </div>

      <div class="col-12 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
        <a href="{{ url('reportes/notas')|e }}" class="btn btn-outline-secondary">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
    <h5 class="card-title fw-bold mb-0 text-dark">Calificaciones Registradas</h5>
    <span class="badge bg-secondary-subtle text-secondary border">
      Total: {{ filas|length|e }} registros
    </span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>
              <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por=carrera&direccion={% if ordenarPor == 'carrera' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Carrera
                {% if ordenarPor == 'carrera' %}<i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th>
              <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por=curso&direccion={% if ordenarPor == 'curso' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Curso
                {% if ordenarPor == 'curso' %}<i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th class="text-center">
              <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por=seccion&direccion={% if ordenarPor == 'seccion' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Sección
                {% if ordenarPor == 'seccion' %}<i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th>Semestre</th>
            <th>
              <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por=alumno&direccion={% if ordenarPor == 'alumno' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Alumno
                {% if ordenarPor == 'alumno' %}<i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th class="text-center">
              <a href="{{ url('reportes/notas')|e }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&ordenar_por=nota&direccion={% if ordenarPor == 'nota' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Nota
                {% if ordenarPor == 'nota' %}<i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th class="text-center">Estado</th>
          </tr>
        </thead>
        <tbody>
          {% for f in filas %}
            <tr>
              <td>
                <span class="badge bg-light text-dark border">{{ f['carrera']|e }}</span>
              </td>
              <td class="fw-semibold text-dark">{{ f['curso']|e }}</td>
              <td class="text-center">{{ f['seccion']|e }}</td>
              <td class="text-muted small">{{ f['semestre']|e }}</td>
              <td>
                <a href="{{ url('alumnos/' ~ f['alumno_id'])|e }}" class="text-decoration-none text-dark fw-semibold">
                  {{ f['apellidos']|e }}, {{ f['nombres']|e }}
                </a>
              </td>
              <td class="text-center fw-bold">{{ f['nota']|e }}</td>
              <td class="text-center">
                {% if f['aprobado'] %}
                  <span class="badge bg-success-subtle text-success border">Aprobado</span>
                {% else %}
                  <span class="badge bg-danger-subtle text-danger border">Reprobado</span>
                {% endif %}
              </td>
            </tr>
          {% else %}
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No se encontraron notas registradas para los filtros aplicados.
              </td>
            </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>
</div>
