<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-file-earmark-person text-primary me-2"></i>Reporte General de Alumnos por Carrera
    </h2>
    <p class="text-muted small mb-0">Listado consolidado de estudiantes matriculados</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ url('reportes/alumnos-por-carrera')|e }}?carrera_id={{ carreraId|default('')|e }}&ordenar_por={{ ordenarPor|default('apellidos')|e }}&direccion={{ direccion|default('ASC')|e }}&formato=pdf" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
    </a>
    <a href="{{ url('reportes/alumnos-por-carrera')|e }}?carrera_id={{ carreraId|default('')|e }}&ordenar_por={{ ordenarPor|default('apellidos')|e }}&direccion={{ direccion|default('ASC')|e }}&formato=csv" class="btn btn-outline-success btn-sm shadow-sm">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV
    </a>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-3">
    <form method="get" action="{{ url('reportes/alumnos-por-carrera')|e }}" class="row g-2 align-items-center">
      <div class="col-12 col-md-5">
        <label for="carrera_id" class="form-label small text-muted fw-semibold mb-1">Carrera</label>
        <select name="carrera_id" id="carrera_id" class="form-select">
          <option value="">Todas las carreras</option>
          {% for c in carreras %}
            <option value="{{ c.id|e }}" {% if carreraId == c.id %}selected{% endif %}>
              {{ c.nombre|e }}
            </option>
          {% endfor %}
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label for="ordenar_por" class="form-label small text-muted fw-semibold mb-1">Ordenar por</label>
        <select name="ordenar_por" id="ordenar_por" class="form-select">
          <option value="apellidos" {% if ordenarPor == 'apellidos' %}selected{% endif %}>Apellidos y Nombres</option>
          <option value="id" {% if ordenarPor == 'id' %}selected{% endif %}>No. Expediente (#)</option>
          <option value="carrera" {% if ordenarPor == 'carrera' %}selected{% endif %}>Carrera</option>
          <option value="fecha_nacimiento" {% if ordenarPor == 'fecha_nacimiento' %}selected{% endif %}>Fecha de Nacimiento</option>
        </select>
      </div>

      <div class="col-6 col-md-2">
        <label for="direccion" class="form-label small text-muted fw-semibold mb-1">Sentido</label>
        <select name="direccion" id="direccion" class="form-select">
          <option value="ASC" {% if direccion == 'ASC' %}selected{% endif %}>Ascendente (A-Z / 1-9)</option>
          <option value="DESC" {% if direccion == 'DESC' %}selected{% endif %}>Descendente (Z-A / 9-1)</option>
        </select>
      </div>

      <div class="col-12 col-md-2 d-flex gap-2 align-self-end">
        <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
        <a href="{{ url('reportes/alumnos-por-carrera')|e }}" class="btn btn-outline-secondary" title="Restablecer">Limpiar</a>
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
            <th style="width: 80px;" class="text-center">
              <a href="{{ url('reportes/alumnos-por-carrera')|e }}?carrera_id={{ carreraId|default('')|e }}&ordenar_por=id&direccion={% if ordenarPor == 'id' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                #
                {% if ordenarPor == 'id' %}
                  <i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>
                {% endif %}
              </a>
            </th>
            <th>
              <a href="{{ url('reportes/alumnos-por-carrera')|e }}?carrera_id={{ carreraId|default('')|e }}&ordenar_por=carrera&direccion={% if ordenarPor == 'carrera' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                Carrera
                {% if ordenarPor == 'carrera' %}
                  <i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>
                {% endif %}
              </a>
            </th>
            <th>
              <a href="{{ url('reportes/alumnos-por-carrera')|e }}?carrera_id={{ carreraId|default('')|e }}&ordenar_por=apellidos&direccion={% if ordenarPor == 'apellidos' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                Apellidos y Nombres
                {% if ordenarPor == 'apellidos' %}
                  <i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>
                {% endif %}
              </a>
            </th>
            <th class="text-center">
              <a href="{{ url('reportes/alumnos-por-carrera')|e }}?carrera_id={{ carreraId|default('')|e }}&ordenar_por=fecha_nacimiento&direccion={% if ordenarPor == 'fecha_nacimiento' and direccion == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                Fecha de Nacimiento
                {% if ordenarPor == 'fecha_nacimiento' %}
                  <i class="bi bi-arrow-{% if direccion == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>
                {% endif %}
              </a>
            </th>
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
                <a href="{{ url('alumnos/' ~ a['id'])|e }}" class="btn btn-sm btn-outline-info">
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
