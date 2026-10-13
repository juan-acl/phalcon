<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Alumnos por Carrera</title>
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
      border-bottom: 2px solid #2b4c7e;
      padding-bottom: 8px;
      margin-bottom: 12px;
    }
    .header h1 {
      font-size: 16px;
      color: #2b4c7e;
      margin: 0 0 4px 0;
      text-transform: uppercase;
    }
    .header h2 {
      font-size: 12px;
      color: #555;
      margin: 0;
      font-weight: normal;
    }
    .meta-box {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      padding: 8px 12px;
      margin-bottom: 15px;
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
      font-size: 9.5px;
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
      background-color: #2b4c7e;
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
    .totales {
      margin-top: 15px;
      padding: 8px 12px;
      background-color: #eef2f7;
      border-left: 4px solid #2b4c7e;
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
    Documento emitido por Sistema Escolar &bull; Página <span class="pagenum"></span>
  </div>

  <div class="header">
    <h1>Colegio de Bachillerato &bull; Sistema Académico</h1>
    <h2>Reporte General de Alumnos por Carrera</h2>
  </div>

  <div class="meta-box">
    <table>
      <tr>
        <td style="width: 50%;"><strong>Filtro aplicado:</strong> {{ carreraNombre|e }}</td>
        <td style="width: 50%;"><strong>Fecha y hora:</strong> {{ fechaHora|e }}</td>
      </tr>
      <tr>
        <td><strong>Total listados:</strong> {{ alumnos|length|e }} alumno(s)</td>
        <td><strong>Generado por:</strong> {{ usuario|e }}</td>
      </tr>
    </table>
  </div>

  {% if alumnos is empty %}
    <p style="text-align: center; color: #777; margin-top: 30px;">No se encontraron alumnos para los criterios seleccionados.</p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%;">#</th>
          <th style="width: 35%;">Carrera</th>
          <th style="width: 40%;">Apellidos y Nombres</th>
          <th style="width: 20%; text-align: center;">Fecha Nacimiento</th>
        </tr>
      </thead>
      <tbody>
        {% for index, a in alumnos %}
          <tr>
            <td style="text-align: center;">{{ (index + 1)|e }}</td>
            <td>{{ a['carrera']|e }}</td>
            <td><strong>{{ a['apellidos']|e }}</strong>, {{ a['nombres']|e }}</td>
            <td style="text-align: center;">{{ a['fecha_nacimiento']|e }}</td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <div class="totales">
      <strong>Resumen de Registro:</strong> Total de <strong>{{ alumnos|length|e }}</strong> alumno(s) matriculado(s) en el reporte.
    </div>
  {% endif %}
</body>
</html>
