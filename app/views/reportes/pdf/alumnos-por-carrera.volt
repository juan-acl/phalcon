<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte General de Alumnos por Carrera</title>
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

    /* Membrete institucional formal */
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
      font-size: 9px;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin: 0;
    }
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
      margin: 0 0 3px 0;
    }
    .documento-subtitulo {
      font-size: 9px;
      color: #64748b;
      font-style: italic;
      margin: 0;
    }

    /* Tabla de metadatos del informe */
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
      background-color: #f8fafc;
      border: 1px solid #cbd5e1;
    }
    .meta-table td {
      padding: 5px 8px;
      font-size: 9px;
      border: 1px solid #e2e8f0;
    }
    .meta-label {
      font-weight: bold;
      color: #334155;
      text-transform: uppercase;
      font-size: 8px;
      letter-spacing: 0.3px;
      width: 20%;
    }
    .meta-value {
      color: #0f172a;
      width: 30%;
    }

    /* Tabla principal de datos */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
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
      font-size: 8.5px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
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

    /* Resumen formal inferior */
    .resumen-tabla {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
      border: 1px solid #cbd5e1;
    }
    .resumen-tabla td {
      padding: 6px 10px;
      font-size: 9px;
      border: 1px solid #e2e8f0;
      background-color: #f8fafc;
    }

    /* Cláusula y firmas oficiales */
    .certificacion-texto {
      margin-top: 16px;
      font-size: 8px;
      color: #64748b;
      line-height: 1.35;
      text-align: justify;
      border-top: 1px dashed #cbd5e1;
      padding-top: 6px;
    }
    .firmas-container {
      margin-top: 35px;
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
      padding: 0 25px;
    }
    .linea-firma {
      border-top: 1px solid #334155;
      margin-bottom: 4px;
    }
    .cargo-firma {
      font-size: 8.5px;
      color: #334155;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .dep-firma {
      font-size: 8px;
      color: #64748b;
    }

    /* Pie de página institucional */
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
      Sistema de Control y Registro Escolar &bull; Documento Oficial de Consulta Interna &bull; República de Guatemala
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
          <div class="institucion-sub">Dirección General &bull; Departamento de Control Académico y Registro Escolar</div>
        </td>
        <td style="width: 25%; border: none; padding: 0; text-align: right;">
          <div style="font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase;">Control Oficial</div>
          <div style="font-size: 8px; color: #64748b;">REG-EXP-2026</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Título del documento -->
  <div class="documento-titulo-box">
    <div class="documento-titulo">Nómina General de Estudiantes por Carrera</div>
    <div class="documento-subtitulo">Constancia oficial de matrícula e inscripción escolar correspondiente al ciclo lectivo vigente</div>
  </div>

  <!-- Ficha técnica del informe -->
  <table class="meta-table">
    <tr>
      <td class="meta-label">Carrera / Programa:</td>
      <td class="meta-value"><strong>{{ carreraNombre|e }}</strong></td>
      <td class="meta-label">Fecha de Expedición:</td>
      <td class="meta-value">{{ fechaHora|e }}</td>
    </tr>
    <tr>
      <td class="meta-label">Total Alumnos:</td>
      <td class="meta-value"><strong>{{ alumnos|length|e }}</strong> estudiante(s)</td>
      <td class="meta-label">Emitido por:</td>
      <td class="meta-value">{{ usuario|e }}</td>
    </tr>
  </table>

  <!-- Tabla de resultados -->
  {% if alumnos is empty %}
    <p style="text-align: center; color: #64748b; margin-top: 30px; font-style: italic;">
      No se registran estudiantes matriculados bajo los criterios de búsqueda seleccionados.
    </p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 6%; text-align: center;">No.</th>
          <th style="width: 34%;">Carrera / Especialidad</th>
          <th style="width: 44%;">Apellidos y Nombres del Estudiante</th>
          <th style="width: 16%; text-align: center;">Fecha Nacimiento</th>
        </tr>
      </thead>
      <tbody>
        {% for index, a in alumnos %}
          <tr>
            <td style="text-align: center; color: #475569;">{{ (index + 1)|e }}</td>
            <td>{{ a['carrera']|e }}</td>
            <td><strong>{{ a['apellidos']|e }}</strong>, {{ a['nombres']|e }}</td>
            <td style="text-align: center; color: #334155;">{{ a['fecha_nacimiento']|e }}</td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <!-- Resumen institucional -->
    <table class="resumen-tabla">
      <tr>
        <td style="width: 50%;">
          <strong>Consolidado de Matrícula:</strong> Total de <strong>{{ alumnos|length|e }}</strong> estudiante(s) registrado(s) en la nómina.
        </td>
        <td style="width: 50%; text-align: right; color: #475569;">
          Estado de Registro: <strong>Vigente y Conforme</strong>
        </td>
      </tr>
    </table>

    <!-- Cláusula de validez oficial -->
    <div class="certificacion-texto">
      <strong>CERTIFICACIÓN OFICIAL:</strong> La presente nómina estudiantil certifica la matrícula de los alumnos inscritos en los registros oficiales de este centro educativo. Todo tachón, enmienda o alteración anula la validez del presente informe.
    </div>

    <!-- Firmas oficiales de secretaría y dirección -->
    <div class="firmas-container">
      <table class="firmas-table">
        <tr>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Secretaría de Control Académico</div>
            <div class="dep-firma">Registro y Archivo Escolar</div>
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
