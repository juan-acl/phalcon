<div class="row justify-content-center py-5">
  <div class="col-md-8 col-lg-6 text-center">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3 text-primary">
          <i class="bi bi-lock-fill" style="font-size: 3.5rem;"></i>
        </div>
        <h2 class="h4 fw-bold text-dark mb-3">Inicio de Sesión Requerido</h2>
        <div class="alert alert-light border text-start small mb-4">
          <p class="mb-2 fw-semibold text-secondary"><i class="bi bi-info-circle me-1"></i>Motivo del inconveniente:</p>
          <p class="mb-2">Para ingresar a este apartado del sistema escolar debes identificarte con tu cuenta institucional de alumno, docente o dirección.</p>
          <hr class="my-2">
          <p class="mb-1 fw-semibold text-secondary"><i class="bi bi-check-circle me-1"></i>Qué debes hacer:</p>
          <ul class="mb-0 ps-3">
            <li>Haz clic en el botón de abajo para identificarte con tu usuario y contraseña.</li>
            <li>Si no recuerdas tu clave de acceso, contacta a la administración para restablecerla.</li>
          </ul>
        </div>
        <div class="d-flex justify-content-center gap-2">
          <a href="{{ url('login')|e }}" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
          </a>
          <a href="{{ url('')|e }}" class="btn btn-outline-secondary">
            <i class="bi bi-house-door me-1"></i>Ir al Inicio
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
