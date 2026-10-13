<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ficha del Alumno</title>
  <style>
    @page {
      margin: 20mm 15mm 20mm 15mm;
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 10px;
      color: #333;
      line-height: 1.4;
    }
    .header {
      border-bottom: 2px solid #0d6efd;
      padding-bottom: 8px;
      margin-bottom: 15px;
    }
    .header h1 {
      font-size: 16px;
      color: #0d6efd;
      margin: 0 0 4px 0;
      text-transform: uppercase;
    }
    .header h2 {
      font-size: 12px;
      color: #555;
      margin: 0;
      font-weight: normal;
    }
    .ficha-box {
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 12px;
      margin-bottom: 15px;
      background-color: #fcfcfc;
    }
    .ficha-table {
      width: 100%;
      border: none;
    }
    .ficha-table td {
      border: none;
      padding: 3px 6px;
      vertical-align: top;
    }
    .foto-box {
      width: 110px;
      text-align: center;
      vertical-align: middle;
    }
    .foto-img {
      width: 95px;
      height: 115px;
      object-fit: cover;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .foto-placeholder {
      width: 95px;
      height: 115px;
      background-color: #e9ecef;
      border: 1px dashed #adb5bd;
      border-radius: 4px;
      text-align: center;
      line-height: 115px;
      color: #6c757d;
      font-size: 9px;
    }
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #dee2e6;
      padding: 6px 8px;
      text-align: left;
    }
    table.data-table th {
      background-color: #0d6efd;
      color: #ffffff;
      font-weight: bold;
      font-size: 9.5px;
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
    .resumen-box {
      margin-top: 15px;
      padding: 10px 14px;
      background-color: #f0f7ff;
      border-left: 4px solid #0d6efd;
      font-size: 10px;
    }
    .footer {
      position: fixed;
      bottom: -12mm;
      left: 0;
      right: 0;
      height: 8mm;
      font-size: 8.5px;
      color: #777;
      border-top: 1px solid #ccc;
      padding-top: 4px;
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="footer">
    Colegio de Bachillerato &bull; Ficha Oficial de Alumno &bull; Fecha: {{ fechaHora|e }} &bull; Página <span class="pagenum"></span>
  </div>

  <div class="header">
    <h1>Colegio de Bachillerato &bull; Sistema Académico</h1>
    <h2>Ficha Oficial del Alumno e Historial de Calificaciones</h2>
  </div>

  <div class="ficha-box">
    <table class="ficha-table">
      <tr>
        <td class="foto-box">
          {% if fotoPath is not empty %}
            <img src="{{ fotoPath|e }}" class="foto-img" alt="Foto">
          {% else %}
            <div class="foto-placeholder">Sin fotografía</div>
          {% endif %}
        </td>
        <td style="padding-left: 15px;">
          <table style="width: 100%; border: none;">
            <tr>
              <td style="width: 30%;"><strong>No. de Registro (ID):</strong></td>
              <td>#{{ alumno.id|e }}</td>
            </tr>
            <tr>
              <td><strong>Nombres:</strong></td>
              <td>{{ alumno.nombres|e }}</td>
            </tr>
            <tr>
              <td><strong>Apellidos:</strong></td>
              <td>{{ alumno.apellidos|e }}</td>
            </tr>
            <tr>
              <td><strong>Carrera inscrita:</strong></td>
              <td><strong>{{ carreraNombre|e }}</strong></td>
            </tr>
            <tr>
              <td><strong>Fecha de Nacimiento:</strong></td>
              <td>{{ alumno.fecha_nacimiento|e }}</td>
            </tr>
            <tr>
              <td><strong>Fecha de Emisión:</strong></td>
              <td>{{ fechaHora|e }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <h3 style="font-size: 12px; color: #0d6efd; margin: 15px 0 6px 0; text-transform: uppercase;">
    Historial de Cursos y Calificaciones
  </h3>

  {% if historial is empty %}
    <p style="text-align: center; color: #777; margin-top: 25px;">El alumno aún no tiene calificaciones registradas.</p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%;">#</th>
          <th style="width: 25%;">Semestre</th>
          <th style="width: 35%;">Curso</th>
          <th style="width: 10%; text-align: center;">Sección</th>
          <th style="width: 12%; text-align: right;">Nota</th>
          <th style="width: 13%; text-align: center;">Resultado</th>
        </tr>
      </thead>
      <tbody>
        {% for index, h in historial %}
          <tr>
            <td style="text-align: center;">{{ (index + 1)|e }}</td>
            <td>{{ h['semestre']|e }}</td>
            <td><strong>{{ h['curso']|e }}</strong></td>
            <td style="text-align: center;">{{ h['seccion']|e }}</td>
            <td style="text-align: right; font-weight: bold;">{{ h['nota']|e }}</td>
            <td style="text-align: center;">
              {% if h['aprobado'] %}
                <span style="color: #0f5132; font-weight: bold;">Aprobado</span>
              {% else %}
                <span style="color: #842029; font-weight: bold;">Reprobado</span>
              {% endif %}
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <div class="resumen-box">
      <strong>Rendimiento Académico:</strong>
      Cursos Registrados: <strong>{{ (historial|length)|e }}</strong> &bull;
      Aprobados (>= {{ umbral|e }} pts): <strong style="color: #0f5132;">{{ totalAprobados|e }}</strong> &bull;
      Reprobados (< {{ umbral|e }} pts): <strong style="color: #842029;">{{ totalReprobados|e }}</strong> &bull;
      Promedio Acumulado: <strong>{{ promedio|e }}</strong> pts.
    </div>
  {% endif %}
</body>
</html>
