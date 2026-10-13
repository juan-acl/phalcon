<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema escolar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="{{ url('') }}"><i class="bi bi-mortarboard-fill me-1"></i>Escuela</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Navegación">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <div class="navbar-nav me-auto">
        {% if auth is defined and auth %}
          {% if auth['rol'] == 'director' %}
            <a class="nav-link" href="{{ url('alumnos') }}"><i class="bi bi-people me-1"></i>Alumnos</a>
            <a class="nav-link" href="{{ url('notas/buscar') }}"><i class="bi bi-journal-check me-1"></i>Notas</a>
            <a class="nav-link" href="{{ url('catalogos/carreras') }}">Carreras</a>
            <a class="nav-link" href="{{ url('catalogos/cursos') }}">Cursos</a>
            <a class="nav-link" href="{{ url('catalogos/secciones') }}">Secciones</a>
            <a class="nav-link" href="{{ url('catalogos/semestres') }}">Semestres</a>
            <a class="nav-link" href="{{ url('reportes/alumnos-por-carrera') }}"><i class="bi bi-file-earmark-bar-graph me-1"></i>Reportes</a>
          {% elseif auth['rol'] == 'profesor' %}
            <a class="nav-link" href="{{ url('reportes/notas') }}"><i class="bi bi-journal-text me-1"></i>Notas</a>
            <a class="nav-link" href="{{ url('reportes/alumnos-por-carrera') }}"><i class="bi bi-file-earmark-bar-graph me-1"></i>Reportes</a>
          {% elseif auth['rol'] == 'alumno' %}
            {% if auth['alumno_id'] %}
              <a class="nav-link" href="{{ url('alumnos/' ~ auth['alumno_id'] ~ '/ficha') }}"><i class="bi bi-person-lines-fill me-1"></i>Mi Ficha</a>
            {% endif %}
          {% endif %}
        {% endif %}
      </div>

      <div class="navbar-nav ms-auto align-items-center">
        {% if auth is defined and auth %}
          <span class="navbar-text text-light me-3 small">
            <i class="bi bi-person-circle me-1"></i><strong>{{ auth['usuario']|e }}</strong>
            <span class="badge bg-secondary ms-1">{{ auth['rol']|e }}</span>
          </span>
          <a class="btn btn-sm btn-outline-light me-2" href="{{ url('cuenta/clave') }}" title="Cambiar clave">
            <i class="bi bi-key"></i> Clave
          </a>
          <form method="post" action="{{ url('logout') }}" class="d-inline">
            <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-box-arrow-right"></i> Salir
            </button>
          </form>
        {% else %}
          <a class="btn btn-sm btn-primary" href="{{ url('login') }}">
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
</body>
</html>
