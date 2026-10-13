<div class="row g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm border-0 text-center p-4">
      <div class="mb-3">
        {% if alumno.foto %}
          <img src="{{ url(alumno.foto)|e }}" alt="Foto del estudiante" class="rounded-circle img-thumbnail shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
        {% else %}
          <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 140px; height: 140px; font-size: 3rem;">
            {{ alumno.nombres|slice(0, 1)|e }}{{ alumno.apellidos|slice(0, 1)|e }}
          </div>
        {% endif %}
      </div>

      <h3 class="h4 fw-bold mb-1 text-dark">{{ alumno.nombres|e }} {{ alumno.apellidos|e }}</h3>
      <div class="badge bg-primary-subtle text-primary border fs-6 mb-3">
        Carnet: {{ alumno.carnet|default('Pendiente')|e }}
      </div>

      <div class="text-start border-top pt-3 mt-2">
        <div class="mb-2">
          <span class="text-muted small d-block">Carrera</span>
          <span class="fw-semibold text-dark">{{ alumno.carrera.nombre|default('No asignada')|e }}</span>
        </div>
        <div class="mb-2">
          <span class="text-muted small d-block">Fecha de Nacimiento</span>
          <span class="fw-semibold text-dark">{{ alumno.fecha_nacimiento|e }}</span>
        </div>
      </div>

      <div class="d-grid gap-2 mt-4">
        <a href="{{ url('mi/notas')|e }}" class="btn btn-primary">
          <i class="bi bi-file-earmark-check me-1"></i>Ver Mis Calificaciones
        </a>
        <a href="{{ url('alumnos/' ~ alumno.id ~ '/ficha?formato=pdf')|e }}" target="_blank" class="btn btn-outline-danger">
          <i class="bi bi-file-earmark-pdf me-1"></i>Descargar Ficha en PDF
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title mb-0 fw-bold">
          <i class="bi bi-collection me-2"></i>Mis Clases Inscritas
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Curso</th>
                <th class="text-center">Sección</th>
                <th>Semestre</th>
                <th>Docente</th>
              </tr>
            </thead>
            <tbody>
              {% if inscripciones|length == 0 %}
                <tr>
                  <td colspan="4" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    No se encuentra inscrito en ninguna clase activa.
                  </td>
                </tr>
              {% else %}
                {% for insc in inscripciones %}
                  {% set cl = insc.clase %}
                  <tr>
                    <td class="ps-4">
                      <div class="fw-semibold text-dark">{{ cl.curso.nombre|default('Curso #' ~ cl.curso_id)|e }}</div>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-secondary-subtle text-secondary border">
                        {{ cl.seccion.nombre|default(cl.seccion_id)|e }}
                      </span>
                    </td>
                    <td>
                      <div>{{ cl.semestre.nombre|default(cl.semestre_id)|e }}</div>
                    </td>
                    <td>
                      {% if cl.profesor %}
                        <span>{{ cl.profesor.nombres|e }} {{ cl.profesor.apellidos|e }}</span>
                      {% else %}
                        <span class="text-muted fst-italic">Por asignar</span>
                      {% endif %}
                    </td>
                  </tr>
                {% endfor %}
              {% endif %}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
