<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 mb-1"><i class="bi bi-shield-check me-2"></i>Auditoría de Calificaciones</h2>
    <p class="text-muted mb-0">Registro inmutable de creación, edición y eliminación de notas.</p>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <form method="get" action="{{ url('auditoria')|e }}" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="accion" class="form-label fw-semibold small text-muted">Acción</label>
        <select name="accion" id="accion" class="form-select">
          <option value="">-- Todas las acciones --</option>
          <option value="crear" {% if filtros['accion'] == 'crear' %}selected{% endif %}>Crear (Ingreso inicial)</option>
          <option value="editar" {% if filtros['accion'] == 'editar' %}selected{% endif %}>Editar (Modificación)</option>
          <option value="eliminar" {% if filtros['accion'] == 'eliminar' %}selected{% endif %}>Eliminar</option>
        </select>
      </div>

      <div class="col-md-3">
        <label for="alumno_id" class="form-label fw-semibold small text-muted">Alumno</label>
        <select name="alumno_id" id="alumno_id" class="form-select">
          <option value="0">-- Todos los alumnos --</option>
          {% for al in alumnos %}
            <option value="{{ al.id|e }}" {% if filtros['alumno_id'] == al.id %}selected{% endif %}>
              {{ al.carnet|default('')|e }} - {{ al.apellidos|e }}, {{ al.nombres|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-md-2">
        <label for="fecha_desde" class="form-label fw-semibold small text-muted">Desde</label>
        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ filtros['fecha_desde']|e }}">
      </div>

      <div class="col-md-2">
        <label for="fecha_hasta" class="form-label fw-semibold small text-muted">Hasta</label>
        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ filtros['fecha_hasta']|e }}">
      </div>

      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <a href="{{ url('auditoria')|e }}" class="btn btn-outline-secondary" title="Limpiar filtros">
          <i class="bi bi-x-circle"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Fecha y Hora</th>
            <th>Usuario</th>
            <th>Alumno</th>
            <th>Curso / Semestre</th>
            <th class="text-center">Acción</th>
            <th class="text-center">Nota Ant.</th>
            <th class="text-center">Nota Nva.</th>
            <th class="pe-4" style="max-width: 280px;">Motivo / Justificación</th>
          </tr>
        </thead>
        <tbody>
          {% if auditorias|length == 0 %}
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No se encontraron registros de auditoría con los filtros seleccionados.
              </td>
            </tr>
          {% else %}
            {% for aud in auditorias %}
              <tr>
                <td class="ps-4 text-nowrap small text-muted">
                  {{ aud.created_at|e }}
                </td>
                <td>
                  {% if aud.usuario %}
                    <span class="badge bg-secondary-subtle text-secondary border">
                      <i class="bi bi-person me-1"></i>{{ aud.usuario.email|e }}
                    </span>
                  {% else %}
                    <span class="text-muted fst-italic">Sistema</span>
                  {% endif %}
                </td>
                <td>
                  {% if aud.alumno %}
                    <strong>{{ aud.alumno.apellidos|e }}, {{ aud.alumno.nombres|e }}</strong>
                    <div class="small text-muted">{{ aud.alumno.carnet|default('')|e }}</div>
                  {% else %}
                    <span class="text-muted">Alumno #{{ aud.alumno_id|e }}</span>
                  {% endif %}
                </td>
                <td>
                  {% if aud.curso %}
                    <div>{{ aud.curso.nombre|e }}</div>
                  {% else %}
                    <div>Curso #{{ aud.curso_id|e }}</div>
                  {% endif %}
                  <div class="small text-muted">
                    Sec. {{ aud.seccion.nombre|default(aud.seccion_id)|e }} &bull; {{ aud.semestre.nombre|default(aud.semestre_id)|e }}
                  </div>
                </td>
                <td class="text-center">
                  {% if aud.accion == 'crear' %}
                    <span class="badge bg-success">Creación</span>
                  {% elseif aud.accion == 'editar' %}
                    <span class="badge bg-warning text-dark">Edición</span>
                  {% elseif aud.accion == 'eliminar' %}
                    <span class="badge bg-danger">Eliminación</span>
                  {% else %}
                    <span class="badge bg-secondary">{{ aud.accion|e }}</span>
                  {% endif %}
                </td>
                <td class="text-center">
                  {% if aud.nota_anterior is not null %}
                    <span class="fw-semibold text-muted">{{ aud.nota_anterior|e }}</span>
                  {% else %}
                    <span class="text-muted">&mdash;</span>
                  {% endif %}
                </td>
                <td class="text-center">
                  {% if aud.nota_nueva is not null %}
                    <span class="fw-bold {% if aud.nota_nueva >= 61 %}text-success{% else %}text-danger{% endif %}">
                      {{ aud.nota_nueva|e }}
                    </span>
                  {% else %}
                    <span class="text-muted">&mdash;</span>
                  {% endif %}
                </td>
                <td class="pe-4 text-break small">
                  {{ aud.motivo|default('Sin motivo registrado')|e }}
                </td>
              </tr>
            {% endfor %}
          {% endif %}
        </tbody>
      </table>
    </div>
  </div>
</div>
