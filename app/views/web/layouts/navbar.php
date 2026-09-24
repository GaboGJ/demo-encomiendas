  <!-- NAVBAR CON OPCIÓN RESPONSIVA COMPLETA -->
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
        <nav class="navbar navbar-expand-lg blur border-radius-xl top-0 z-index-3 shadow position-absolute my-3 py-2 start-0 end-0 mx-3 mx-md-4 bg-white">
          <div class="container-fluid ps-2 pe-0">
            <!-- Nombre corto -->
            <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 d-flex align-items-center text-success" href="#">
              <span class="material-symbols-rounded text-success me-2">local_shipping</span> TransExpress
            </a>
            
            <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon mt-2">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </span>
            </button>
            
            <div class="collapse navbar-collapse w-100 pt-3 pb-2 py-lg-0" id="navigation">
              <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center me-2 text-dark font-weight-bold" href="#">
                    <span class="material-symbols-rounded opacity-8 me-1 text-sm">home</span> Inicio
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link me-2 text-dark font-weight-bold d-flex align-items-center" href="#">
                    <span class="material-symbols-rounded opacity-8 me-1 text-sm">search</span> Rastreo
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link me-2 text-dark font-weight-bold d-flex align-items-center" href="#">
                    <span class="material-symbols-rounded opacity-8 me-1 text-sm">route</span> Rutas
                  </a>
                </li>
              </ul>
              
              <!-- Botón Versión Escritorio -->
              <ul class="navbar-nav d-lg-flex d-none align-items-center gap-2">
                <li class="nav-item">
                  <button type="button" onclick="verLogin()" class="btn btn-sm mb-0 bg-gradient-success border-radius-md px-4 text-white">
                    <span class="material-symbols-rounded text-sm me-1 align-middle">login</span> Acceso
                  </button>
                </li>
              </ul>

              <!-- Sección Adaptada para Dispositivos Móviles -->
              <div class="d-lg-none py-3 px-2 border-top mt-2">
                <button type="button" onclick="verLogin()" class="btn btn-sm bg-gradient-success mb-2 w-100 text-white">
                  <span class="material-symbols-rounded me-1 align-middle text-sm">login</span> Iniciar Sesión
                </button>
                <button type="button" onclick="
                ()" class="btn btn-sm btn-outline-success mb-0 w-100">
                  <span class="material-symbols-rounded me-1 align-middle text-sm">person_add</span> Registrarse
                </button>
              </div>

            </div>
          </div>
        </nav>
      </div>
    </div>
  </div>