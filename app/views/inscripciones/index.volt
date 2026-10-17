<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-person-check text-primary me-2"></i>Inscripciones de Alumnos</h2>
    <p class="text-muted small mb-0">Gestión de estudiantes matriculados en cada clase académica</p>
  </div>
  <a href="{{ url('inscripciones/nueva' ~ (claseSeleccionada ? '?clase_id=' ~ claseSeleccionada : '')) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Inscribir Alumno
  </a>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body bg-light p-3">
    <form method="get" action="{{ url('inscripciones') }}" class="row g-2 align-items-end">

      <div class="col-md-3">
        <label for="filtro_semestre" class="form-label fw-semibold small mb-1">Semestre:</label>
        <select name="semestre_id" id="filtro_semestre" class="form-select form-select-sm" onchange="document.getElementById('filtro_clase').value='';this.form.submit()">
          <option value="">-- Todos --</option>
          {% for sem in semestres %}
            <option value="{{ sem.id }}" {% if semestreSeleccionado == sem.id %}selected{% endif %}>
              {{ sem.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-md-4">
        <label for="filtro_clase" class="form-label fw-semibold small mb-1">Clase:</label>
        <select name="clase_id" id="filtro_clase" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">-- Todas las clases --</option>
          {% for cl in clases %}
            <option value="{{ cl.id }}" {% if claseSeleccionada == cl.id %}selected{% endif %}>
              {{ cl.curso ? cl.curso.nombre|e : '' }} (Sec. {{ cl.seccion ? cl.seccion.nombre|e : '' }}) - {{ cl.semestre ? cl.semestre.nombre|e : '' }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-md-4">
        <label for="filtro_q" class="form-label fw-semibold small mb-1">Buscar alumno:</label>
        <div class="input-group input-group-sm">
          <input type="text" name="q" id="filtro_q" class="form-control form-control-sm"
                 placeholder="Nombre o apellido…" value="{{ q|e }}">
          <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>

      {% if claseSeleccionada or semestreSeleccionado or q %}
        <div class="col-auto align-self-end">
          <a href="{{ url('inscripciones') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Limpiar
          </a>
        </div>
      {% endif %}

    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Alumno</th>
          <th>Curso</th>
          <th class="text-center">Sección</th>
          <th>Semestre</th>
          <th>Docente</th>
          <th class="text-end pe-4">Acciones</th>
        </tr>
      </thead>
      <tbody>
        {% for i in inscripciones %}
          <tr>
            <td class="text-muted small">#{{ i.id|e }}</td>
            <td class="fw-semibold text-dark">
              {% if i.alumno %}
                <a href="{{ url('alumnos/' ~ i.alumno.id) }}" class="text-decoration-none">
                  {{ i.alumno.getNombreCompleto()|e }}
                </a>
              {% else %}
                N/A
              {% endif %}
            </td>
            <td>{{ i.clase and i.clase.curso ? i.clase.curso.nombre|e : 'N/A' }}</td>
            <td class="text-center"><span class="badge bg-light text-dark border">{{ i.clase and i.clase.seccion ? i.clase.seccion.nombre|e : 'N/A' }}</span></td>
            <td>{{ i.clase and i.clase.semestre ? i.clase.semestre.nombre|e : 'N/A' }}</td>
            <td>{{ i.clase and i.clase.profesor ? i.clase.profesor.getNombreCompleto()|e : 'Sin profesor' }}</td>
            <td class="text-end pe-4">
              <form action="{{ url('inscripciones/' ~ i.id ~ '/eliminar') }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de anular esta inscripción?');">
                <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Desinscribir">
                  <i class="bi bi-x-circle me-1"></i>Anular
                </button>
              </form>
            </td>
          </tr>
        {% else %}
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">
              No hay alumnos inscritos con los filtros seleccionados.
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>
  </div>
</div>
