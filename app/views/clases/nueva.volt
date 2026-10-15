<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Crear Nueva Clase</h5>
      </div>
      <div class="card-body p-4">
        {% if errores is defined and errores|length > 0 %}
          <div class="alert alert-danger shadow-sm" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Por favor verifique los siguientes datos requeridos:</div>
            <ul class="mb-0 ps-3">
              {% for err in errores %}
                <li>{{ err|e }}</li>
              {% endfor %}
            </ul>
          </div>
        {% endif %}

        <form method="post" action="{{ url('clases/crear') }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="mb-3">
            <label for="curso_id" class="form-label fw-semibold">Curso <span class="text-danger">*</span></label>
            <select name="curso_id" id="curso_id" class="form-select" required autofocus>
              <option value="">-- Seleccionar Curso --</option>
              {% for cu in cursos %}
                <option value="{{ cu.id|e }}" {% if old['curso_id']|default('') == cu.id %}selected{% endif %}>
                  {{ cu.nombre|e }}
                </option>
              {% endfor %}
            </select>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="seccion_id" class="form-label fw-semibold">Sección <span class="text-danger">*</span></label>
              <select name="seccion_id" id="seccion_id" class="form-select" required>
                <option value="">-- Seleccionar --</option>
                {% for sec in secciones %}
                  <option value="{{ sec.id|e }}" {% if old['seccion_id']|default('') == sec.id %}selected{% endif %}>
                    Sección {{ sec.nombre|e }}
                  </option>
                {% endfor %}
              </select>
            </div>
            <div class="col-sm-6">
              <label for="semestre_id" class="form-label fw-semibold">Semestre <span class="text-danger">*</span></label>
              <select name="semestre_id" id="semestre_id" class="form-select" required>
                <option value="">-- Seleccionar --</option>
                {% for sem in semestres %}
                  <option value="{{ sem.id|e }}" {% if old['semestre_id']|default('') == sem.id %}selected{% endif %}>
                    {{ sem.nombre|e }} {% if sem.cerrado %}(Cerrado){% endif %}
                  </option>
                {% endfor %}
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label for="profesor_id" class="form-label fw-semibold">Docente Titular (Opcional)</label>
            <select name="profesor_id" id="profesor_id" class="form-select">
              <option value="">-- Sin docente asignado --</option>
              {% for p in profesores %}
                <option value="{{ p.id|e }}" {% if old['profesor_id']|default('') == p.id %}selected{% endif %}>
                  {{ p.getNombreCompleto()|e }}
                </option>
              {% endfor %}
            </select>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('clases') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Guardar Clase
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
