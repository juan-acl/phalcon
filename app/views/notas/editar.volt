<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0">
          <i class="bi bi-pencil-square me-2"></i>Editar Calificación — {{ alumno.nombres|e }} {{ alumno.apellidos|e }}
        </h5>
      </div>
      <div class="card-body p-4">
        {% if errores is defined and errores|length > 0 %}
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
              {% for err in errores %}
                <li>{{ err|e }}</li>
              {% endfor %}
            </ul>
          </div>
        {% endif %}

        <div class="bg-light p-3 rounded mb-4 border">
          <div class="row g-2 text-muted small">
            <div class="col-sm-6">
              <strong>Curso:</strong> <span class="text-dark">{{ curso.nombre|e }}</span>
            </div>
            <div class="col-sm-6">
              <strong>Semestre:</strong> <span class="text-dark">{{ semestre.nombre|e }}</span>
              {% if semestre.cerrado %}
                <span class="badge bg-danger ms-1">Cerrado</span>
              {% endif %}
            </div>
            <div class="col-sm-6">
              <strong>Sección:</strong> <span class="text-dark">{{ seccion.nombre|e }}</span>
            </div>
            <div class="col-sm-6">
              <strong>Nota Actual:</strong> <span class="text-dark fw-bold">{{ nota.nota|e }} pts</span>
            </div>
          </div>
        </div>

        <form method="post" action="{{ url('notas/' ~ nota.id ~ '/actualizar') }}" autocomplete="off">
          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}">
          <input type="hidden" name="updated_at_token" value="{{ nota.updated_at|default('')|e }}">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="nota" class="form-label fw-semibold">Nueva Calificación (0 - 100)</label>
              <input type="number"
                     name="nota"
                     id="nota"
                     class="form-control"
                     min="0"
                     max="100"
                     step="0.01"
                     value="{{ old['nota']|default(nota.nota)|e }}"
                     required
                     autofocus>
            </div>
            <div class="col-md-6">
              <label for="estado" class="form-label fw-semibold">Estado de Publicación</label>
              <select name="estado" id="estado" class="form-select">
                <option value="publicada" {% if old['estado']|default(nota.estado) == 'publicada' %}selected{% endif %}>
                  Publicada (visible para estudiante)
                </option>
                <option value="borrador" {% if old['estado']|default(nota.estado) == 'borrador' %}selected{% endif %}>
                  Borrador (oculta para estudiante)
                </option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label for="motivo" class="form-label fw-semibold">
              Motivo de la Modificación <span class="text-danger">*</span>
            </label>
            <textarea name="motivo"
                      id="motivo"
                      rows="3"
                      class="form-control"
                      placeholder="Indique la justificación académica o corrección administrativa (mínimo 10 caracteres)..."
                      minlength="10"
                      required>{{ old['motivo']|default('')|e }}</textarea>
            <div class="form-text">El motivo quedará registrado permanentemente en la bitácora de auditoría.</div>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('alumnos/' ~ alumno.id) }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-save me-1"></i> Guardar Modificación
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Historial de Auditoría -->
    {% if auditorias is defined and auditorias|length > 0 %}
      <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-2">
          <h6 class="mb-0"><i class="bi bi-clock-history me-1"></i>Historial de Auditoría de esta Nota</h6>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0 small">
            <thead>
              <tr>
                <th>Fecha/Hora</th>
                <th>Acción</th>
                <th>Anterior</th>
                <th>Nueva</th>
                <th>Motivo</th>
                <th>Usuario</th>
              </tr>
            </thead>
            <tbody>
              {% for a in auditorias %}
                <tr>
                  <td>{{ a.created_at|e }}</td>
                  <td>
                    <span class="badge {% if a.accion == 'crear' %}bg-success{% elseif a.accion == 'editar' %}bg-warning text-dark{% else %}bg-danger{% endif %}">
                      {{ a.accion|e }}
                    </span>
                  </td>
                  <td>{{ a.nota_anterior|default('—')|e }}</td>
                  <td>{{ a.nota_nueva|default('—')|e }}</td>
                  <td>{{ a.motivo|default('—')|e }}</td>
                  <td>{{ a.usuario ? a.usuario.usuario|e : 'Sistema'|e }}</td>
                </tr>
              {% endfor %}
            </tbody>
          </table>
        </div>
      </div>
    {% endif %}
  </div>
</div>
