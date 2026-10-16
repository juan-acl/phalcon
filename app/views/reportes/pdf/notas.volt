<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acta Consolidada de Calificaciones</title>
  <style>
    @page {
      margin: 14mm 15mm 18mm 15mm;
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 9px;
      color: #1a202c;
      line-height: 1.35;
    }

    /* Membrete institucional formal */
    .institucion-header {
      width: 100%;
      border-bottom: 2.5px solid #1e293b;
      padding-bottom: 6px;
      margin-bottom: 4px;
    }
    .institucion-nombre {
      font-size: 13px;
      font-weight: bold;
      color: #0f172a;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin: 0 0 2px 0;
    }
    .institucion-sub {
      font-size: 8.5px;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin: 0;
    }

    /* Título del documento */
    .documento-titulo-box {
      margin-top: 8px;
      margin-bottom: 10px;
      text-align: center;
    }
    .documento-titulo {
      font-size: 11.5px;
      font-weight: bold;
      color: #0f172a;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 0 0 2px 0;
    }
    .documento-subtitulo {
      font-size: 8.5px;
      color: #64748b;
      font-style: italic;
      margin: 0;
    }

    /* Tabla de metadatos */
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      background-color: #f8fafc;
      border: 1px solid #cbd5e1;
    }
    .meta-table td {
      padding: 4px 7px;
      font-size: 8.5px;
      border: 1px solid #e2e8f0;
    }
    .meta-label {
      font-weight: bold;
      color: #334155;
      text-transform: uppercase;
      font-size: 7.5px;
      letter-spacing: 0.3px;
      width: 16%;
    }
    .meta-value {
      color: #0f172a;
      width: 34%;
    }

    /* Tabla de calificaciones */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #cbd5e1;
      padding: 4px 6px;
      font-size: 8.5px;
    }
    table.data-table th {
      background-color: #1e293b;
      color: #ffffff;
      font-weight: bold;
      font-size: 8px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      text-align: left;
    }
    table.data-table tr:nth-child(even) {
      background-color: #f8fafc;
    }
    thead {
      display: table-header-group;
    }
    tr {
      page-break-inside: avoid;
    }

    .estado-aprobado {
      color: #166534;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 7.5px;
    }
    .estado-reprobado {
      color: #991b1b;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 7.5px;
    }

    /* Tabla resumen estadístico */
    .resumen-tabla {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border: 1px solid #cbd5e1;
    }
    .resumen-tabla th {
      background-color: #f1f5f9;
      color: #334155;
      font-size: 7.5px;
      text-transform: uppercase;
      padding: 4px 6px;
      border: 1px solid #cbd5e1;
      text-align: center;
    }
    .resumen-tabla td {
      padding: 5px 6px;
      font-size: 8.5px;
      border: 1px solid #cbd5e1;
      text-align: center;
      background-color: #ffffff;
    }

    /* Cláusula y firmas */
    .certificacion-texto {
      margin-top: 12px;
      font-size: 7.5px;
      color: #64748b;
      line-height: 1.3;
      text-align: justify;
      border-top: 1px dashed #cbd5e1;
      padding-top: 5px;
    }
    .firmas-container {
      margin-top: 30px;
      width: 100%;
    }
    .firmas-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    .firmas-table td {
      border: none;
      width: 50%;
      text-align: center;
      vertical-align: top;
      padding: 0 40px;
    }
    .linea-firma {
      border-top: 1px solid #334155;
      margin-bottom: 4px;
    }
    .cargo-firma {
      font-size: 8px;
      color: #334155;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .dep-firma {
      font-size: 7.5px;
      color: #64748b;
    }

    /* Pie de página */
    .footer {
      position: fixed;
      bottom: -10mm;
      left: 0;
      right: 0;
      height: 8mm;
      border-top: 0.8px solid #cbd5e1;
      padding-top: 3px;
    }
    .footer-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    .footer-table td {
      border: none;
      padding: 0;
      font-size: 7.5px;
      color: #64748b;
    }
    .footer-left {
      text-align: left;
      width: 72%;
    }
    .footer-right {
      text-align: right;
      width: 28%;
      padding-right: 55pt;
      white-space: nowrap;
    }
  </style>
