<div class="row justify-content-center mt-4">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white text-center py-3">
        <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Iniciar Sesión</h4>
        <small class="text-white-50">Control Académico y Escolar</small>
      </div>
      <div class="card-body p-4">
        {% if auth is defined and auth %}
          <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-info-circle me-1"></i> Sesión activa como <strong>{{ auth['usuario']|e }}</strong> ({{ auth['rol']|e }}). Ingrese credenciales para cambiar de usuario o <a href="{{ url('logout')|e }}" class="alert-link">Cerrar sesión</a>.
          </div>
        {% endif %}

        {% if errores is defined and errores|length > 0 %}
          <div class="alert alert-danger shadow-sm" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> No se pudo iniciar sesión:</div>
            <ul class="mb-0 ps-3">
              {% for err in errores %}
                <li>{{ err|e }}</li>
              {% endfor %}
            </ul>
          </div>
        {% endif %}

        <form method="post" action="{{ url('login')|e }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">

          <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input type="text"
                     name="usuario"
                     id="usuario"
                     class="form-control"
                     value="{{ old['usuario']|default('')|e }}"
                     required
                     autofocus>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password"
                     name="password"
                     id="password"
                     class="form-control"
                     required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar al Sistema
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
