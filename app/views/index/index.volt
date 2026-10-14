<div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-light border">
  <div class="col-lg-9 px-0">
    {% if auth is defined and auth %}
      <span class="badge bg-primary mb-2 text-uppercase">Portal {{ auth['rol']|e }}</span>
      <h1 class="display-6 fw-bold">Bienvenido, {{ auth['usuario']|e }}</h1>
      {% if auth['rol'] == 'director' %}
        <p class="lead my-3 text-muted">Panel de control y administración general del sistema escolar institucional.</p>
      {% elseif auth['rol'] == 'profesor' %}
        <p class="lead my-3 text-muted">Portal docente para el seguimiento de clases, registro de calificaciones y consulta de nóminas.</p>
      {% elseif auth['rol'] == 'alumno' %}
        <p class="lead my-3 text-muted">Portal estudiantil para la consulta oficial de notas, materias aprobadas y boleta académica.</p>
      {% endif %}
    {% else %}
      <h1 class="display-6 fw-bold">Sistema de Alumnos y Notas</h1>
      <p class="lead my-3">Gestión académica institucional de bachillerato, cursos aprobados, calificaciones y catálogos escolares.</p>
      <a href="{{ url('login')|e }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión</a>
    {% endif %}
  </div>
</div>

{% if auth is defined and auth %}
  {% if auth['rol'] == 'alumno' %}
    <div class="row g-4 mb-4">
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-file-earmark-check text-primary me-2"></i>Mis Calificaciones</h5>
            <p class="card-text text-muted small">Consulte sus calificaciones oficiales publicadas, promedio general y cursos aprobados.</p>
            <a href="{{ url('mi/notas')|e }}" class="btn btn-primary btn-sm">Ver Mis Notas</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-person-lines-fill text-success me-2"></i>Mi Perfil y Clases</h5>
            <p class="card-text text-muted small">Consulte sus datos de estudiante y las clases en las que se encuentra matriculado.</p>
            <a href="{{ url('mi/perfil')|e }}" class="btn btn-success btn-sm">Ver Mi Perfil</a>
          </div>
        </div>
      </div>
      {% if auth['alumno_id'] is defined and auth['alumno_id'] %}
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Boleta Oficial (PDF)</h5>
              <p class="card-text text-muted small">Descargue en formato PDF oficial su ficha de estudiante con historial académico.</p>
              <a href="{{ url('alumnos/' ~ auth['alumno_id'] ~ '/ficha?formato=pdf')|e }}" target="_blank" class="btn btn-outline-danger btn-sm">Descargar Boleta</a>
            </div>
          </div>
        </div>
      {% endif %}
    </div>

  {% elseif auth['rol'] == 'profesor' %}
    <div class="row g-4 mb-4">
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-mortarboard-fill text-primary me-2"></i>Mis Clases</h5>
            <p class="card-text text-muted small">Gestión de nóminas de estudiantes, captura de calificaciones y publicación oficial.</p>
            <a href="{{ url('profesor/clases')|e }}" class="btn btn-primary btn-sm">Ir a Mis Clases</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-journal-text text-success me-2"></i>Reporte de Notas</h5>
            <p class="card-text text-muted small">Filtre calificaciones por carrera, curso y sección con descarga en PDF y CSV.</p>
            <a href="{{ url('reportes/notas')|e }}" class="btn btn-success btn-sm">Consultar Reporte</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-file-earmark-bar-graph text-info me-2"></i>Alumnos por Carrera</h5>
            <p class="card-text text-muted small">Listado general de alumnos matriculados por carrera con exportación de archivos.</p>
            <a href="{{ url('reportes/alumnos-por-carrera')|e }}" class="btn btn-info text-white btn-sm">Ver Alumnos</a>
          </div>
        </div>
      </div>
    </div>

  {% else %}
    {# Director #}
    <div class="row g-4 mb-4">
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-speedometer2 text-primary me-2"></i>Tablero</h5>
            <p class="card-text text-muted small">Métricas generales, distribución y resumen de la institución.</p>
            <a href="{{ url('tablero')|e }}" class="btn btn-sm btn-outline-primary">Ver Tablero</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-people text-success me-2"></i>Alumnos</h5>
            <p class="card-text text-muted small">Registro, expedientes, fotografía e historial de notas.</p>
            <a href="{{ url('alumnos')|e }}" class="btn btn-sm btn-outline-success">Gestionar Alumnos</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-person-badge text-warning me-2"></i>Docentes</h5>
            <p class="card-text text-muted small">Registro del cuerpo docente, especialidades y contactos.</p>
            <a href="{{ url('profesores')|e }}" class="btn btn-sm btn-outline-warning">Gestionar Docentes</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-collection text-info me-2"></i>Clases</h5>
            <p class="card-text text-muted small">Asignación académica de cursos, profesores y secciones.</p>
            <a href="{{ url('clases')|e }}" class="btn btn-sm btn-outline-info">Gestionar Clases</a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-award text-primary me-2"></i>Carreras</h5>
            <p class="card-text text-muted small">Catálogo de carreras de bachillerato disponibles.</p>
            <a href="{{ url('catalogos/carreras')|e }}" class="btn btn-sm btn-outline-primary">Ver Carreras</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-book text-success me-2"></i>Cursos</h5>
            <p class="card-text text-muted small">Catálogo de cursos o asignaturas académicas.</p>
            <a href="{{ url('catalogos/cursos')|e }}" class="btn btn-sm btn-outline-success">Ver Cursos</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-grid-3x3-gap text-warning me-2"></i>Secciones</h5>
            <p class="card-text text-muted small">Catálogo de secciones escolares (A, B, C).</p>
            <a href="{{ url('catalogos/secciones')|e }}" class="btn btn-sm btn-outline-warning">Ver Secciones</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-calendar3 text-info me-2"></i>Semestres</h5>
            <p class="card-text text-muted small">Ciclos académicos con opción de cierre oficial.</p>
            <a href="{{ url('catalogos/semestres')|e }}" class="btn btn-sm btn-outline-info">Ver Semestres</a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5><i class="bi bi-journal-check text-dark me-2"></i>Registro de Notas</h5>
            <p class="text-muted small">Búsqueda rápida de alumno e ingreso de calificaciones.</p>
            <a href="{{ url('notas/buscar')|e }}" class="btn btn-primary btn-sm">Ingreso de Notas</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5><i class="bi bi-file-earmark-bar-graph text-dark me-2"></i>Reportes Descargables</h5>
            <p class="text-muted small">Reportes de alumnos y notas con exportación a PDF y CSV.</p>
            <a href="{{ url('reportes/alumnos-por-carrera')|e }}" class="btn btn-primary btn-sm">Ver Reportes</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5><i class="bi bi-shield-check text-dark me-2"></i>Auditoría de Notas</h5>
            <p class="text-muted small">Bitácora de modificaciones con motivo, usuario y fecha.</p>
            <a href="{{ url('auditoria')|e }}" class="btn btn-outline-secondary btn-sm">Ver Auditoría</a>
          </div>
        </div>
      </div>
    </div>
  {% endif %}

{% else %}
  {# Invitado #}
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body p-4">
          <h4><i class="bi bi-shield-lock text-primary me-2"></i>Acceso al Sistema</h4>
          <p class="text-muted">Por favor inicie sesión con sus credenciales institucionales para acceder a los módulos académicos según su rol asignado (Director, Docente o Estudiante).</p>
          <a href="{{ url('login')|e }}" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i> Ir al Formulario de Ingreso
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body p-4">
          <h4><i class="bi bi-info-circle text-info me-2"></i>Módulos Disponibles</h4>
          <ul class="list-unstyled text-muted mb-0">
            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Estudiantes:</strong> Consulta de historial académico, promedio y descarga de boleta en PDF.</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Docentes:</strong> Registro y edición de notas, clases asignadas y publicación de actas.</li>
            <li><i class="bi bi-check2-circle text-success me-2"></i><strong>Directores:</strong> Control integral, catálogos, inscripciones, reportes y auditoría.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
{% endif %}
