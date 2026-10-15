<div class="row justify-content-center py-5">
  <div class="col-md-8 col-lg-6 text-center">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3 text-warning">
          <i class="bi bi-search" style="font-size: 3.5rem;"></i>
        </div>
        <h2 class="h4 fw-bold text-dark mb-3">Página o Recurso No Encontrado</h2>
        <div class="alert alert-light border text-start small mb-4">
          <p class="mb-2 fw-semibold text-secondary"><i class="bi bi-info-circle me-1"></i>Motivo del inconveniente:</p>
          <p class="mb-2">El elemento, alumno o página a la que intentas acceder no existe, ha sido trasladado o la dirección web indicada es incorrecta.</p>
          <hr class="my-2">
          <p class="mb-1 fw-semibold text-secondary"><i class="bi bi-check-circle me-1"></i>Sugerencias:</p>
          <ul class="mb-0 ps-3">
            <li>Revisa que el código o identificador en el enlace esté bien escrito.</li>
            <li>Si eres docente, verifica que el alumno o curso pertenezca a tus clases asignadas.</li>
            <li>Regresa a la sección principal para continuar navegando.</li>
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
