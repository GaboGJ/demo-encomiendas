<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0 d-flex align-items-center" href="<?= URL; ?>/dashboard">
      <span class="material-symbols-rounded text-success fs-3 me-2">local_shipping</span>
      <span class="ms-1 font-weight-bold text-dark text-sm">TransExpress</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-2">
  
  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <!-- PANEL PRINCIPAL -->
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'dashboard') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/dashboard">
          <i class="material-symbols-rounded opacity-5">dashboard</i>
          <span class="nav-link-text ms-1">Panel Principal</span>
        </a>
      </li>

      <!-- GESTIÓN OPERATIVA -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Gestión Operativa</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'encomiendas') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/encomiendas">
          <i class="material-symbols-rounded opacity-5">inventory_2</i>
          <span class="nav-link-text ms-1">Encomiendas (POS)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'pasajes') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/pasajes">
          <i class="material-symbols-rounded opacity-5">confirmation_number</i>
          <span class="nav-link-text ms-1">Pasajes (POS)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'despachos') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/despachos">
          <i class="material-symbols-rounded opacity-5">departure_board</i>
          <span class="nav-link-text ms-1">Despachos</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'cajas') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/cajas">
          <i class="material-symbols-rounded opacity-5">payments</i>
          <span class="nav-link-text ms-1">Cajas</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'moviles') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/moviles">
          <i class="material-symbols-rounded opacity-5">directions_bus</i>
          <span class="nav-link-text ms-1">Móviles</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'choferes') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/choferes">
          <i class="material-symbols-rounded opacity-5">badge</i>
          <span class="nav-link-text ms-1">Choferes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'rutas') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/rutas">
          <i class="material-symbols-rounded opacity-5">alt_route</i>
          <span class="nav-link-text ms-1">Rutas</span>
        </a>
      </li>

      <!-- CONFIGURACIÓN E INSTITUCIONAL -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Administración</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'empresa') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/empresa">
          <i class="material-symbols-rounded opacity-5">apartment</i>
          <span class="nav-link-text ms-1">Empresa</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'sindicatos') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/sindicatos">
          <i class="material-symbols-rounded opacity-5">domain</i>
          <span class="nav-link-text ms-1">Sindicatos Asociados</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'modelos') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/modelos">
          <i class="material-symbols-rounded opacity-5">car_rental</i>
          <span class="nav-link-text ms-1">Modelos de Vehículos</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'usuarios') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/usuarios">
          <i class="material-symbols-rounded opacity-5">group</i>
          <span class="nav-link-text ms-1">Gestión de Usuarios</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'roles') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/roles">
          <i class="material-symbols-rounded opacity-5">admin_panel_settings</i>
          <span class="nav-link-text ms-1">Roles y Permisos</span>
        </a>
      </li>

      <!-- REPORTES -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Reportes</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (isset($menuActivo) && $menuActivo == 'reportes') ? 'active bg-gradient-success text-white' : 'text-dark'; ?>" href="<?= URL; ?>/reportes">
          <i class="material-symbols-rounded opacity-5">analytics</i>
          <span class="nav-link-text ms-1">Reportes Operativos</span>
        </a>
      </li>
    </ul>
  </div>

  <div class="sidenav-footer position-absolute w-100 bottom-0">
    <div class="mx-3">
      <a class="btn btn-outline-success mt-2 w-100" href="javascript:;">
        <i class="material-symbols-rounded text-sm me-1 align-middle">menu_book</i> Guía
      </a>
      <a class="btn bg-gradient-success w-100" href="javascript:;">
        <i class="material-symbols-rounded text-sm me-1 align-middle">support_agent</i> Soporte
      </a>
    </div>
  </div>
</aside>