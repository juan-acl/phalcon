<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-file-earmark-person text-primary me-2"></i>Reporte General de Alumnos por Carrera
    </h2>
    <p class="text-muted small mb-0">Listado consolidado de estudiantes matriculados</p>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-3">
    <form method="get" action="{{ url('reportes/alumnos-por-carrera') }}" class="row g-2 align-items-center">
      <div class="col-md-9">
        <select name="carrera_id" class="form-select">
          <option value="">Todas las carreras</option>
          {% for c in carreras %}
            <option value="{{ c.id|e }}" {% if carreraId == c.id %}selected{% endif %}>
              {{ c.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
        <a href="{{ url('reportes/alumnos-por-carrera') }}" class="btn btn-outline-secondary">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
    <h5 class="card-title fw-bold mb-0 text-dark">Resultados</h5>
    <span class="badge bg-secondary-subtle text-secondary border">
      Total: {{ alumnos|length|e }} alumnos
    </span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 80px;" class="text-center">#</th>
            <th>Carrera</th>
            <th>Apellidos y Nombres</th>
            <th class="text-center">Fecha de Nacimiento</th>
            <th class="text-end pe-4">Acción</th>
          </tr>
        </thead>
        <tbody>
          {% for a in alumnos %}
            <tr>
              <td class="text-center text-muted fw-semibold">{{ a['id']|e }}</td>
              <td>
                <span class="badge bg-light text-dark border">{{ a['carrera']|e }}</span>
              </td>
              <td class="fw-semibold text-dark">
                {{ a['apellidos']|e }}, {{ a['nombres']|e }}
              </td>
              <td class="text-center text-muted small">{{ a['fecha_nacimiento']|e }}</td>
              <td class="text-end pe-4">
                <a href="{{ url('alumnos/' ~ a['id']) }}" class="btn btn-sm btn-outline-info">
                  <i class="bi bi-eye me-1"></i>Ver Ficha
                </a>
              </td>
            </tr>
          {% else %}
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No se encontraron alumnos para los criterios seleccionados.
              </td>
            </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>
</div>