</head>
<body>

  <!-- Pie de página fijo -->
  <div class="footer">
    <table class="footer-table">
      <tr>
        <td class="footer-left">
          Sistema de Control y Registro Escolar &bull; Acta Oficial de Calificaciones &bull; República de Guatemala
        </td>
        <td class="footer-right">
          Emisión: {{ fechaHora|e }}
        </td>
      </tr>
    </table>
  </div>

  <!-- Membrete oficial -->
  <div class="institucion-header">
    <table style="width: 100%; border-collapse: collapse; border: none;">
      <tr>
        <td style="width: 75%; border: none; padding: 0;">
          <div class="institucion-nombre">Colegio de Bachillerato en Ciencias y Computación</div>
          <div class="institucion-sub">Dirección General &bull; Departamento de Registro y Evaluación del Rendimiento Escolar</div>
        </td>
        <td style="width: 25%; border: none; padding: 0; text-align: right;">
          <div style="font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase;">Acta No.</div>
          <div style="font-size: 8px; color: #64748b;">ACT-NOT-2026</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Título del documento -->
  <div class="documento-titulo-box">
    <div class="documento-titulo">Acta Consolidada de Calificaciones y Rendimiento Académico</div>
    <div class="documento-subtitulo">Cuadro de evaluación y registro oficial de calificaciones por carrera, asignatura y sección</div>
  </div>

  <!-- Ficha técnica del acta -->
  <table class="meta-table">
    <tr>
      <td class="meta-label">Carrera / Plan:</td>
      <td class="meta-value"><strong>{{ filtroCarrera|e }}</strong></td>
      <td class="meta-label">Asignatura / Curso:</td>
      <td class="meta-value"><strong>{{ filtroCurso|e }}</strong></td>
    </tr>
    <tr>
      <td class="meta-label">Sección Asignada:</td>
      <td class="meta-value"><strong>{{ filtroSeccion|e }}</strong></td>
      <td class="meta-label">Umbral de Aprobación:</td>
      <td class="meta-value"><strong>{{ umbral|e }} pts.</strong> (escala de 0 a 100)</td>
    </tr>
    <tr>
      <td class="meta-label">Total Evaluaciones:</td>
      <td class="meta-value"><strong>{{ filas|length|e }}</strong> registro(s)</td>
      <td class="meta-label">Fecha de Cierre:</td>
      <td class="meta-value">{{ fechaHora|e }}</td>
    </tr>
  </table>

  <!-- Tabla de resultados -->
  {% if filas is empty %}
    <p style="text-align: center; color: #64748b; margin-top: 25px; font-style: italic;">
      No se registran calificaciones asentadas bajo los criterios de consulta especificados.
    </p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 4%; text-align: center;">No.</th>
          <th style="width: 20%;">Carrera</th>
          <th style="width: 20%;">Asignatura</th>
          <th style="width: 8%; text-align: center;">Sec.</th>
          <th style="width: 14%;">Semestre</th>
          <th style="width: 22%;">Apellidos y Nombres</th>
          <th style="width: 6%; text-align: right;">Nota</th>
          <th style="width: 6%; text-align: center;">Condición</th>
        </tr>
      </thead>
      <tbody>
        {% for index, f in filas %}
          <tr>
            <td style="text-align: center; color: #475569;">{{ (index + 1)|e }}</td>
            <td>{{ f['carrera']|e }}</td>
            <td>{{ f['curso']|e }}</td>
            <td style="text-align: center; font-weight: bold;">{{ f['seccion']|e }}</td>
            <td>{{ f['semestre']|e }}</td>
            <td><strong>{{ f['apellidos']|e }}</strong>, {{ f['nombres']|e }}</td>
            <td style="text-align: right; font-weight: bold;">{{ f['nota']|e }}</td>
            <td style="text-align: center;">
              {% if f['aprobado'] %}
                <span class="estado-aprobado">Aprobado</span>
              {% else %}
                <span class="estado-reprobado">Reprobado</span>
              {% endif %}
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <!-- Resumen estadístico formal -->
    <table class="resumen-tabla">
      <thead>
        <tr>
          <th>Total Evaluados</th>
          <th>Estudiantes Aprobados</th>
          <th>Estudiantes Reprobados</th>
          <th>Promedio General</th>
          <th>Porcentaje Promoción</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>{{ (filas|length)|e }}</strong></td>
          <td style="color: #166534; font-weight: bold;">{{ totalAprobados|e }} ({{ porcentajeAprobados|e }}%)</td>
          <td style="color: #991b1b; font-weight: bold;">{{ totalReprobados|e }} ({{ porcentajeReprobados|e }}%)</td>
          <td><strong>{{ promedio|e }}</strong> pts.</td>
          <td style="font-weight: bold;">{{ porcentajeAprobados|e }}%</td>
        </tr>
      </tbody>
    </table>

    <!-- Certificación y firmas de cierre -->
    <div class="certificacion-texto">
      <strong>CERTIFICACIÓN DEL ACTA:</strong> El infrascrito docente y las autoridades académicas del plantel certifican que las calificaciones aquí registradas corresponden fielmente a las evaluaciones reglamentarias efectuadas en el periodo correspondiente, de conformidad con el régimen escolar vigente.
    </div>

    <div class="firmas-container">
      <table class="firmas-table">
        <tr>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Docente / Catedrático Titular</div>
            <div class="dep-firma">Firma y Certificación de Calificaciones</div>
          </td>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Dirección General y Secretaría</div>
            <div class="dep-firma">Vo.Bo. Aprobación y Archivo Oficial</div>
          </td>
        </tr>
      </table>
    </div>
  {% endif %}

</body>
</html>
