<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-mortarboard-fill text-primary me-2"></i>Mis Clases Asignadas
    </h2>
    <p class="text-muted small mb-0">Gestione la nómina de estudiantes, capture calificaciones y publique los resultados.</p>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Curso</th>
            <th class="text-center">Sección</th>
            <th>Semestre</th>
            <th class="text-center">Inscritos</th>
            <th class="text-center">Calificaciones</th>
            <th class="text-center">Promedio</th>
            <th class="text-end pe-4">Acción</th>
          </tr>
        </thead>
        <tbody>
          {% if resumenClases|length == 0 %}
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                No tiene clases asignadas actualmente.
              </td>
            </tr>
          {% else %}
            {% for item in resumenClases %}
              {% set cl = item['clase'] %}
              <tr>
                <td class="ps-4">
                  <div class="fw-semibold text-dark">{{ cl.curso.nombre|default('Curso #' ~ cl.curso_id)|e }}</div>
                  <div class="small text-muted">Clase ID #{{ cl.id|e }}</div>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle text-secondary border">
                    {{ cl.seccion.nombre|default(cl.seccion_id)|e }}
                  </span>
                </td>
                <td>
                  <div>{{ cl.semestre.nombre|default(cl.semestre_id)|e }}</div>
                  {% if cl.semestre.cerrado %}
                    <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Cerrado</span>
                  {% else %}
                    <span class="badge bg-success"><i class="bi bi-unlock-fill me-1"></i>Abierto</span>
                  {% endif %}
                </td>
                <td class="text-center fw-semibold">
                  {{ item['totalInscritos']|e }}
                </td>
                <td class="text-center">
                  <span class="badge bg-light text-dark border">
                    {{ item['totalNotas']|e }} / {{ item['totalInscritos']|e }}
                  </span>
                  {% if item['publicadas'] > 0 %}
                    <span class="badge bg-success-subtle text-success border ms-1" title="Publicadas">
                      {{ item['publicadas']|e }} pub.
                    </span>
                  {% endif %}
                </td>
                <td class="text-center">
                  {% if item['totalNotas'] > 0 %}
                    <span class="fw-bold {% if item['promedio'] >= 61 %}text-success{% else %}text-danger{% endif %}">
                      {{ item['promedio']|e }}
                    </span>
                  {% else %}
                    <span class="text-muted">&mdash;</span>
                  {% endif %}
                </td>
                <td class="text-end pe-4">
                  <a href="{{ url('profesor/clases/' ~ cl.id)|e }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil-square me-1"></i>Calificar / Ver
                  </a>
                </td>
              </tr>
            {% endfor %}
          {% endif %}
        </tbody>
      </table>
    </div>
  </div>
</div>
