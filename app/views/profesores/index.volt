<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-person-workspace text-primary me-2"></i>Gestión de Docentes</h2>
    <p class="text-muted small mb-0">Cuerpo docente y credenciales de acceso para profesores</p>
  </div>
  <a href="{{ url('profesores/nuevo') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nuevo Docente
  </a>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Nombre Completo</th>
          <th>Correo Electrónico</th>
          <th>Usuario del Sistema</th>
          <th class="text-center">Clases Asignadas</th>
          <th class="text-end pe-4">Acciones</th>
        </tr>
      </thead>
      <tbody>
        {% for p in profesores %}
          <tr>
            <td class="text-muted small">#{{ p.id|e }}</td>
            <td class="fw-semibold text-dark">{{ p.getNombreCompleto()|e }}</td>
            <td>{{ p.email|default('—')|e }}</td>
            <td>
              {% if p.usuario %}
                <span class="badge bg-secondary-subtle text-secondary border">
                  <i class="bi bi-person me-1"></i>{{ p.usuario.usuario|e }}
                </span>
              {% else %}
                <span class="text-muted small">Sin usuario</span>
              {% endif %}
            </td>
            <td class="text-center">
              <span class="badge bg-info-subtle text-info border">
                {{ p.clases ? p.clases|length|e : 0 }} clases
              </span>
            </td>
            <td class="text-end pe-4">
              <a href="{{ url('profesores/' ~ p.id ~ '/editar') }}" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ url('profesores/' ~ p.id ~ '/eliminar') }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar a este docente?');">
                <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        {% else %}
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">
              No hay docentes registrados en el sistema.
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>
  </div>
</div>
