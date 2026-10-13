<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="{{ meta['icono']|e }} me-2"></i>Catálogo de {{ meta['plural']|e }}
    </h2>
    <p class="text-muted small mb-0">{{ meta['descripcion']|e }}</p>
  </div>
  <a href="{{ url('catalogos/' ~ tipo ~ '/nuevo')|e }}" class="btn btn-primary">
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
            <th class="text-end pe-4" style="width: 220px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          {% for item in items %}
            <tr>
              <td class="text-center text-muted fw-semibold">{{ item['id']|e }}</td>
              <td class="fw-semibold text-dark">
                {{ item['nombre']|e }}
                {% if tipo == 'semestres' %}
                  {% if item['cerrado'] %}
                    <span class="badge bg-danger ms-2"><i class="bi bi-lock-fill me-1"></i>Cerrado</span>
                  {% else %}
                    <span class="badge bg-success ms-2"><i class="bi bi-unlock-fill me-1"></i>Abierto</span>
                  {% endif %}
                {% endif %}
              </td>
              <td class="text-center">
                <span class="badge bg-secondary-subtle text-secondary border">
                  {{ item['conteo']|e }} {{ meta['relacionTexto']|e }}
                </span>
              </td>
              <td class="text-end pe-4">
                <div class="btn-group" role="group">
                  {% if tipo == 'semestres' %}
                    {% if item['cerrado'] %}
                      <form action="{{ url('catalogos/semestres/' ~ item['id'] ~ '/reabrir')|e }}" method="post" class="d-inline" onsubmit="return confirm('¿Reabrir este semestre?');">
                        <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">
                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Reabrir semestre">
                          <i class="bi bi-unlock"></i> Reabrir
                        </button>
                      </form>
                    {% else %}
                      <form action="{{ url('catalogos/semestres/' ~ item['id'] ~ '/cerrar')|e }}" method="post" class="d-inline" onsubmit="return confirm('¿Cerrar este semestre? Los profesores no podrán modificar notas.');">
                        <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Cerrar semestre">
                          <i class="bi bi-lock"></i> Cerrar
                        </button>
                      </form>
                    {% endif %}
                  {% endif %}
                  <a href="{{ url('catalogos/' ~ tipo ~ '/' ~ item['id'] ~ '/editar')|e }}" class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ url('catalogos/' ~ tipo ~ '/' ~ item['id'] ~ '/eliminar')|e }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este registro?');">
                    <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">
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
