<div class="row justify-content-center py-5">
  <div class="col-md-8 col-lg-6 text-center">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3 text-warning">
          <i class="bi bi-exclamation-triangle-fill" style="font-size: 3.5rem;"></i>
        </div>
        <h2 class="h4 fw-bold text-dark mb-3">Inconveniente al Procesar la Operación</h2>
        <div class="alert alert-light border text-start small mb-4">
          <p class="mb-2 fw-semibold text-secondary"><i class="bi bi-info-circle me-1"></i>Motivo del inconveniente:</p>
          <p class="mb-2">El sistema no pudo completar la acción en este momento. La información ha sido resguardada para proteger los registros académicos y notas existentes.</p>
          <hr class="my-2">
          <p class="mb-1 fw-semibold text-secondary"><i class="bi bi-check-circle me-1"></i>Qué debes hacer:</p>
          <ul class="mb-0 ps-3">
            <li>Verifica que los datos que intentas guardar no estén duplicados o incompletos.</li>
            <li>Si el semestre está cerrado o una materia ya fue evaluada, revisa el estado en el catálogo respectivo.</li>
            <li>Vuelve a intentarlo en unos instantes o regresa a la pantalla anterior.</li>
          </ul>
        </div>
        <div class="d-flex justify-content-center gap-2">
          <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Regresar
          </a>
          <a href="{{ url('')|e }}" class="btn btn-primary">
            <i class="bi bi-house-door me-1"></i>Ir al Inicio
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
