<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="mb-1">
      <a href="{{ url('profesor/clases')|e }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i>Volver a mis clases
      </a>
    </div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      {{ clase.curso.nombre|default('Curso #' ~ clase.curso_id)|e }}
    </h2>
    <div class="text-muted small">
      Sección {{ clase.seccion.nombre|default(clase.seccion_id)|e }} &bull;
      {{ clase.semestre.nombre|default(clase.semestre_id)|e }}
      {% if clase.semestre.cerrado %}
        <span class="badge bg-danger ms-2"><i class="bi bi-lock-fill me-1"></i>Semestre Cerrado</span>
      {% else %}
        <span class="badge bg-success ms-2"><i class="bi bi-unlock-fill me-1"></i>Semestre Abierto</span>
      {% endif %}
    </div>
  </div>
  <div class="d-flex gap-2">
    {% if borradores > 0 and not clase.semestre.cerrado %}
      <form action="{{ url('profesor/clases/' ~ clase.id ~ '/publicar')|e }}" method="post" onsubmit="return confirm('¿Confirma que desea publicar las calificaciones? Los alumnos podrán visualizarlas en sus perfiles.');">
        <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-send-check me-1"></i>Publicar {{ borradores|e }} Calificaciones
        </button>
      </form>
    {% endif %}
  </div>
</div>

<!-- Tarjetas de Resumen / Estadísticas -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="text-muted small fw-semibold">Inscritos</div>
      <div class="h3 fw-bold mb-0 text-dark">{{ totalInscritos|e }}</div>
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
      <div class="text-muted small fw-semibold">Aprobados (&ge; {{ umbral|e }})</div>
      <div class="h3 fw-bold mb-0 text-success">{{ aprobados|e }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="text-muted small fw-semibold">Reprobados (&lt; {{ umbral|e }})</div>
      <div class="h3 fw-bold mb-0 text-danger">{{ reprobados|e }}</div>
    </div>
  </div>
</div>

{% if borradores > 0 %}
  <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
    <div>
      <i class="bi bi-info-circle-fill fs-5 me-2 align-middle"></i>
      <span>Tiene <strong>{{ borradores|e }}</strong> calificación(es) en <strong>borrador</strong>. No son visibles para los alumnos hasta ser publicadas.</span>
    </div>
    {% if not clase.semestre.cerrado %}
      <form action="{{ url('profesor/clases/' ~ clase.id ~ '/publicar')|e }}" method="post" onsubmit="return confirm('¿Publicar calificaciones ahora?');" class="ms-3">
        <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">
        <button type="submit" class="btn btn-sm btn-info text-white fw-semibold">
          <i class="bi bi-send-check me-1"></i>Publicar Ahora
        </button>
      </form>
    {% endif %}
  </div>
{% endif %}

{% if clase.semestre.cerrado %}
  <div class="alert alert-warning mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Periodo Cerrado:</strong> Este semestre se encuentra cerrado. El formulario de captura está deshabilitado. Solo la Dirección puede realizar modificaciones.
  </div>
{% endif %}

<!-- Formulario de Calificaciones -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-white py-3 border-0">
    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-people me-2"></i>Nómina de Alumnos y Calificaciones</h5>
  </div>
  <div class="card-body p-0">
    <form action="{{ url('profesor/clases/' ~ clase.id ~ '/notas')|e }}" method="post">
      <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4" style="width: 140px;">Carnet</th>
              <th>Estudiante</th>
              <th>Carrera</th>
              <th style="width: 180px;">Nota (0 - 100)</th>
              <th class="text-center" style="width: 140px;">Estado</th>
              <th class="text-end pe-4" style="width: 160px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {% if filas|length == 0 %}
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-people fs-1 d-block mb-2"></i>
                  No hay alumnos inscritos en esta clase actualmente.
                </td>
              </tr>
            {% else %}
              {% for fila in filas %}
                {% set al = fila['alumno'] %}
                {% set nt = fila['nota'] %}
                <tr>
                  <td class="ps-4 font-monospace text-muted">{{ al.carnet|default('')|e }}</td>
                  <td>
                    <div class="fw-semibold text-dark">{{ al.apellidos|e }}, {{ al.nombres|e }}</div>
                  </td>
                  <td>
                    <span class="small text-muted">{{ al.carrera.nombre|default('')|e }}</span>
                  </td>
                  <td>
                    {% if nt and nt.estado == 'publicada' %}
                      <div class="input-group input-group-sm">
                        <input type="text" class="form-control fw-bold {% if nt.nota >= umbral %}text-success{% else %}text-danger{% endif %}" value="{{ nt.nota|e }}" readonly disabled>
                        <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                      </div>
                    {% else %}
                      <input type="number" step="0.01" min="0" max="100" name="notas[{{ al.id|e }}]" value="{% if nt %}{{ nt.nota|e }}{% endif %}" class="form-control form-control-sm" placeholder="Sin nota" {% if clase.semestre.cerrado %}disabled{% endif %}>
                    {% endif %}
                  </td>
                  <td class="text-center">
                    {% if nt %}
                      {% if nt.estado == 'publicada' %}
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Publicada</span>
                      {% else %}
                        <span class="badge bg-secondary"><i class="bi bi-pencil me-1"></i>Borrador</span>
                      {% endif %}
                    {% else %}
                      <span class="badge bg-light text-muted border">Pendiente</span>
                    {% endif %}
                  </td>
                  <td class="text-end pe-4">
                    {% if nt and nt.estado == 'publicada' %}
                      {% if not clase.semestre.cerrado %}
                        <a href="{{ url('notas/' ~ nt.id ~ '/editar')|e }}" class="btn btn-sm btn-outline-warning" title="Corregir calificación publicada (requiere motivo)">
                          <i class="bi bi-pencil-square me-1"></i>Corregir
                        </a>
                      {% else %}
                        <span class="text-muted small fst-italic">Cerrada</span>
                      {% endif %}
                    {% else %}
                      <span class="text-muted small">&mdash;</span>
                    {% endif %}
                  </td>
                </tr>
              {% endfor %}
            {% endif %}
          </tbody>
        </table>
      </div>

      {% if filas|length > 0 and not clase.semestre.cerrado %}
        <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
          <span class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>Las notas ingresadas se guardarán como borrador.
          </span>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Guardar Borradores
          </button>
        </div>
      {% endif %}
    </form>
  </div>
</div>
