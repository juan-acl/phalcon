<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="{{ meta['icono']|e }} me-2"></i>Catálogo de {{ meta['plural']|e }}
    </h2>
    <p class="text-muted small mb-0">{{ meta['descripcion']|e }}</p>
  </div>
  <a href="{{ url('catalogos/' ~ tipo ~ '/nuevo') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nueva {{ meta['singular']|e }}
  </a>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 80px;" class="text-center">#</th>
            <th>{{ meta['campo']|e }}</th>
            <th class="text-center" style="width: 200px;">{{ meta['etiquetaRelacion']|e }}</th>
            <th class="text-end pe-4" style="width: 180px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          {% for item in items %}
            <tr>
              <td class="text-center text-muted fw-semibold">{{ item['id']|e }}</td>
              <td class="fw-semibold text-dark">{{ item['nombre']|e }}</td>
              <td class="text-center">
                <span class="badge bg-secondary-subtle text-secondary border">
                  {{ item['conteo']|e }} {{ meta['relacionTexto']|e }}
                </span>
              </td>
              <td class="text-end pe-4">
                <div class="btn-group" role="group">
                  <a href="{{ url('catalogos/' ~ tipo ~ '/' ~ item['id'] ~ '/editar') }}" class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ url('catalogos/' ~ tipo ~ '/' ~ item['id'] ~ '/eliminar') }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este registro?');">
                    <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          {% else %}
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No hay {{ meta['plural']|lower|e }} registrados actualmente.
              </td>
            </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>
</div>
