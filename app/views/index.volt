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
<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="{{ url('') }}">Escuela</a>
    <div class="navbar-nav">
      <a class="nav-link" href="{{ url('alumnos') }}">Alumnos</a>
      <a class="nav-link" href="{{ url('notas/buscar') }}">Notas</a>
      <a class="nav-link" href="{{ url('catalogos/carreras') }}">Carreras</a>
      <a class="nav-link" href="{{ url('catalogos/cursos') }}">Cursos</a>
      <a class="nav-link" href="{{ url('catalogos/secciones') }}">Secciones</a>
      <a class="nav-link" href="{{ url('catalogos/semestres') }}">Semestres</a>
      <a class="nav-link" href="{{ url('reportes/alumnos-por-carrera') }}">Reportes</a>
    </div>
  </div>
</nav>
<main class="container">
  {{ flashSession.output() }}
  {{ content() }}
</main>
</body>
</html>
