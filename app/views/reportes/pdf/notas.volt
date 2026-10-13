<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Calificaciones</title>
  <style>
    @page {
      margin: 15mm 15mm 18mm 15mm;
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 9.5px;
      color: #333;
      line-height: 1.35;
    }
    .header {
      border-bottom: 2px solid #198754;
      padding-bottom: 6px;
      margin-bottom: 10px;
    }
    .header h1 {
      font-size: 15px;
      color: #198754;
      margin: 0 0 3px 0;
      text-transform: uppercase;
    }
    .header h2 {
      font-size: 11px;
      color: #555;
      margin: 0;
      font-weight: normal;
    }
    .meta-box {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      padding: 6px 10px;
      margin-bottom: 12px;
      border-radius: 4px;
    }
    .meta-box table {
      width: 100%;
      border: none;
      margin: 0;
    }
    .meta-box td {
      border: none;
      padding: 2px 4px;
      font-size: 9px;
    }
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #dee2e6;
      padding: 5px 6px;
      text-align: left;
    }
    table.data-table th {
      background-color: #198754;
      color: #ffffff;
      font-weight: bold;
      font-size: 9px;
      text-transform: uppercase;
    }
    table.data-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }
    thead {
      display: table-header-group;
    }
    tr {
      page-break-inside: avoid;
    }
    .badge-aprobado {
      color: #0f5132;
      font-weight: bold;
    }
    .badge-reprobado {
      color: #842029;
      font-weight: bold;
    }
    .totales-box {
      margin-top: 12px;
      padding: 8px 12px;
      background-color: #e8f5e9;
      border-left: 4px solid #198754;
      font-size: 9.5px;
    }
    .footer {
      position: fixed;
      bottom: -10mm;
      left: 0;
      right: 0;
      height: 7mm;
      font-size: 8px;
      color: #777;
      border-top: 1px solid #ccc;
      padding-top: 3px;
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="footer">
    Colegio de Bachillerato &bull; Sistema de Calificaciones &bull; Fecha: {{ fechaHora|e }} &bull; Página <span class="pagenum"></span>
  </div>

  <div class="header">
    <h1>Colegio de Bachillerato &bull; Sistema Académico</h1>
    <h2>Reporte de Calificaciones por Carrera, Curso y Sección</h2>
  </div>

  <div class="meta-box">
    <table>
      <tr>
        <td style="width: 35%;"><strong>Carrera:</strong> {{ filtroCarrera|e }}</td>
        <td style="width: 35%;"><strong>Curso:</strong> {{ filtroCurso|e }}</td>
        <td style="width: 30%;"><strong>Sección:</strong> {{ filtroSeccion|e }}</td>
      </tr>
      <tr>
        <td><strong>Umbral aprobación:</strong> {{ umbral|e }} pts.</td>
        <td><strong>Registros:</strong> {{ filas|length|e }}</td>
        <td><strong>Generado por:</strong> {{ usuario|e }}</td>
      </tr>
    </table>
  </div>

  {% if filas is empty %}
    <p style="text-align: center; color: #777; margin-top: 25px;">No se encontraron calificaciones con los filtros seleccionados.</p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 4%;">#</th>
          <th style="width: 20%;">Carrera</th>
          <th style="width: 20%;">Curso</th>
          <th style="width: 7%; text-align: center;">Sec.</th>
          <th style="width: 15%;">Semestre</th>
          <th style="width: 22%;">Alumno</th>
          <th style="width: 6%; text-align: right;">Nota</th>
          <th style="width: 6%; text-align: center;">Estado</th>
        </tr>
      </thead>
      <tbody>
        {% for index, f in filas %}
          <tr>
            <td style="text-align: center;">{{ (index + 1)|e }}</td>
            <td>{{ f['carrera']|e }}</td>
            <td>{{ f['curso']|e }}</td>
            <td style="text-align: center;"><strong>{{ f['seccion']|e }}</strong></td>
            <td>{{ f['semestre']|e }}</td>
            <td><strong>{{ f['apellidos']|e }}</strong>, {{ f['nombres']|e }}</td>
            <td style="text-align: right; font-weight: bold;">{{ f['nota']|e }}</td>
            <td style="text-align: center;">
              {% if f['aprobado'] %}
                <span class="badge-aprobado">Aprobado</span>
              {% else %}
                <span class="badge-reprobado">Reprobado</span>
              {% endif %}
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <div class="totales-box">
      <strong>Resumen Estadístico:</strong>
      Total: <strong>{{ filas|length|e }}</strong> notas &bull;
      Promedio General: <strong>{{ promedio|e }}</strong> pts &bull;
      Aprobados: <strong style="color:#0f5132;">{{ totalAprobados|e }}</strong> ({{ porcentajeAprobados|e }}%) &bull;
      Reprobados: <strong style="color:#842029;">{{ totalReprobados|e }}</strong> ({{ porcentajeReprobados|e }}%)
    </div>
  {% endif %}
</body>
</html>
