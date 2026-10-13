<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h3 fw-bold mb-1 text-dark"><i class="bi bi-speedometer2 text-primary me-2"></i>Tablero de Control Institucional</h2>
    <p class="text-muted small mb-0">Resumen ejecutivo del sistema escolar y rendimiento académico</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 border-start border-primary border-4">
      <div class="card-body">
        <span class="text-muted small text-uppercase fw-bold">Estudiantes Registrados</span>
        <h3 class="fw-bold mb-0 text-dark">{{ totalAlumnos|e }}</h3>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 border-start border-success border-4">
      <div class="card-body">
        <span class="text-muted small text-uppercase fw-bold">Docentes</span>
        <h3 class="fw-bold mb-0 text-dark">{{ totalProfesores|e }}</h3>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 border-start border-warning border-4">
      <div class="card-body">
        <span class="text-muted small text-uppercase fw-bold">Clases Activas</span>
        <h3 class="fw-bold mb-0 text-dark">{{ totalClases|e }}</h3>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 border-start border-info border-4">
      <div class="card-body">
        <span class="text-muted small text-uppercase fw-bold">Cursos en Catálogo</span>
        <h3 class="fw-bold mb-0 text-dark">{{ totalCursos|e }}</h3>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line text-primary me-2"></i>Rendimiento Académico General</h6>
      </div>
      <div class="card-body p-4">
        <div class="row text-center">
          <div class="col-6 border-end">
            <span class="text-muted small d-block mb-1">Promedio General</span>
            <span class="display-6 fw-bold text-primary">{{ promedioGeneral|e }}</span>
            <span class="text-muted small d-block">puntos</span>
          </div>
          <div class="col-6">
            <span class="text-muted small d-block mb-1">Tasa de Aprobación</span>
            <span class="display-6 fw-bold text-success">{{ porcentajeAprobacion|e }}%</span>
            <span class="text-muted small d-block">notas &ge; 61 pts</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-link-45deg text-primary me-2"></i>Accesos Rápidos de Dirección</h6>
      </div>
      <div class="card-body p-3">
        <div class="list-group list-group-flush">
          <a href="{{ url('profesores') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-person-workspace text-primary me-2"></i>Gestión de Docentes</span>
            <i class="bi bi-chevron-right text-muted small"></i>
          </a>
          <a href="{{ url('clases') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-collection text-success me-2"></i>Asignación de Clases y Profesores</span>
            <i class="bi bi-chevron-right text-muted small"></i>
          </a>
          <a href="{{ url('inscripciones') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-person-check text-warning me-2"></i>Inscripciones de Alumnos en Clases</span>
            <i class="bi bi-chevron-right text-muted small"></i>
          </a>
          <a href="{{ url('auditoria') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history text-danger me-2"></i>Bitácora de Auditoría de Notas</span>
            <i class="bi bi-chevron-right text-muted small"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-success me-2"></i>Últimos Movimientos de Auditoría</h6>
    <a href="{{ url('auditoria') }}" class="btn btn-sm btn-outline-primary">Ver Bitácora Completa</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0 small">
      <thead class="table-light">
        <tr>
          <th>Fecha/Hora</th>
          <th>Acción</th>
          <th>Estudiante</th>
          <th>Curso</th>
          <th class="text-center">Ant.</th>
          <th class="text-center">Nueva</th>
          <th>Motivo</th>
          <th>Usuario</th>
        </tr>
      </thead>
      <tbody>
        {% for a in ultimasAuditorias %}
          <tr>
            <td>{{ a.created_at|e }}</td>
            <td>
              <span class="badge {% if a.accion == 'crear' %}bg-success{% elseif a.accion == 'editar' %}bg-warning text-dark{% else %}bg-danger{% endif %}">
                {{ a.accion|e }}
              </span>
            </td>
            <td>{{ a.alumno ? a.alumno.getNombreCompleto()|e : 'ID ' ~ a.alumno_id|e }}</td>
            <td>{{ a.curso ? a.curso.nombre|e : 'ID ' ~ a.curso_id|e }}</td>
            <td class="text-center">{{ a.nota_anterior|default('—')|e }}</td>
            <td class="text-center fw-bold">{{ a.nota_nueva|default('—')|e }}</td>
            <td>{{ a.motivo|default('—')|e }}</td>
            <td>{{ a.usuario ? a.usuario.usuario|e : 'Sistema'|e }}</td>
          </tr>
        {% else %}
          <tr>
            <td colspan="8" class="text-center py-3 text-muted">No hay movimientos recientes en la auditoría.</td>
          </tr>
        {% endfor %}
      </tbody>
    </table>
  </div>
</div>
