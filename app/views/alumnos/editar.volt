<div class="row justify-content-center">
  <div class="col-md-8 col-lg-7">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h3 fw-bold mb-0 text-dark">
        <i class="bi bi-pencil-square text-primary me-2"></i>Editar Alumno
      </h2>
      <a href="{{ url('alumnos') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
      </a>
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
        <form method="post" action="{{ url('alumnos/' ~ alumno.id ~ '/actualizar') }}" enctype="multipart/form-data">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="row g-3">
            <div class="col-md-6">
              <label for="nombres" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
              <input type="text"
                     name="nombres"
                     id="nombres"
                     maxlength="100"
                     class="form-control"
                     value="{{ old['nombres']|default(alumno.nombres)|e }}"
                     required>
            </div>

            <div class="col-md-6">
              <label for="apellidos" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
              <input type="text"
                     name="apellidos"
                     id="apellidos"
                     maxlength="100"
                     class="form-control"
                     value="{{ old['apellidos']|default(alumno.apellidos)|e }}"
                     required>
            </div>

            <div class="col-md-6">
              <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
              <input type="date"
                     name="fecha_nacimiento"
                     id="fecha_nacimiento"
                     class="form-control"
                     value="{{ old['fecha_nacimiento']|default(alumno.fecha_nacimiento)|e }}"
                     required>
            </div>

            <div class="col-md-6">
              <label for="carrera_id" class="form-label fw-semibold">Carrera <span class="text-danger">*</span></label>
              <select name="carrera_id" id="carrera_id" class="form-select" required>
                {% for c in carreras %}
                  <option value="{{ c.id|e }}" {% if old['carrera_id']|default(alumno.carrera_id) == c.id %}selected{% endif %}>
                    {{ c.nombre|e }}
                  </option>
                {% endfor %}
              </select>
            </div>

            <div class="col-12">
              <label for="foto" class="form-label fw-semibold">Fotografía</label>
              {% if alumno.foto is not empty %}
                <div class="mb-2 d-flex align-items-center gap-3">
                  <img src="{{ url(alumno.foto) }}" alt="Foto actual" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                  <span class="text-muted small">Foto actual guardada. Sube una nueva para reemplazarla.</span>
                </div>
              {% endif %}
              <input type="file"
                     name="foto"
                     id="foto"
                     class="form-control"
                     accept="image/jpeg,image/png">
              <div class="form-text">Dejar vacío si no deseas cambiar la foto actual. Formatos: JPG, PNG (máx. 2 MB).</div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ url('alumnos') }}" class="btn btn-light border">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Actualizar Alumno
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
