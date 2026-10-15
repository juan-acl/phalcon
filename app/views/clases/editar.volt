<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Asignar Docente a Clase</h5>
      </div>
      <div class="card-body p-4">
        {% if errores is defined and errores|length > 0 %}
          <div class="alert alert-danger shadow-sm" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Por favor verifique los siguientes datos:</div>
            <ul class="mb-0 ps-3">
              {% for err in errores %}
                <li>{{ err|e }}</li>
              {% endfor %}
            </ul>
          </div>
        {% endif %}

        <div class="bg-light p-3 rounded mb-4 border">
          <div class="row g-2 text-muted small">
            <div class="col-12">
              <strong>Curso:</strong> <span class="text-dark">{{ clase.curso ? clase.curso.nombre|e : 'N/A' }}</span>
            </div>
            <div class="col-sm-6">
              <strong>Sección:</strong> <span class="text-dark">{{ clase.seccion ? clase.seccion.nombre|e : 'N/A' }}</span>
            </div>
            <div class="col-sm-6">
              <strong>Semestre:</strong> <span class="text-dark">{{ clase.semestre ? clase.semestre.nombre|e : 'N/A' }}</span>
            </div>
          </div>
        </div>

        <form method="post" action="{{ url('clases/' ~ clase.id ~ '/actualizar') }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="mb-4">
            <label for="profesor_id" class="form-label fw-semibold">Docente Titular</label>
            <select name="profesor_id" id="profesor_id" class="form-select">
              <option value="">-- Sin docente asignado --</option>
              {% for p in profesores %}
                <option value="{{ p.id|e }}" {% if old['profesor_id']|default(clase.profesor_id) == p.id %}selected{% endif %}>
                  {{ p.getNombreCompleto()|e }}
                </option>
              {% endfor %}
            </select>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('clases') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Actualizar Asignación
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
