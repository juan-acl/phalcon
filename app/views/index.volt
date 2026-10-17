<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema escolar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .navbar-dark .navbar-nav .nav-link.active,
    .navbar-dark .navbar-nav .nav-item.dropdown.active > .nav-link {
      background-color: rgba(255, 255, 255, 0.15);
      border-radius: 0.375rem;
      color: #fff;
      font-weight: 500;
    }
  </style>
</head>
<body>
{% set currentController = dispatcher.getControllerName() %}
{% set currentAction     = dispatcher.getActionName() %}
{% set currentUri        = this.request.getUri() %}
<nav class="navbar navbar-expand-xl navbar-dark bg-dark mb-4 shadow-sm">
  <div class="container-fluid px-3 px-xl-4">
    <a class="navbar-brand fw-bold" href="{{ url('')|e }}"><i class="bi bi-mortarboard-fill me-1"></i>Escuela</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Navegación">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <div class="navbar-nav me-auto py-2 py-xl-0">
        {% if auth is defined and auth %}
          {% if auth['rol'] == 'director' %}
            <a class="nav-link {% if currentController == 'tablero' %}active{% endif %}" href="{{ url('tablero')|e }}"><i class="bi bi-speedometer2 me-1"></i>Tablero</a>
            <a class="nav-link {% if currentController == 'alumnos' %}active{% endif %}" href="{{ url('alumnos')|e }}"><i class="bi bi-people me-1"></i>Alumnos</a>
            <a class="nav-link {% if currentController == 'profesores' %}active{% endif %}" href="{{ url('profesores')|e }}"><i class="bi bi-person-badge me-1"></i>Docentes</a>
            <a class="nav-link {% if currentController == 'clases' %}active{% endif %}" href="{{ url('clases')|e }}"><i class="bi bi-collection me-1"></i>Clases</a>
            <a class="nav-link {% if currentController == 'inscripciones' %}active{% endif %}" href="{{ url('inscripciones')|e }}"><i class="bi bi-person-check me-1"></i>Inscripciones</a>
            <a class="nav-link {% if currentController == 'notas' %}active{% endif %}" href="{{ url('notas/buscar')|e }}"><i class="bi bi-journal-check me-1"></i>Notas</a>
            <div class="nav-item dropdown {% if currentController == 'catalogos' %}active{% endif %}">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-folder me-1"></i>Catálogos
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item {% if '/catalogos/carreras' in currentUri %}active{% endif %}" href="{{ url('catalogos/carreras')|e }}"><i class="bi bi-award me-2 text-primary"></i>Carreras</a></li>
                <li><a class="dropdown-item {% if '/catalogos/cursos' in currentUri %}active{% endif %}" href="{{ url('catalogos/cursos')|e }}"><i class="bi bi-book me-2 text-success"></i>Cursos</a></li>
                <li><a class="dropdown-item {% if '/catalogos/secciones' in currentUri %}active{% endif %}" href="{{ url('catalogos/secciones')|e }}"><i class="bi bi-grid-3x3-gap me-2 text-warning"></i>Secciones</a></li>
                <li><a class="dropdown-item {% if '/catalogos/semestres' in currentUri %}active{% endif %}" href="{{ url('catalogos/semestres')|e }}"><i class="bi bi-calendar3 me-2 text-info"></i>Semestres</a></li>
              </ul>
            </div>
            <a class="nav-link {% if currentController == 'reportes' %}active{% endif %}" href="{{ url('reportes/alumnos-por-carrera')|e }}"><i class="bi bi-file-earmark-bar-graph me-1"></i>Reportes</a>
            <a class="nav-link {% if currentController == 'auditoria' %}active{% endif %}" href="{{ url('auditoria')|e }}"><i class="bi bi-shield-check me-1"></i>Auditoría</a>
          {% elseif auth['rol'] == 'profesor' %}
            <a class="nav-link {% if currentController == 'profesor' %}active{% endif %}" href="{{ url('profesor/clases')|e }}"><i class="bi bi-mortarboard me-1"></i>Mis Clases</a>
            <a class="nav-link {% if currentController == 'notas' %}active{% endif %}" href="{{ url('notas/buscar')|e }}"><i class="bi bi-journal-plus me-1"></i>Ingresar Notas</a>
            <a class="nav-link {% if currentController == 'reportes' and currentAction == 'notas' %}active{% endif %}" href="{{ url('reportes/notas')|e }}"><i class="bi bi-journal-text me-1"></i>Reporte Notas</a>
            <a class="nav-link {% if currentController == 'reportes' and currentAction == 'alumnosporcarrera' %}active{% endif %}" href="{{ url('reportes/alumnos-por-carrera')|e }}"><i class="bi bi-file-earmark-bar-graph me-1"></i>Alumnos por Carrera</a>
          {% elseif auth['rol'] == 'alumno' %}
            <a class="nav-link {% if currentController == 'alumno' and currentAction in ['perfil', 'mificha', 'descargarficha'] %}active{% endif %}" href="{{ url('mi/perfil')|e }}"><i class="bi bi-person-lines-fill me-1"></i>Mi Perfil</a>
            <a class="nav-link {% if currentController == 'alumno' and currentAction in ['notas', 'misnotas'] %}active{% endif %}" href="{{ url('mi/notas')|e }}"><i class="bi bi-file-earmark-check me-1"></i>Mis Notas</a>
          {% endif %}
        {% endif %}
      </div>

      <div class="navbar-nav ms-auto align-items-center flex-row flex-wrap gap-2 pt-2 pt-xl-0">
        {% if auth is defined and auth %}
          <span class="navbar-text text-light me-2 small">
            <i class="bi bi-person-circle me-1"></i><strong>{{ auth['usuario']|e }}</strong>
            <span class="badge bg-secondary ms-1">{{ auth['rol']|e }}</span>
          </span>
          <a class="btn btn-sm btn-outline-light me-1" href="{{ url('cuenta/clave')|e }}" title="Cambiar clave">
            <i class="bi bi-key"></i> Clave
          </a>
          <form method="post" action="{{ url('logout')|e }}" class="d-inline">
            <input type="hidden" name="{{ security.getTokenKey()|e }}" value="{{ security.getToken()|e }}">
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-box-arrow-right"></i> Salir
            </button>
          </form>
        {% else %}
          <a class="btn btn-sm btn-primary" href="{{ url('login')|e }}">
            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
          </a>
        {% endif %}
      </div>
    </div>
  </div>
</nav>

<main class="container">
  {{ flashSession.output() }}
  {{ content() }}
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
