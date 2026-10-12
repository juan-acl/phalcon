<div class="row justify-content-center">
  <div class="col-md-8 col-lg-7">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h3 fw-bold mb-0 text-dark">
        <i class="bi bi-journal-plus text-success me-2"></i>Ingresar Calificación
      </h2>
      <a href="{{ url('alumnos/' ~ alumno.id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver a la Ficha
      </a>
    </div>

    <!-- Resumen del alumno -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        {% if alumno.foto is not empty %}
          <img src="{{ url(alumno.foto) }}" alt="Foto" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
        {% else %}
          <div class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center border" style="width: 50px; height: 50px;">
            <i class="bi bi-person fs-4"></i>
          </div>
        {% endif %}
        <div>
          <h5 class="fw-bold mb-0 text-dark">{{ alumno.nombres|e }} {{ alumno.apellidos|e }}</h5>
          <span class="text-muted small">Carrera: {{ alumno.carrera ? alumno.carrera.nombre|e : 'N/A' }}</span>
        </div>
      </div>
    </div>

    {% if errores is not empty %}
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          {% for e in errores %}
            <li>{{ e|e }}</li>
          {% endfor %}
        </ul>
      </div>
    {% endif %}

    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="post" action="{{ url('alumnos/' ~ alumno.id ~ '/notas/crear') }}">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="mb-3">
            <label for="curso_id" class="form-label fw-semibold">Curso <span class="text-danger">*</span></label>
            <select name="curso_id" id="curso_id" class="form-select" required>
              <option value="">Selecciona el curso...</option>
              {% for c in cursos %}
                <option value="{{ c.id|e }}" {% if old['curso_id']|default(0) == c.id %}selected{% endif %}>
                  {{ c.nombre|e }}
                </option>
              {% endfor %}
            </select>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="semestre_id" class="form-label fw-semibold">Semestre <span class="text-danger">*</span></label>
              <select name="semestre_id" id="semestre_id" class="form-select" required>
                <option value="">Selecciona el semestre...</option>
                {% for s in semestres %}
                  <option value="{{ s.id|e }}" {% if old['semestre_id']|default(0) == s.id %}selected{% endif %}>
                    {{ s.nombre|e }}
                  </option>
                {% endfor %}
              </select>
            </div>

            <div class="col-md-6">
              <label for="seccion_id" class="form-label fw-semibold">Sección <span class="text-danger">*</span></label>
              <select name="seccion_id" id="seccion_id" class="form-select" required>
                <option value="">Selecciona la sección...</option>
                {% for sec in secciones %}
                  <option value="{{ sec.id|e }}" {% if old['seccion_id']|default(0) == sec.id %}selected{% endif %}>
                    {{ sec.nombre|e }}
                  </option>
                {% endfor %}
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label for="nota" class="form-label fw-semibold">Nota Obtenida (0 a 100) <span class="text-danger">*</span></label>
            <input type="number"
                   step="0.01"
                   min="0"
                   max="100"
                   name="nota"
                   id="nota"
                   class="form-control"
                   value="{{ old['nota']|default('')|e }}"
                   placeholder="Ej. 85.00"
                   required>
            <div class="form-text">La nota mínima de aprobación es de 61 puntos.</div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ url('alumnos/' ~ alumno.id) }}" class="btn btn-light border">Cancelar</a>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-lg me-1"></i>Guardar Calificación
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
