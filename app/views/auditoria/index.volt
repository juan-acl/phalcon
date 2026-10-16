<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h2 class="h3 mb-1"><i class="bi bi-shield-check me-2"></i>Auditoría de Calificaciones</h2>
    <p class="text-muted mb-0">Registro inmutable de trazabilidad, modificaciones y revocaciones de notas.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ url('auditoria?formato=pdf' ~ (queryString ? '&' ~ queryString : ''))|e }}" class="btn btn-outline-danger" target="_blank">
      <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
    </a>
    <a href="{{ url('auditoria?formato=csv' ~ (queryString ? '&' ~ queryString : ''))|e }}" class="btn btn-outline-success">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV
    </a>
  </div>
</div>

<!-- Tarjetas de Resumen KPI -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
      <div class="card-body p-3">
        <div class="text-muted small text-uppercase fw-semibold">Total Eventos</div>
        <div class="fs-4 fw-bold text-dark mt-1">{{ resumen['total']|e }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
      <div class="card-body p-3">
        <div class="text-muted small text-uppercase fw-semibold">Ediciones</div>
        <div class="fs-4 fw-bold text-warning mt-1">{{ resumen['editar']|e }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
      <div class="card-body p-3">
        <div class="text-muted small text-uppercase fw-semibold">Creaciones</div>
        <div class="fs-4 fw-bold text-success mt-1">{{ resumen['crear']|e }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
      <div class="card-body p-3">
        <div class="text-muted small text-uppercase fw-semibold">Eliminaciones</div>
        <div class="fs-4 fw-bold text-danger mt-1">{{ resumen['eliminar']|e }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card shadow-sm border-0 border-start border-danger border-4 h-100 bg-danger-subtle bg-opacity-25">
      <div class="card-body p-3">
        <div class="text-danger small text-uppercase fw-bold">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>&ge; 20 Puntos
        </div>
        <div class="fs-4 fw-bold text-danger mt-1">{{ resumen['cambios_grandes']|e }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card shadow-sm border-0 border-start border-secondary border-4 h-100">
      <div class="card-body p-3">
        <div class="text-secondary small text-uppercase fw-semibold">
          <i class="bi bi-lock-fill me-1"></i>Semestre Cerrado
        </div>
        <div class="fs-4 fw-bold text-secondary mt-1">{{ resumen['semestre_cerrado']|e }}</div>
      </div>
    </div>
  </div>
</div>

<!-- Filtros de búsqueda -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <form method="get" action="{{ url('auditoria')|e }}" class="row g-3 align-items-end">
      <div class="col-12 col-sm-6 col-lg-3">
        <label for="accion" class="form-label fw-semibold small text-muted">Acción</label>
        <select name="accion" id="accion" class="form-select">
          <option value="">-- Todas las acciones --</option>
          <option value="crear" {% if filtros['accion'] == 'crear' %}selected{% endif %}>Creación (Ingreso inicial)</option>
          <option value="editar" {% if filtros['accion'] == 'editar' %}selected{% endif %}>Edición (Modificación)</option>
          <option value="eliminar" {% if filtros['accion'] == 'eliminar' %}selected{% endif %}>Eliminación</option>
        </select>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <label for="alumno_id" class="form-label fw-semibold small text-muted">Estudiante</label>
        <select name="alumno_id" id="alumno_id" class="form-select">
          <option value="0">-- Todos los estudiantes --</option>
          {% for al in alumnos %}
            <option value="{{ al.id|e }}" {% if filtros['alumno_id'] == al.id %}selected{% endif %}>
              {{ al.apellidos|e }}, {{ al.nombres|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <label for="usuario_id" class="form-label fw-semibold small text-muted">Usuario Operador</label>
        <select name="usuario_id" id="usuario_id" class="form-select">
          <option value="0">-- Todos los usuarios --</option>
          {% for u in usuarios %}
            <option value="{{ u.id|e }}" {% if filtros['usuario_id'] == u.id %}selected{% endif %}>
              {{ u.usuario|e }} ({{ u.rol|capitalize|e }})
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <label for="semestre_id" class="form-label fw-semibold small text-muted">Semestre</label>
        <select name="semestre_id" id="semestre_id" class="form-select">
          <option value="0">-- Todos los semestres --</option>
          {% for s in semestres %}
            <option value="{{ s.id|e }}" {% if filtros['semestre_id'] == s.id %}selected{% endif %}>
              {{ s.nombre|e }} {% if s.cerrado %}(Cerrado){% endif %}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-6 col-sm-6 col-lg-3">
        <label for="fecha_desde" class="form-label fw-semibold small text-muted">Fecha Desde</label>
        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ filtros['fecha_desde']|e }}">
      </div>

      <div class="col-6 col-sm-6 col-lg-3">
        <label for="fecha_hasta" class="form-label fw-semibold small text-muted">Fecha Hasta</label>
        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ filtros['fecha_hasta']|e }}">
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-center">
        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" name="cambio_grande" value="1" id="cambio_grande" {% if filtros['cambio_grande'] == 1 %}checked{% endif %}>
          <label class="form-check-label small fw-semibold text-danger" for="cambio_grande">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>Solo cambios &ge; 20 pts
          </label>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <a href="{{ url('auditoria')|e }}" class="btn btn-outline-secondary" title="Limpiar filtros">
          <i class="bi bi-x-circle"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Tabla de Registros de Auditoría -->
<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Fecha y Hora</th>
            <th>Operador</th>
            <th>Estudiante</th>
            <th>Curso y Sección</th>
            <th>Semestre</th>
            <th class="text-center">Acción</th>
            <th class="text-center">Ant.</th>
            <th class="text-center">Nva.</th>
            <th class="text-center">Dif.</th>
            <th class="pe-4" style="max-width: 280px;">Motivo / Justificación</th>
          </tr>
        </thead>
        <tbody>
          {% if auditorias|length == 0 %}
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No se encontraron registros de auditoría con los filtros seleccionados.
              </td>
            </tr>
          {% else %}
            {% for aud in auditorias %}
              <tr>
                <td class="ps-4 text-nowrap small text-muted">
                  {{ aud['created_at']|e }}
                </td>
                <td>
                  <span class="fw-semibold text-dark small d-block">
                    <i class="bi bi-person-circle text-secondary me-1"></i>{{ aud['usuario_nombre']|e }}
                  </span>
                  <span class="badge bg-secondary-subtle text-secondary border small">
                    {{ aud['usuario_rol']|e }}
                  </span>
                </td>
                <td>
                  <strong>{{ aud['alumno_nombre']|e }}</strong>
                </td>
                <td>
                  <div>{{ aud['curso_nombre']|e }}</div>
                  <div class="small text-muted">
                    Sec. {{ aud['seccion_nombre']|e }}
                  </div>
                </td>
                <td>
                  <div>{{ aud['semestre_nombre']|e }}</div>
                  {% if aud['es_semestre_cerrado'] %}
                    <span class="badge bg-secondary-subtle text-secondary border small" title="Modificado en semestre cerrado">
                      <i class="bi bi-lock-fill me-1"></i>Cerrado
                    </span>
                  {% endif %}
                </td>
                <td class="text-center">
                  {% if aud['accion'] == 'crear' %}
                    <span class="badge bg-success">Creación</span>
                  {% elseif aud['accion'] == 'editar' %}
                    <span class="badge bg-warning text-dark">Edición</span>
                  {% elseif aud['accion'] == 'eliminar' %}
                    <span class="badge bg-danger">Eliminación</span>
                  {% else %}
                    <span class="badge bg-secondary">{{ aud['accion']|e }}</span>
                  {% endif %}
                </td>
                <td class="text-center">
                  <span class="fw-semibold text-muted">{{ aud['nota_anterior']|e }}</span>
                </td>
                <td class="text-center">
                  <span class="fw-bold {% if aud['nota_nueva'] != '-' and aud['nota_nueva'] >= 61 %}text-success{% elseif aud['nota_nueva'] != '-' %}text-danger{% else %}text-muted{% endif %}">
                    {{ aud['nota_nueva']|e }}
                  </span>
                </td>
                <td class="text-center">
                  {% if aud['diferencia'] is not null %}
                    <span class="fw-bold {% if aud['diferencia'] > 0 %}text-success{% elseif aud['diferencia'] < 0 %}text-danger{% else %}text-muted{% endif %}">
                      {{ aud['diferencia_str']|e }}
                    </span>
                    {% if aud['es_cambio_grande'] %}
                      <span class="badge bg-danger-subtle text-danger border border-danger small d-block mt-1" title="Cambio significativo ≥ 20 puntos">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>&ge; 20 pts
                      </span>
                    {% endif %}
                  {% else %}
                    <span class="text-muted">&mdash;</span>
                  {% endif %}
                </td>
                <td class="pe-4 text-break small">
                  {{ aud['motivo']|e }}
                </td>
              </tr>
            {% endfor %}
          {% endif %}
        </tbody>
      </table>
    </div>
    {% if page is defined and page.last > 1 %}
      <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center py-2 px-3 gap-2">
        <span class="text-muted small">
          Mostrando {{ ((page.current - 1) * page.limit + 1)|e }} a {{ (page.current * page.limit > page.total_items ? page.total_items : (page.current * page.limit))|e }} de {{ page.total_items|e }} registros de auditoría
        </span>
        <nav aria-label="Paginación de auditoría">
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item {% if page.current <= 1 %}disabled{% endif %}">
              <a class="page-link" href="{{ url('auditoria?pagina=' ~ page.previous ~ (queryString ? '&' ~ queryString : ''))|e }}">&laquo; Anterior</a>
            </li>
            <li class="page-item active">
              <span class="page-link">Página {{ page.current|e }} de {{ page.last|e }}</span>
            </li>
            <li class="page-item {% if page.current >= page.last %}disabled{% endif %}">
              <a class="page-link" href="{{ url('auditoria?pagina=' ~ page.next ~ (queryString ? '&' ~ queryString : ''))|e }}">Siguiente &raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    {% endif %}
  </div>
</div>
