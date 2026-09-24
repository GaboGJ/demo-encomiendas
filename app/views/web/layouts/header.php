<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="<?= URL ?>/public/assets/img/favicon.png">
  <title>TransExpress - Acceso y Registro</title>

  <!-- Fuentes e Iconos -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="<?= URL ?>/public/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="<?= URL ?>/public/assets/css/nucleo-svg.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

  <!-- CSS Material Dashboard 3 -->
  <link id="pagestyle" href="<?= URL ?>/public/assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
    }

    main {
      flex: 1 0 auto;
    }

    .contenedor_todo {
      width: 100%;
      max-width: 1050px;
      margin: 20px auto;
      position: relative;
    }

    .caja_trasera {
      width: 100%;
      height: 540px;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 1rem !important;
      position: relative;
    }

    .caja_trasera-login, 
    .caja_trasera-registro {
      width: 50%;
      padding: 20px;
      text-align: center;
      color: white;
      transition: all 500ms ease;
      z-index: 1;
    }

    .contenedor_login-deslizable {
      display: flex;
      align-items: center;
      width: 50%;
      height: calc(100% + 40px);
      position: absolute;
      top: -20px; 
      left: 0;
      z-index: 2;
      transition: left 500ms cubic-bezier(0.175, 0.885, 0.320, 1.275);
    }

    .card-auth-container {
      width: 100%;
      height: 100%;
      padding: 24px;
      background: #ffffff;
      border-radius: 1rem !important;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(76, 175, 80, 0.4) !important;
      overflow-y: hidden !important;
    }

    .custom-nav-wrapper {
      position: relative;
      background-color: #f8f9fa;
      padding: 4px;
      border-radius: 0.5rem;
      width: 100%;
      box-sizing: border-box;
    }

    .custom-nav-wrapper .nav-pills {
      position: relative;
      display: flex;
      margin-bottom: 0;
      padding-left: 0;
      list-style: none;
      width: 100%;
    }

    .custom-nav-wrapper .nav-pills .nav-item {
      flex: 1 1 0%;
      text-align: center;
      z-index: 2;
      min-width: 0;
    }

    .custom-nav-wrapper .nav-pills .nav-link {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 0.5rem 1rem;
      border: 0;
      background: transparent;
      color: #67748e;
      font-weight: 600;
      font-size: 0.875rem;
      border-radius: 0.375rem;
      transition: color 0.3s ease;
      cursor: pointer;
      white-space: nowrap;
    }

    .custom-nav-wrapper .nav-pills .nav-link.active {
      color: #2e7d32 !important;
      background-color: transparent !important;
    }

    .custom-nav-wrapper .moving-tab {
      position: absolute;
      top: 0;
      left: 0;
      background-color: #ffffff;
      border-radius: 0.375rem;
      box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.12);
      z-index: 1;
      transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), width 0.35s cubic-bezier(0.25, 1, 0.5, 1), height 0.35s cubic-bezier(0.25, 1, 0.5, 1);
      pointer-events: none;
    }

    .input-custom {
      border: 1px solid #d2d6da;
      border-radius: 0.375rem;
      padding: 0.35rem 0.65rem;
      font-size: 0.8125rem;
      width: 100%;
      outline: none;
      transition: all 0.2s ease;
    }
    .input-custom:focus {
      border-color: #4caf50;
      box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.25);
    }
    .label-custom {
      font-size: 0.75rem;
      font-weight: 700;
      color: #344767;
      margin-bottom: 2px;
      display: block;
    }

    @media (max-width: 850px) {
      .contenedor_todo {
        margin: 10px auto;
        padding: 0 10px;
      }
      .caja_trasera {
        height: auto;
        padding: 15px;
        flex-direction: column;
        border-radius: 1rem !important;
      }
      .caja_trasera-login, 
      .caja_trasera-registro {
        width: 100%;
        padding: 10px;
      }
      .contenedor_login-deslizable {
        width: 100%;
        height: auto;
        position: relative;
        top: 0;
        left: 0 !important;
      }
      .card-auth-container {
        padding: 20px 15px;
        border-radius: 1rem !important;
        overflow-y: auto !important;
      }
    }
  </style>
</head>

<body class="bg-gray-100">