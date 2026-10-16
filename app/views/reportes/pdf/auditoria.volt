<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Informe de Auditoría de Calificaciones</title>
  <style>
    @page {
      margin: 12mm 14mm 16mm 14mm;
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 8.5px;
      color: #1a202c;
      line-height: 1.3;
    }

    /* Membrete institucional formal */
    .institucion-header {
      width: 100%;
      border-bottom: 2px solid #1e293b;
      padding-bottom: 5px;
      margin-bottom: 4px;
    }
    .institucion-nombre {
      font-size: 12px;
      font-weight: bold;
      color: #0f172a;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin: 0 0 2px 0;
    }
    .institucion-sub {
      font-size: 8px;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin: 0;
    }
    .documento-titulo-box {
      margin-top: 6px;
      margin-bottom: 8px;
      text-align: center;
    }
    .documento-titulo {
      font-size: 11px;
      font-weight: bold;
      color: #0f172a;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 0 0 2px 0;
    }
    .documento-subtitulo {
      font-size: 8px;
      color: #64748b;
      font-style: italic;
      margin: 0;
    }

    /* Metadatos */
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      background-color: #f8fafc;
      border: 1px solid #cbd5e1;
    }
    .meta-table td {
      padding: 4px 6px;
      font-size: 8px;
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

    /* Resumen de indicadores de auditoría */
    .resumen-tabla {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border: 1px solid #cbd5e1;
    }
    .resumen-tabla th {
      background-color: #f1f5f9;
      color: #334155;
      font-size: 7.5px;
      text-transform: uppercase;
      padding: 3px 5px;
      border: 1px solid #cbd5e1;
      text-align: center;
    }
    .resumen-tabla td {
      padding: 4px 5px;
      font-size: 8px;
      border: 1px solid #cbd5e1;
      text-align: center;
      background-color: #ffffff;
    }

    /* Tabla principal */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #cbd5e1;
      padding: 4px 5px;
      font-size: 8px;
    }
    table.data-table th {
      background-color: #1e293b;
      color: #ffffff;
      font-weight: bold;
      font-size: 7.5px;
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

    /* Badges y estados */
    .badge-crear {
      color: #166534;
      font-weight: bold;
    }
    .badge-editar {
      color: #b45309;
      font-weight: bold;
    }
    .badge-eliminar {
      color: #b91c1c;
      font-weight: bold;
    }
    .badge-alerta {
      color: #b91c1c;
      font-weight: bold;
      font-size: 7px;
      background-color: #fee2e2;
      border: 1px solid #fca5a5;
      padding: 1px 3px;
      border-radius: 2px;
      display: inline-block;
      margin-top: 1px;
    }
    .badge-cerrado {
      color: #475569;
      font-size: 7px;
      background-color: #f1f5f9;
      border: 1px solid #cbd5e1;
      padding: 1px 3px;
      border-radius: 2px;
      display: inline-block;
      margin-top: 1px;
    }

    /* Certificación y firmas */
    .certificacion-texto {
      margin-top: 10px;
      font-size: 7.5px;
      color: #64748b;
      line-height: 1.3;
      text-align: justify;
      border-top: 1px dashed #cbd5e1;
      padding-top: 4px;
    }
    .firmas-container {
      margin-top: 25px;
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
      margin-bottom: 3px;
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

    /* Pie de página institucional */
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
          Sistema de Control y Registro Escolar &bull; Registro Inmutable de Auditoría de Calificaciones &bull; República de Guatemala
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
          <div class="institucion-sub">Dirección General &bull; Departamento de Control Académico y Auditoría Interna</div>
        </td>
        <td style="width: 25%; border: none; padding: 0; text-align: right;">
          <div style="font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase;">Registro Inmutable</div>
          <div style="font-size: 8px; color: #64748b;">AUD-REG-2026</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Título del documento -->
  <div class="documento-titulo-box">
    <div class="documento-titulo">Informe Oficial de Auditoría y Trazabilidad de Calificaciones</div>
    <div class="documento-subtitulo">Historial pormenorizado de eventos de ingreso, modificación y revocación de notas escolares</div>
  </div>

  <!-- Metadatos de la consulta -->
  <table class="meta-table">
    <tr>
      <td class="meta-label">Acción Auditada:</td>
      <td class="meta-value"><strong>{{ filtrosAplicados['accion']|e }}</strong></td>
      <td class="meta-label">Estudiante Filtro:</td>
      <td class="meta-value">{{ filtrosAplicados['alumno']|e }}</td>
    </tr>
    <tr>
      <td class="meta-label">Usuario Operador:</td>
      <td class="meta-value">{{ filtrosAplicados['usuario']|e }}</td>
      <td class="meta-label">Semestre / Periodo:</td>
      <td class="meta-value">{{ filtrosAplicados['semestre']|e }}</td>
    </tr>
    <tr>
      <td class="meta-label">Rango de Fechas:</td>
      <td class="meta-value">{{ filtrosAplicados['fechas']|e }}</td>
      <td class="meta-label">Generado Por / Fecha:</td>
      <td class="meta-value">{{ usuario|e }} &bull; {{ fechaHora|e }}</td>
    </tr>
  </table>

  <!-- Resumen estadístico de auditoría -->
  <table class="resumen-tabla">
    <thead>
      <tr>
        <th>Total Eventos</th>
        <th>Creaciones</th>
        <th>Ediciones</th>
        <th>Eliminaciones</th>
        <th>Cambios Mayores (≥ 20 pts)</th>
        <th>En Semestre Cerrado</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>{{ resumen['total']|e }}</strong></td>
        <td style="color: #166534; font-weight: bold;">{{ resumen['crear']|e }}</td>
        <td style="color: #b45309; font-weight: bold;">{{ resumen['editar']|e }}</td>
        <td style="color: #b91c1c; font-weight: bold;">{{ resumen['eliminar']|e }}</td>
        <td style="color: #b91c1c; font-weight: bold;">{{ resumen['cambios_grandes']|e }}</td>
        <td style="color: #475569; font-weight: bold;">{{ resumen['semestre_cerrado']|e }}</td>
      </tr>
    </tbody>
  </table>

  <!-- Tabla de registros -->
  {% if auditorias is empty %}
    <p style="text-align: center; color: #64748b; margin-top: 25px; font-style: italic;">
      No se registran eventos de auditoría para los criterios de consulta especificados.
    </p>
  {% else %}
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 4%; text-align: center;">No.</th>
          <th style="width: 11%;">Fecha y Hora</th>
          <th style="width: 11%;">Operador / Rol</th>
          <th style="width: 17%;">Estudiante</th>
          <th style="width: 16%;">Asignatura y Sección</th>
          <th style="width: 11%;">Semestre</th>
          <th style="width: 7%; text-align: center;">Acción</th>
          <th style="width: 5%; text-align: center;">Ant.</th>
          <th style="width: 5%; text-align: center;">Nva.</th>
          <th style="width: 5%; text-align: center;">Dif.</th>
          <th style="width: 8%;">Motivo / Alertas</th>
        </tr>
      </thead>
      <tbody>
        {% for index, a in auditorias %}
          <tr>
            <td style="text-align: center; color: #475569;">{{ (index + 1)|e }}</td>
            <td style="color: #334155; font-size: 7.5px;">{{ a['created_at']|e }}</td>
            <td>
              <strong>{{ a['usuario_nombre']|e }}</strong>
              <div style="font-size: 7px; color: #64748b;">{{ a['usuario_rol']|e }}</div>
            </td>
            <td><strong>{{ a['alumno_nombre']|e }}</strong></td>
            <td>
              {{ a['curso_nombre']|e }}
              <div style="font-size: 7px; color: #64748b;">Sec. {{ a['seccion_nombre']|e }}</div>
            </td>
            <td>
              {{ a['semestre_nombre']|e }}
              {% if a['es_semestre_cerrado'] %}
                <div class="badge-cerrado">Cerrado</div>
              {% endif %}
            </td>
            <td style="text-align: center;">
              {% if a['accion'] == 'crear' %}
                <span class="badge-crear">Creación</span>
              {% elseif a['accion'] == 'editar' %}
                <span class="badge-editar">Edición</span>
              {% elseif a['accion'] == 'eliminar' %}
                <span class="badge-eliminar">Eliminación</span>
              {% else %}
                <span>{{ a['accion']|e }}</span>
              {% endif %}
            </td>
            <td style="text-align: center; color: #64748b;">{{ a['nota_anterior']|e }}</td>
            <td style="text-align: center; font-weight: bold;">{{ a['nota_nueva']|e }}</td>
            <td style="text-align: center;">
              {% if a['diferencia'] is not null %}
                <strong style="color: {% if a['diferencia'] > 0 %}#166534{% elseif a['diferencia'] < 0 %}#b91c1c{% else %}#475569{% endif %};">
                  {{ a['diferencia_str']|e }}
                </strong>
                {% if a['es_cambio_grande'] %}
                  <div class="badge-alerta">&ge; 20 pts</div>
                {% endif %}
              {% else %}
                <span style="color: #94a3b8;">&mdash;</span>
              {% endif %}
            </td>
            <td style="font-size: 7px; color: #334155;">
              {{ a['motivo']|e }}
            </td>
          </tr>
        {% endfor %}
      </tbody>
    </table>

    <!-- Certificación de autenticidad -->
    <div class="certificacion-texto">
      <strong>CERTIFICACIÓN DE INMUTABILIDAD:</strong> El presente informe de auditoría refleja fielmente los registros criptográficos y de bitácora transaccional almacenados de forma permanente en la base de datos institucional. Cualquier discrepancia o alteración técnica requerirá investigación formal ante el Comité Directivo de Control Escolar.
    </div>

    <!-- Firmas institucionales -->
    <div class="firmas-container">
      <table class="firmas-table">
        <tr>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Auditoría Interna y Sistemas</div>
            <div class="dep-firma">Control de Seguridad y Registros</div>
          </td>
          <td>
            <div class="linea-firma"></div>
            <div class="cargo-firma">Dirección General del Plantel</div>
            <div class="dep-firma">Vo.Bo. Autoridad Máxima</div>
          </td>
        </tr>
      </table>
    </div>
  {% endif %}

</body>
</html>
