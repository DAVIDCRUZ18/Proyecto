<!-- ===============================
// Validar sesión de usuario
=============================== -->
<?php include __DIR__ . '/../php/validar_sesion.php'; ?>

<!-- ===============================
     HEADER / NAVBAR
=============================== -->
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TSJ SPORTS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Estilos del Navbar -->
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/header.css">
</head>

<nav class="navbar">
  <div class="navbar-container">
    <a href="/TSJ SPORTS 2025/tsj_sports/dashboard.php" class="logo">TSJ SPORTS</a>

    <ul class="nav-menu">
      <li class="nav-item">
        <a href="/TSJ SPORTS 2025/tsj_sports/mapa/map.php">
          <i class="fas fa-map-marked-alt"></i> Mapa
        </a>
      </li>
      <!-- <li class="nav-item">
        <a href="/TSJ SPORTS 2025/tsj_sports/chat/chat.php">
          <i class="fas fa-comments"></i> Chat
        </a>
      </li>-->
      <li class="nav-item"> 
        <a href="/TSJ SPORTS 2025/tsj_sports/equipos/equipos.php">
          <i class="fas fa-users"></i> Mi Equipo
        </a>
      </li>
      <li class="nav-item">
        <a href="/TSJ SPORTS 2025/tsj_sports/Perfil/perfil.php">
          <i class="fas fa-user-circle"></i> Perfil
        </a>
      </li>
      <!-- <li class="nav-item">
        <a href="#contacto">
          <i class="fas fa-address-book"></i> Contactos
        </a>
      </li> -->

      <!-- Botón de Cerrar Sesión -->
      <li class="nav-item logout-item">
        <form action="/TSJ SPORTS 2025/tsj_sports/php/logout.php" method="POST">
          <button type="submit" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </button>
        </form>
      </li>
    </ul>

    <!-- Menú hamburguesa (Responsive) -->
    <div class="hamburger">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </div>
  </div>
</nav>

<!-- ===============================
     SCRIPT - MENÚ RESPONSIVE
=============================== -->
<script defer>
  document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".nav-menu");

    if (hamburger && navMenu) {
      hamburger.addEventListener("click", () => {
        navMenu.classList.toggle("active");
        hamburger.classList.toggle("active");
      });
    }
  });
</script>