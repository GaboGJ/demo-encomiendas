<div class="container-fluid py-4 min-vh-80 d-flex align-items-center justify-content-center">
  <div class="row w-100 justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm border-radius-xl text-center p-4 p-md-5">
        <div class="icon icon-shape bg-gradient-warning shadow-warning border-radius-lg mx-auto mb-3 d-flex align-items-center justify-content-center">
          <i class="material-symbols-rounded text-white">lock</i>
        </div>
        <h4 class="font-weight-bolder text-dark mb-2">Caja cerrada</h4>
        <p class="text-sm text-secondary mb-4"><?= htmlspecialchars($mensaje) ?></p>
        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
          <a href="<?= rtrim(URL, '/') ?>/dashboard" class="btn btn-outline-secondary mb-0 border-radius-md w-100 w-sm-auto">
            <i class="material-symbols-rounded text-sm align-middle me-1">arrow_back</i> Volver
          </a>
          <a href="<?= rtrim(URL, '/') ?>/cajas" class="btn bg-gradient-success text-white mb-0 border-radius-md w-100 w-sm-auto">
            <i class="material-symbols-rounded text-sm align-middle me-1">lock_open</i> Aperturar caja
          </a>
        </div>
      </div>
    </div>
  </div>
</div>