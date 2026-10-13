<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark">
      <i class="bi bi-person-badge text-primary me-2"></i>Ficha del Alumno
    </h2>
    <p class="text-muted small mb-0">Información personal e historial académico de notas</p>
  </div>
  <div class="d-flex gap-2">
    {% if auth is defined and auth['rol'] == 'director' %}
      <a href="{{ url('alumnos') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
      </a>
    {% endif %}
    <a href="{{ url('alumnos/' ~ alumno.id ~ '/ficha?formato=pdf') }}" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-pdf me-1"></i>Descargar Ficha PDF
    </a>
    {% if auth is defined and (auth['rol'] == 'director' or auth['rol'] == 'profesor') %}
      <a href="{{ url('alumnos/' ~ alumno.id ~ '/notas/nueva') }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Ingresar Nota
      </a>
    {% endif %}
    {% if auth is defined and auth['rol'] == 'director' %}
      <a href="{{ url('alumnos/' ~ alumno.id ~ '/editar') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Editar Datos
      </a>
    {% endif %}
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm border-0 text-center p-3">
      <div class="card-body">
        {% if alumno.foto is not empty %}
          <img src="{{ url(alumno.foto) }}" alt="Fotografía" class="rounded-circle border shadow-sm mb-3" style="width: 140px; height: 140px; object-fit: cover;">
        {% else %}
          <div class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center border shadow-sm mb-3" style="width: 140px; height: 140px;">
            <i class="bi bi-person fs-1"></i>
          </div>
        {% endif %}
        <h4 class="fw-bold mb-1">{{ alumno.nombres|e }} {{ alumno.apellidos|e }}</h4>
        <p class="text-muted small mb-2">Expediente #{{ alumno.id|e }}</p>
        <span class="badge bg-primary-subtle text-primary border px-3 py-2">
          {{ alumno.carrera ? alumno.carrera.nombre|e : 'Sin carrera' }}
        </span>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title fw-bold mb-0 text-dark">Datos Personales</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <span class="text-muted small d-block">Nombres</span>
            <span class="fw-semibold">{{ alumno.nombres|e }}</span>
          </div>
          <div class="col-sm-6">
            <span class="text-muted small d-block">Apellidos</span>
            <span class="fw-semibold">{{ alumno.apellidos|e }}</span>
          </div>
          <div class="col-sm-6">
            <span class="text-muted small d-block">Fecha de Nacimiento</span>
            <span class="fw-semibold">{{ alumno.fecha_nacimiento|e }}</span>
          </div>
          <div class="col-sm-6">
            <span class="text-muted small d-block">Carrera</span>
            <span class="fw-semibold">{{ alumno.carrera ? alumno.carrera.nombre|e : 'N/A' }}</span>
          </div>
          <div class="col-sm-6">
            <span class="text-muted small d-block">Fecha de Registro</span>
            <span class="fw-semibold text-muted">{{ alumno.created_at|e }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
    <h5 class="card-title fw-bold mb-0 text-dark">
      <i class="bi bi-journal-check text-success me-2"></i>Historial de Cursos y Notas
    </h5>
    <span class="badge bg-light text-dark border">
      Umbral de Aprobación: {{ umbral|e }} pts
    </span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Semestre</th>
            <th>Curso</th>
            <th class="text-center">Sección</th>
            <th class="text-center">Nota</th>
            <th class="text-center">Estado</th>
            <th class="text-end pe-4">Acción</th>
          </tr>
        </thead>
        <tbody>
          {% for h in historial %}
            <tr>
              <td>{{ h['semestre']|e }}</td>
              <td class="fw-semibold text-dark">{{ h['curso']|e }}</td>
              <td class="text-center">{{ h['seccion']|e }}</td>
              <td class="text-center fw-bold">{{ h['nota']|e }}</td>
              <td class="text-center">
                {% if h['aprobado'] %}
                  <span class="badge bg-success-subtle text-success border">Aprobado</span>
                {% else %}
                  <span class="badge bg-danger-subtle text-danger border">Reprobado</span>
                {% endif %}
                {% if h['estado'] is defined and h['estado'] == 'borrador' %}
                  <span class="badge bg-warning text-dark border ms-1">Borrador</span>
                {% endif %}
                {% if h['updated_at'] is defined and h['updated_at'] %}
                  <span class="badge bg-info-subtle text-info border ms-1" title="Nota corregida con auditoría">Corregida</span>
                {% endif %}
              </td>
              <td class="text-end pe-4">
                {% if auth is defined and (auth['rol'] == 'director' or auth['rol'] == 'profesor') %}
                  <a href="{{ url('notas/' ~ h['id'] ~ '/editar') }}" class="btn btn-sm btn-outline-primary me-1" title="Editar Nota">
                    <i class="bi bi-pencil"></i>
                  </a>
                {% endif %}
                {% if auth is defined and auth['rol'] == 'director' %}
                  <form action="{{ url('notas/' ~ h['id'] ~ '/eliminar') }}" method="post" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta nota?');">
                    <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Nota">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                {% endif %}
                {% if auth is defined and auth['rol'] == 'alumno' %}
                  <span class="text-muted">—</span>
                {% endif %}
              </td>
            </tr>
          {% else %}
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">
                <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                El alumno aún no tiene notas registradas.
              </td>
            </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>
  </div>
</div>
