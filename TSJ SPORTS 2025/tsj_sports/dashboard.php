<?php
session_start();
include __DIR__ . '/php/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";
// usa aquí tu conexión existente

$id_usuario = $_SESSION['id_usuario'];  // ID del usuario logueado

// ================================
// 1. Verificar si el usuario ya tiene perfil
// ================================
$perfilQuery = $conexion->prepare("SELECT * FROM perfil WHERE id = ?");
$perfilQuery->bind_param("i", $id_usuario);
$perfilQuery->execute();
$perfilResult = $perfilQuery->get_result();

if ($perfilResult->num_rows === 0) {
    // Crear perfil automáticamente
    $insertPerfil = $conexion->prepare("INSERT INTO perfil (id, name) VALUES (?, ?)");
    $nombreDefault = "Jugador_" . $id_usuario;
    $insertPerfil->bind_param("is", $id_usuario, $nombreDefault);
    $insertPerfil->execute();
}

// ================================
// 2. Verificar si ya pertenece a un equipo
// ================================
$equipoQuery = $conexion->prepare("
    SELECT e.id_equipo, e.nombre_equipo
    FROM equipo_jugadores ej
    INNER JOIN equipos e ON e.id_equipo = ej.id_equipo
    WHERE ej.id_usuario = ?
");
$equipoQuery->bind_param("i", $id_usuario);
$equipoQuery->execute();
$equipoResult = $equipoQuery->get_result();

$perteneceEquipo = $equipoResult->num_rows > 0;
$equipo = $equipoResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - TSJ SPORTS</title>
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/dashboard.css">
</head>
<body>

<div>
  <h1>Bienvenido al Dashboard de TSJ SPORTS ⚽</h1>
  <p>Desde aquí puedes gestionar tu equipo, perfil y torneos.</p>
</div>

<?php if ($perteneceEquipo): ?>
  <div>
    <h2>🏆 Tu equipo actual: <?php echo htmlspecialchars($equipo['nombre_equipo']); ?></h2>
    <a href="/TSJ SPORTS 2025/tsj_sports/php/equipo.php?id=<?php echo $equipo['id_equipo']; ?>">Ir a mi equipo</a>
  </div>
<?php else: ?>
  <!-- Modal para crear o unirse a equipo -->
  <div id="modalEquipo" class="modal">
    <div class="modal-content">
      <h2>¡Bienvenido jugador! 👋</h2>
      <p>No estás en ningún equipo, elige una opción:</p>
      <button onclick="mostrarCrear()">Crear equipo</button>
      <button onclick="mostrarUnirse()">Unirse a equipo</button>

      <form id="crearForm" action="/TSJ SPORTS 2025/tsj_sports/php/crear_equipo.php" method="POST" style="display:none;">
        <input type="text" name="nombre_equipo" placeholder="Nombre del equipo" required>
        <button type="submit">Crear equipo</button>
      </form>

      <form id="unirseForm" action="/TSJ SPORTS 2025/tsj_sports/php/unirse_equipo.php" method="POST" style="display:none;">
        <input type="text" name="codigo_equipo" placeholder="ID o código del equipo" required>
        <button type="submit">Unirse</button>
      </form>
    </div>
  </div>
<?php endif; ?>

<script>
  const modal = document.getElementById('modalEquipo');
  <?php if (!$perteneceEquipo): ?>
    modal.style.display = 'flex';
  <?php endif; ?>

  function mostrarCrear() {
    document.getElementById('crearForm').style.display = 'block';
    document.getElementById('unirseForm').style.display = 'none';
  }

  function mostrarUnirse() {
    document.getElementById('unirseForm').style.display = 'block';
    document.getElementById('crearForm').style.display = 'none';
  }
</script>

</body>
</html>