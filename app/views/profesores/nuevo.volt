<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Registrar Nuevo Docente</h5>
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

        <form method="post" action="{{ url('profesores/crear') }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="nombres" class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
              <input type="text" name="nombres" id="nombres" class="form-control" pattern="^[A-Za-zÁÉÍÓÚáéíóúñÑüÜ\s\'.\-]+$" title="Solo letras y espacios, sin números ni caracteres especiales" value="{{ old['nombres']|default('')|e }}" required autofocus>
            </div>
            <div class="col-sm-6">
              <label for="apellidos" class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
              <input type="text" name="apellidos" id="apellidos" class="form-control" pattern="^[A-Za-zÁÉÍÓÚáéíóúñÑüÜ\s\'.\-]+$" title="Solo letras y espacios, sin números ni caracteres especiales" value="{{ old['apellidos']|default('')|e }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old['email']|default('')|e }}" placeholder="docente@escuela.edu">
          </div>

          <hr class="my-4">
          <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock me-1"></i>Credenciales de Acceso al Sistema</h6>

          <div class="mb-3">
            <label for="usuario" class="form-label fw-semibold">Nombre de Usuario <span class="text-danger">*</span></label>
            <input type="text" name="usuario" id="usuario" class="form-control" pattern="^[A-Za-z0-9._-]+$" title="Solo letras, números, puntos y guiones sin espacios" value="{{ old['usuario']|default('')|e }}" required>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Contraseña Inicial <span class="text-danger">*</span></label>
            <input type="password" name="password" id="password" class="form-control" minlength="8" required>
            <div class="form-text">Mínimo 8 caracteres. El docente podrá cambiarla tras iniciar sesión.</div>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('profesores') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Registrar Docente
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
