<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Datos del Docente</h5>
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

        <form method="post" action="{{ url('profesores/' ~ profesor.id ~ '/actualizar') }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="nombres" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
              <input type="text" name="nombres" id="nombres" class="form-control" value="{{ old['nombres']|default(profesor.nombres)|e }}" required autofocus>
            </div>
            <div class="col-sm-6">
              <label for="apellidos" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
              <input type="text" name="apellidos" id="apellidos" class="form-control" value="{{ old['apellidos']|default(profesor.apellidos)|e }}" required>
            </div>
          </div>

          <div class="mb-4">
            <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old['email']|default(profesor.email)|e }}">
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('profesores') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Actualizar Docente
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
