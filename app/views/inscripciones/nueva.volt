<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Inscribir Alumno en Clase</h5>
      </div>
      <div class="card-body p-4">
        {% if errores is defined and errores|length > 0 %}
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
              {% for err in errores %}
                <li>{{ err|e }}</li>
              {% endfor %}
            </ul>
          </div>
        {% endif %}

        <form method="post" action="{{ url('inscripciones/crear')|e }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">

          <div class="mb-3">
            <label for="clase_id" class="form-label fw-semibold">Clase <span class="text-danger">*</span></label>
            <select name="clase_id" id="clase_id" class="form-select" required autofocus>
              <option value="">-- Seleccionar Clase --</option>
              {% for cl in clases %}
                <option value="{{ cl.id|e }}" {% if old['clase_id']|default(claseIdDefecto)|default('') == cl.id %}selected{% endif %}>
                  {{ cl.curso.nombre|default('Curso #' ~ cl.curso_id)|e }} &mdash; Sec. {{ cl.seccion.nombre|default(cl.seccion_id)|e }} ({{ cl.semestre.nombre|default(cl.semestre_id)|e }})
                </option>
              {% endfor %}
            </select>
          </div>

          <div class="mb-4">
            <label for="alumno_id" class="form-label fw-semibold">Alumno <span class="text-danger">*</span></label>
            <select name="alumno_id" id="alumno_id" class="form-select" required>
              <option value="">-- Seleccionar Alumno --</option>
              {% for al in alumnos %}
                <option value="{{ al.id|e }}" {% if old['alumno_id']|default('') == al.id %}selected{% endif %}>
                  {{ al.carnet|default('')|e }} - {{ al.apellidos|e }}, {{ al.nombres|e }}
                </option>
              {% endfor %}
            </select>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ url('inscripciones')|e }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i>Inscribir Alumno
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
