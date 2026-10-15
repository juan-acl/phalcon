<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-people text-primary me-2"></i>Gestión de Alumnos
    </h2>
    <p class="text-muted small mb-0">Listado, búsqueda y control de expedientes de bachillerato</p>
  </div>
  <a href="{{ url('alumnos/nuevo')|e }}" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i>Nuevo Alumno
  </a>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-3">
    <form method="get" action="{{ url('alumnos')|e }}" class="row g-2 align-items-center">
      <div class="col-12 col-md-10">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text"
                 name="q"
                 class="form-control"
                 placeholder="Buscar por nombres o apellidos..."
                 value="{{ q|e }}">
        </div>
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button type="submit" class="btn btn-dark">Buscar</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;" class="text-center">Foto</th>
            <th>
              <a href="{{ url('alumnos')|e }}?q={{ q|e }}&campo=apellidos&dir={% if campo == 'apellidos' and dir == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Apellidos y Nombres
                {% if campo == 'apellidos' %}<i class="bi bi-arrow-{% if dir == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th>
              <a href="{{ url('alumnos')|e }}?q={{ q|e }}&campo=carrera&dir={% if campo == 'carrera' and dir == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Carrera
                {% if campo == 'carrera' %}<i class="bi bi-arrow-{% if dir == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th class="text-center">
              <a href="{{ url('alumnos')|e }}?q={{ q|e }}&campo=fecha_nacimiento&dir={% if campo == 'fecha_nacimiento' and dir == 'ASC' %}DESC{% else %}ASC{% endif %}" class="text-dark text-decoration-none">
                Fecha de Nacimiento
                {% if campo == 'fecha_nacimiento' %}<i class="bi bi-arrow-{% if dir == 'ASC' %}up{% else %}down{% endif %} ms-1 text-primary"></i>{% endif %}
              </a>
            </th>
            <th class="text-end pe-4" style="width: 220px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          {% for a in alumnos %}
            <tr>
              <td class="text-center">
                {% if a.foto is not empty %}
                  <img src="{{ url(a.foto)|e }}" alt="Foto" class="rounded-circle border" style="width: 45px; height: 45px; object-fit: cover;">
                {% else %}
                  <div class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center border" style="width: 45px; height: 45px;">
                    <i class="bi bi-person fs-5"></i>
                  </div>
                {% endif %}
              </td>
              <td>
                <a href="{{ url('alumnos/' ~ a.id)|e }}" class="fw-semibold text-dark text-decoration-none">
                  {{ a.apellidos|e }}, {{ a.nombres|e }}
                </a>
              </td>
              <td>
                <span class="badge bg-light text-dark border">
                  {{ a.carrera ? a.carrera.nombre|e : 'Sin carrera' }}
                </span>
              </td>
              <td class="text-center text-muted small">
                {{ a.fecha_nacimiento|e }}
              </td>
              <td class="text-end pe-4">
                <div class="btn-group" role="group">
                  <a href="{{ url('alumnos/' ~ a.id)|e }}" class="btn btn-sm btn-outline-info" title="Ver Ficha">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="{{ url('alumnos/' ~ a.id ~ '/notas/nueva')|e }}" class="btn btn-sm btn-outline-success" title="Ingresar Nota">
                    <i class="bi bi-plus-circle"></i>
                  </a>
                  <a href="{{ url('alumnos/' ~ a.id ~ '/editar')|e }}" class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ url('alumnos/' ~ a.id ~ '/eliminar')|e }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este alumno?');">
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
              <td colspan="5" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No se encontraron alumnos registrados.
              </td>
            </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>
</div>
