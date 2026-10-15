<div class="row justify-content-center py-5">
  <div class="col-md-8 col-lg-6 text-center">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3 text-danger">
          <i class="bi bi-shield-x" style="font-size: 3.5rem;"></i>
        </div>
        <h2 class="h4 fw-bold text-dark mb-3">Acceso Restringido o Formulario Expirado</h2>
        <div class="alert alert-light border text-start small mb-4">
          <p class="mb-2 fw-semibold text-secondary"><i class="bi bi-info-circle me-1"></i>Motivo del inconveniente:</p>
          <p class="mb-2">No se pudo procesar la solicitud debido a que no tienes los permisos requeridos para esta sección, o porque el formulario estuvo inactivo demasiado tiempo y expiró por seguridad.</p>
          <hr class="my-2">
          <p class="mb-1 fw-semibold text-secondary"><i class="bi bi-check-circle me-1"></i>Qué debes hacer:</p>
          <ul class="mb-0 ps-3">
            <li>Si estabas completando un formulario, vuelve a la pantalla anterior, recarga la página y vuelve a intentar guardar.</li>
            <li>Si necesitas acceder a una función académica exclusiva, solicita la habilitación a la dirección escolar.</li>
          </ul>
        </div>
        <div class="d-flex justify-content-center gap-2">
          <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Formulario
          </a>
          <a href="{{ url('')|e }}" class="btn btn-primary">
            <i class="bi bi-house-door me-1"></i>Ir al Inicio
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
