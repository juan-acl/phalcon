<div class="row justify-content-center">
  <div class="col-md-8 col-lg-7">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h3 fw-bold mb-0 text-dark">
        <i class="bi bi-person-plus text-primary me-2"></i>Nuevo Alumno
      </h2>
      <a href="{{ url('alumnos') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
      </a>
    </div>

    {% if errores is not empty %}
      <div class="alert alert-danger mb-3 shadow-sm" role="alert">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Por favor verifique los siguientes datos requeridos:</div>
        <ul class="mb-0 ps-3">
          {% for e in errores %}
            <li>{{ e|e }}</li>
          {% endfor %}
        </ul>
      </div>
    {% endif %}

    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="post" action="{{ url('alumnos/crear') }}" enctype="multipart/form-data">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="row g-3">
            <div class="col-md-6">
              <label for="nombres" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
              <input type="text"
                     name="nombres"
                     id="nombres"
                     maxlength="100"
                     class="form-control"
                     value="{{ old['nombres']|default('')|e }}"
                     placeholder="Ej. Juan Carlos"
                     required
                     autofocus>
            </div>

            <div class="col-md-6">
              <label for="apellidos" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
              <input type="text"
                     name="apellidos"
                     id="apellidos"
                     maxlength="100"
                     class="form-control"
                     value="{{ old['apellidos']|default('')|e }}"
                     placeholder="Ej. Pérez Gómez"
                     required>
            </div>

            <div class="col-md-6">
              <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
              <input type="date"
                     name="fecha_nacimiento"
                     id="fecha_nacimiento"
                     class="form-control"
                     value="{{ old['fecha_nacimiento']|default('')|e }}"
                     required>
            </div>

            <div class="col-md-6">
              <label for="carrera_id" class="form-label fw-semibold">Carrera <span class="text-danger">*</span></label>
              <select name="carrera_id" id="carrera_id" class="form-select" required>
                <option value="">Selecciona una carrera...</option>
                {% for c in carreras %}
                  <option value="{{ c.id|e }}" {% if old['carrera_id']|default(0) == c.id %}selected{% endif %}>
                    {{ c.nombre|e }}
                  </option>
                {% endfor %}
              </select>
            </div>

            <div class="col-12">
              <label for="foto" class="form-label fw-semibold">Fotografía del Alumno (JPG o PNG, máx. 2 MB)</label>
              <input type="file"
                     name="foto"
                     id="foto"
                     class="form-control"
                     accept="image/jpeg,image/png">
              <div class="form-text">Opcional. Formatos permitidos: JPG, PNG.</div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ url('alumnos') }}" class="btn btn-light border">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Guardar Alumno
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
