<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-file-earmark-check text-primary me-2"></i>Mis Calificaciones Oficiales
    </h2>
    <p class="text-muted small mb-0">Consulte el historial de materias cursadas y notas finales publicadas.</p>
  </div>
  <a href="{{ url('alumnos/' ~ alumno.id ~ '/ficha?formato=pdf')|e }}" target="_blank" class="btn btn-outline-danger">
    <i class="bi bi-file-earmark-pdf me-1"></i>Descargar Boleta Oficial (PDF)
  </a>
</div>

<!-- Resumen Estadístico -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="text-muted small fw-semibold">Cursos Evaluados</div>
      <div class="h3 fw-bold mb-0 text-dark">{{ totalNotas|e }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="text-muted small fw-semibold">Promedio General</div>
      <div class="h3 fw-bold mb-0 {% if promedio >= umbral %}text-success{% else %}text-danger{% endif %}">
        {{ promedio|e }}
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="text-muted small fw-semibold">Cursos Aprobados</div>
      <div class="h3 fw-bold mb-0 text-success">{{ aprobados|e }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="text-muted small fw-semibold">Cursos Reprobados</div>
      <div class="h3 fw-bold mb-0 text-danger">{{ reprobados|e }}</div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Curso</th>
            <th>Semestre</th>
            <th class="text-center">Sección</th>
            <th class="text-center">Nota Final</th>
            <th class="text-center">Resultado</th>
            <th class="pe-4 text-end">Estado</th>
          </tr>
        </thead>
        <tbody>
          {% if historial|length == 0 %}
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                No tiene calificaciones publicadas registradas en el sistema.
              </td>
            </tr>
          {% else %}
            {% for fila in historial %}
              <tr>
                <td class="ps-4">
                  <div class="fw-semibold text-dark">{{ fila['curso']|e }}</div>
                </td>
                <td>
                  <div>{{ fila['semestre']|e }}</div>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle text-secondary border">
                    {{ fila['seccion']|e }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="h6 mb-0 fw-bold {% if fila['nota'] >= umbral %}text-success{% else %}text-danger{% endif %}">
                    {{ fila['nota']|e }}
                  </span>
                </td>
                <td class="text-center">
                  {% if fila['nota'] >= umbral %}
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aprobado</span>
                  {% else %}
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Reprobado</span>
                  {% endif %}
                </td>
                <td class="pe-4 text-end">
                  <span class="badge bg-success-subtle text-success border">
                    <i class="bi bi-eye me-1"></i>Publicada
                  </span>
                </td>
              </tr>
            {% endfor %}
          {% endif %}
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer bg-light py-2 px-4 text-muted small">
    <i class="bi bi-info-circle me-1"></i>
    Nota mínima de aprobación: <strong>{{ umbral|e }} puntos</strong>. Las calificaciones en borrador no se muestran hasta que el docente titular las publique oficialmente.
  </div>
</div>
