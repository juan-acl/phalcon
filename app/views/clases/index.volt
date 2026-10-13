<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-collection text-primary me-2"></i>Clases Académicas</h2>
    <p class="text-muted small mb-0">Asignación de cursos, secciones, semestres y docentes titulares</p>
  </div>
  <a href="{{ url('clases/nueva') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva Clase
  </a>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Curso</th>
          <th class="text-center">Sección</th>
          <th>Semestre</th>
          <th>Docente Titular</th>
          <th class="text-center">Inscritos</th>
          <th class="text-end pe-4">Acciones</th>
        </tr>
      </thead>
      <tbody>
        {% for c in clases %}
          <tr>
            <td class="text-muted small">#{{ c.id|e }}</td>
            <td class="fw-semibold text-dark">{{ c.curso ? c.curso.nombre|e : 'N/A' }}</td>
            <td class="text-center"><span class="badge bg-light text-dark border">{{ c.seccion ? c.seccion.nombre|e : 'N/A' }}</span></td>
            <td>
              {{ c.semestre ? c.semestre.nombre|e : 'N/A' }}
              {% if c.semestre and c.semestre.cerrado %}
                <span class="badge bg-danger ms-1">Cerrado</span>
              {% endif %}
            </td>
            <td>
              {% if c.profesor %}
                <i class="bi bi-person-badge text-primary me-1"></i>{{ c.profesor.getNombreCompleto()|e }}
              {% else %}
                <span class="text-danger small fst-italic">Sin profesor asignado</span>
              {% endif %}
            </td>
            <td class="text-center">
              <span class="badge bg-primary-subtle text-primary border">
                {{ c.inscripciones ? c.inscripciones|length|e : 0 }} alumnos
              </span>
            </td>
            <td class="text-end pe-4">
              <a href="{{ url('clases/' ~ c.id ~ '/editar') }}" class="btn btn-sm btn-outline-primary me-1" title="Asignar / Cambiar Docente">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ url('clases/' ~ c.id ~ '/eliminar') }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta clase?');">
                <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        {% else %}
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">
              No hay clases académicas registradas.
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>
  </div>
</div>
