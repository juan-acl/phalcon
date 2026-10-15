<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-search text-primary me-2"></i>Buscar Alumno para Ingreso de Notas
    </h2>
    <p class="text-muted small mb-0">
      {% if auth is defined and auth['rol'] == 'profesor' %}
        Selecciona a un estudiante de tus clases asignadas para registrar sus calificaciones
      {% else %}
        Selecciona al alumno para consultar su historial o registrar nuevas calificaciones
      {% endif %}
    </p>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-3">
    <form method="get" action="{{ url('notas/buscar')|e }}" class="row g-2 align-items-center">
      <div class="col-12 col-md-10">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text"
                 name="q"
                 class="form-control"
                 placeholder="Escribe el nombre o apellido del alumno..."
                 value="{{ q|e }}"
                 autofocus>
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
            <th>Alumno</th>
            <th>Carrera</th>
            <th class="text-end pe-4" style="width: 220px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          {% for a in alumnos %}
            <tr>
              <td class="text-center">
                {% if a.foto is not empty %}
                  <img src="{{ url(a.foto)|e }}" alt="Foto" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                {% else %}
                  <div class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                    <i class="bi bi-person"></i>
                  </div>
                {% endif %}
              </td>
              <td>
                <span class="fw-semibold text-dark">{{ a.apellidos|e }}, {{ a.nombres|e }}</span>
                <span class="text-muted small d-block">ID #{{ a.id|e }}</span>
              </td>
              <td>
                <span class="badge bg-light text-dark border">
                  {{ a.carrera ? a.carrera.nombre|e : 'Sin carrera' }}
                </span>
              </td>
              <td class="text-end pe-4">
                <a href="{{ url('alumnos/' ~ a.id ~ '/notas/nueva')|e }}" class="btn btn-sm btn-success">
                  <i class="bi bi-plus-circle me-1"></i>Ingresar Nota
                </a>
                <a href="{{ url('alumnos/' ~ a.id)|e }}" class="btn btn-sm btn-outline-info">
                  <i class="bi bi-eye me-1"></i>Ver Ficha
                </a>
              </td>
            </tr>
          {% else %}
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">
                <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                No se encontraron alumnos con el criterio de búsqueda.
              </td>
            </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>
</div>
