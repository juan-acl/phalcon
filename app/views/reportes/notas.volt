<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-card-checklist text-success me-2"></i>Reporte de Calificaciones
    </h2>
    <p class="text-muted small mb-0">Filtro de notas por carrera, curso y sección</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="badge bg-light text-dark border me-1">
      Umbral: {{ umbral|e }} pts
    </span>
    <a href="{{ url('reportes/notas') }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&formato=pdf" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
    </a>
    <a href="{{ url('reportes/notas') }}?carrera_id={{ carreraId|default('')|e }}&curso_id={{ cursoId|default('')|e }}&seccion_id={{ seccionId|default('')|e }}&formato=csv" class="btn btn-outline-success btn-sm shadow-sm">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV
    </a>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-3">
    <form method="get" action="{{ url('reportes/notas') }}" class="row g-2 align-items-center">
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1">Carrera</label>
        <select name="carrera_id" class="form-select form-select-sm">
          <option value="">Todas las carreras</option>
          {% for c in carreras %}
            <option value="{{ c.id|e }}" {% if carreraId == c.id %}selected{% endif %}>
              {{ c.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label small text-muted mb-1">Curso</label>
        <select name="curso_id" class="form-select form-select-sm">
          <option value="">Todos los cursos</option>
          {% for cu in cursos %}
            <option value="{{ cu.id|e }}" {% if cursoId == cu.id %}selected{% endif %}>
              {{ cu.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label small text-muted mb-1">Sección</label>
        <select name="seccion_id" class="form-select form-select-sm">
          <option value="">Todas las secciones</option>
          {% for s in secciones %}
            <option value="{{ s.id|e }}" {% if seccionId == s.id %}selected{% endif %}>
              {{ s.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-md-3 d-flex align-items-end gap-2 pt-3">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filtrar</button>
        <a href="{{ url('reportes/notas') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
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
            <th>Carrera</th>
            <th>Curso</th>
            <th class="text-center">Sección</th>
            <th>Semestre</th>
            <th>Alumno</th>
            <th class="text-center">Nota</th>
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
                <a href="{{ url('alumnos/' ~ f['alumno_id']) }}" class="text-decoration-none text-dark fw-semibold">
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
