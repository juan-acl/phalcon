<div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-light border">
  <div class="col-lg-8 px-0">
    <h1 class="display-6 fw-bold">Sistema de Alumnos y Notas</h1>
    <p class="lead my-3">Gestión académica de bachillerato, cursos aprobados, calificaciones y catálogos escolares.</p>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-award text-primary me-2"></i>Carreras</h5>
        <p class="card-text text-muted small">Catálogo de carreras de bachillerato disponibles.</p>
        <a href="{{ url('catalogos/carreras') }}" class="btn btn-sm btn-outline-primary">Ver Carreras</a>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-book text-success me-2"></i>Cursos</h5>
        <p class="card-text text-muted small">Catálogo de cursos o asignaturas académicas.</p>
        <a href="{{ url('catalogos/cursos') }}" class="btn btn-sm btn-outline-success">Ver Cursos</a>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-grid-3x3-gap text-warning me-2"></i>Secciones</h5>
        <p class="card-text text-muted small">Catálogo de secciones escolares (A, B, C).</p>
        <a href="{{ url('catalogos/secciones') }}" class="btn btn-sm btn-outline-warning">Ver Secciones</a>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title"><i class="bi bi-calendar3 text-info me-2"></i>Semestres</h5>
        <p class="card-text text-muted small">Catálogo de ciclos o periodos académicos.</p>
        <a href="{{ url('catalogos/semestres') }}" class="btn btn-sm btn-outline-info">Ver Semestres</a>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5><i class="bi bi-people text-dark me-2"></i>Alumnos</h5>
        <p class="text-muted small">Registro, búsqueda y fichas de estudiantes.</p>
        <a href="{{ url('alumnos') }}" class="btn btn-primary btn-sm">Módulo Alumnos</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5><i class="bi bi-journal-check text-dark me-2"></i>Notas</h5>
        <p class="text-muted small">Búsqueda de alumno e ingreso de calificaciones.</p>
        <a href="{{ url('notas/buscar') }}" class="btn btn-primary btn-sm">Ingreso de Notas</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5><i class="bi bi-bar-chart-line text-dark me-2"></i>Reportes</h5>
        <p class="text-muted small">Reporte por carrera y notas por curso y sección.</p>
        <a href="{{ url('reportes/alumnos-por-carrera') }}" class="btn btn-primary btn-sm">Ver Reportes</a>
      </div>
    </div>
  </div>
</div>
