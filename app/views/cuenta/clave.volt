<div class="row justify-content-center mt-3">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-dark text-white py-3">
        <h5 class="mb-0"><i class="bi bi-key-fill me-2"></i>Cambiar Contraseña</h5>
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

        <form method="post" action="{{ url('cuenta/clave') }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="mb-3">
            <label for="password_actual" class="form-label">Contraseña Actual</label>
            <input type="password"
                   name="password_actual"
                   id="password_actual"
                   class="form-control"
                   required
                   autofocus>
          </div>

          <div class="mb-3">
            <label for="password_nueva" class="form-label">Nueva Contraseña (mínimo 8 caracteres)</label>
            <input type="password"
                   name="password_nueva"
                   id="password_nueva"
                   class="form-control"
                   minlength="8"
                   required>
          </div>

          <div class="mb-4">
            <label for="password_confirmacion" class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password"
                   name="password_confirmacion"
                   id="password_confirmacion"
                   class="form-control"
                   minlength="8"
                   required>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Guardar Nueva Contraseña
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
