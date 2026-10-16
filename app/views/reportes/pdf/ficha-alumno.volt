<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Expediente Académico Individual</title>
  <style>
    @page {
      margin: 18mm 15mm 20mm 15mm;
      @bottom-right {
        content: "Página " counter(page) " de " counter(pages);
      }
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 9.5px;
      color: #1a202c;
      line-height: 1.4;
    }

    /* Membrete institucional */
    .institucion-header {
      width: 100%;
      border-bottom: 2.5px solid #1e293b;
      padding-bottom: 8px;
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
      margin-top: 10px;
      margin-bottom: 12px;
      text-align: center;
    }
    .documento-titulo {
      font-size: 12px;
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

    /* Ficha del alumno */
    .ficha-box {
      border: 1px solid #cbd5e1;
      background-color: #f8fafc;
      margin-bottom: 14px;
      padding: 10px 12px;
    }
    .ficha-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    .ficha-table td {
      border: none;
      padding: 0;
      vertical-align: top;
    }
    .foto-celda {
      width: 100px;
      text-align: center;
      padding-right: 15px !important;
    }
    .foto-marco {
      width: 90px;
      height: 110px;
      border: 1px solid #94a3b8;
      background-color: #ffffff;
      padding: 2px;
    }
    .foto-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .foto-placeholder {
      width: 100%;
      height: 100%;
      background-color: #f1f5f9;
      color: #94a3b8;
      font-size: 8px;
      line-height: 110px;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Datos del alumno */
    .datos-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    .datos-table td {
      padding: 3px 6px;
      font-size: 9px;
      border-bottom: 1px solid #e2e8f0;
    }
    .datos-label {
      font-weight: bold;
      color: #334155;
      text-transform: uppercase;
      font-size: 8px;
      letter-spacing: 0.3px;
      width: 32%;
    }
    .datos-valor {
      color: #0f172a;
      width: 68%;
    }

    /* Sección de tabla */
    .seccion-encabezado {
      font-size: 10px;
      font-weight: bold;
      color: #1e293b;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      margin: 14px 0 6px 0;
      padding-bottom: 3px;
      border-bottom: 1px solid #cbd5e1;
    }

    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #cbd5e1;
      padding: 5px 7px;
      font-size: 9px;
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

    /* Resumen de rendimiento formal */
    .resumen-tabla {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border: 1px solid #cbd5e1;
    }
    .resumen-tabla th {
      background-color: #f1f5f9;
      color: #334155;
      font-size: 8px;
      text-transform: uppercase;
      padding: 4px 6px;
      border: 1px solid #cbd5e1;
      text-align: center;
    }
    .resumen-tabla td {
      padding: 5px 6px;
      font-size: 9px;
      border: 1px solid #cbd5e1;
      text-align: center;
      background-color: #ffffff;
    }

    /* Cláusula y firmas */
    .certificacion-texto {
      margin-top: 14px;
      font-size: 7.5px;
      color: #64748b;
      line-height: 1.35;
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
      padding: 0 35px;
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
      bottom: -14mm;
      left: 0;
      right: 0;
      height: 9mm;
      font-size: 7.5px;
      color: #64748b;
      border-top: 0.8px solid #cbd5e1;
      padding-top: 3px;
    }
    .footer-left {
      float: left;
      width: 70%;
      text-align: left;
    }
    .footer-right {
      float: right;
      width: 30%;
      text-align: right;
    }
  </style>
</head>
<body>

  <!-- Pie de página fijo -->
  <div class="footer">
    <div class="footer-left">
      Sistema de Control y Registro Escolar &bull; Expediente Académico del Estudiante &bull; República de Guatemala
    </div>
    <div class="footer-right">
      Emisión: {{ fechaHora|e }}
    </div>
  </div>

  <!-- Membrete oficial -->
  <div class="institucion-header">
    <table style="width: 100%; border-collapse: collapse; border: none;">
      <tr>
        <td style="width: 75%; border: none; padding: 0;">
          <div class="institucion-nombre">Colegio de Bachillerato en Ciencias y Computación</div>
          <div class="institucion-sub">Dirección General &bull; Departamento de Control y Registro Escolar</div>
        </td>
        <td style="width: 25%; border: none; padding: 0; text-align: right;">
          <div style="font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase;">Expediente No.</div>
          <div style="font-size: 8px; color: #64748b;">EXP-ALU-{{ alumno.id|e }}</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Título del documento -->
  <div class="documento-titulo-box">
    <div class="documento-titulo">Ficha Académica Individual e Historial de Calificaciones</div>
    <div class="documento-subtitulo">Registro histórico de cursos cursados, ponderación obtenida y estado de promoción escolar</div>
  </div>

  <!-- Ficha del estudiante con foto oficial -->
  <div class="ficha-box">
    <table class="ficha-table">
      <tr>
        <td class="foto-celda">
          <div class="foto-marco">
            {% if fotoPath is not empty %}
              <img src="{{ fotoPath|e }}" class="foto-img" alt="Fotografía oficial">
            {% else %}
              <div class="foto-placeholder">Fotografía</div>
            {% endif %}
          </div>
        </td>
        <td>
          <table class="datos-table">
            <tr>
              <td class="datos-label">No. Matrícula (ID):</td>
              <td class="datos-valor"><strong>#{{ alumno.id|e }}</strong></td>
            </tr>
            <tr>
              <td class="datos-label">Apellidos y Nombres:</td>
              <td class="datos-valor"><strong>{{ alumno.apellidos|e }}</strong>, {{ alumno.nombres|e }}</td>
            </tr>
            <tr>
              <td class="datos-label">Carrera / Programa:</td>
              <td class="datos-valor"><strong>{{ carreraNombre|e }}</strong></td>
            </tr>
            <tr>
              <td class="datos-label">Fecha de Nacimiento:</td>
              <td class="datos-valor">{{ alumno.fecha_nacimiento|e }}</td>
            </tr>
            <tr>
              <td class="datos-label">Condición de Matrícula:</td>
              <td class="datos-valor" style="color: #166534; font-weight: bold;">Estudiante Regular Activo</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <!-- Historial de calificaciones -->
  <div class="seccion-encabezado">Historial Consolidado de Cursos y Evaluaciones</div>

  {% if historial is empty %}
    <p style="text-align: center; color: #64748b; margin-top: 25px; font-style: italic;">
      El estudiante no cuenta con calificaciones o asignaturas registradas al presente ciclo.
    </p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%; text-align: center;">No.</th>
          <th style="width: 25%;">Semestre / Periodo</th>
          <th style="width: 35%;">Asignatura / Curso</th>
          <th style="width: 10%; text-align: center;">Sección</th>
          <th style="width: 12%; text-align: right;">Nota</th>
          <th style="width: 13%; text-align: center;">Resultado</th>
        </tr>
      </thead>
      <tbody>
        {% for index, h in historial %}
          <tr>
            <td style="text-align: center; color: #475569;">{{ (index + 1)|e }}</td>
            <td>{{ h['semestre']|e }}</td>
            <td><strong>{{ h['curso']|e }}</strong></td>
            <td style="text-align: center;">{{ h['seccion']|e }}</td>
            <td style="text-align: right; font-weight: bold;">{{ h['nota']|e }}</td>
            <td style="text-align: center;">
              {% if h['aprobado'] %}
                <span style="color: #166534; font-weight: bold; text-transform: uppercase; font-size: 7.5px;">Aprobado</span>
              {% else %}
                <span style="color: #991b1b; font-weight: bold; text-transform: uppercase; font-size: 7.5px;">Reprobado</span>
              {% endif %}
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <!-- Resumen de rendimiento formal -->
    <table class="resumen-tabla">
      <thead>
        <tr>
          <th>Cursos Asignados</th>
          <th>Cursos Aprobados (&ge; {{ umbral|e }} pts)</th>
          <th>Cursos Reprobados (&lt; {{ umbral|e }} pts)</th>
          <th>Promedio General Acumulado</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>{{ (historial|length)|e }}</strong></td>
          <td style="color: #166534; font-weight: bold;">{{ totalAprobados|e }}</td>
          <td style="color: #991b1b; font-weight: bold;">{{ totalReprobados|e }}</td>
          <td><strong style="font-size: 10px;">{{ promedio|e }}</strong> pts.</td>
        </tr>
      </tbody>
    </table>

    <!-- Cláusula oficial y firmas -->
    <div class="certificacion-texto">
      <strong>CONSTANCIA OFICIAL:</strong> La Secretaría del centro educativo certifica que los datos aquí consignados concuerdan con los registros y libros de actas de calificaciones vigentes en el archivo general del plantel.
    </div>

    <div class="firmas-container">
      <table class="firmas-table">
        <tr>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Secretaría de Control Académico</div>
            <div class="dep-firma">Registro y Certificación Escolar</div>
          </td>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Dirección General del Plantel</div>
            <div class="dep-firma">Vo.Bo. Autoridad Educativa</div>
          </td>
        </tr>
      </table>
    </div>
  {% endif %}

</body>
</html>
