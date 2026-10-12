<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h3 fw-bold mb-0 text-dark">
        <i class="{{ meta['icono']|e }} me-2"></i>Nueva {{ meta['singular']|e }}
      </h2>
      <a href="{{ url('catalogos/' ~ tipo) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
      </a>
    </div>

    {% if errores is not empty %}
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          {% for error in errores %}
            <li>{{ error|e }}</li>
          {% endfor %}
        </ul>
      </div>
    {% endif %}

    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="post" action="{{ url('catalogos/' ~ tipo ~ '/crear') }}">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">

          <div class="mb-3">
            <label for="nombre" class="form-label fw-semibold">
              {{ meta['campo']|e }} <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="nombre"
                   id="nombre"
                   class="form-control"
                   maxlength="{{ meta['maxLength']|e }}"
                   value="{{ old['nombre']|default('')|e }}"
                   placeholder="{{ meta['placeholder']|e }}"
                   autofocus
                   required>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ url('catalogos/' ~ tipo) }}" class="btn btn-light border">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Guardar {{ meta['singular']|e }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
