<?php
include 'conexion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_equipo = $_GET['id'] ?? 0;

// Obtener info del equipo
$sql_equipo = "SELECT * FROM equipos WHERE id = $id_equipo";
$equipo = $conn->query($sql_equipo)->fetch_assoc();

// Obtener jugadores del equipo
$sql_jugadores = "SELECT * FROM jugadores WHERE equipo_id = $id_equipo";
$jugadores = $conn->query($sql_jugadores);

// Obtener canchas disponibles
$sql_canchas = "SELECT * FROM canchas";
$canchas = $conn->query($sql_canchas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($equipo['nombre']); ?> - TSJ SPORTS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/stylesP.css">
</head>
<body class="p-4">

  <h1>Equipo: <?php echo htmlspecialchars($equipo['nombre']); ?></h1>
  <p><?php echo htmlspecialchars($equipo['descripcion']); ?></p>

  <h3>Integrantes</h3>
  <ul>
    <?php while($jug = $jugadores->fetch_assoc()): ?>
      <li><?php echo htmlspecialchars($jug['nombre']); ?> (<?php echo htmlspecialchars($jug['correo']); ?>)</li>
    <?php endwhile; ?>
  </ul>

  <hr>

  <h3>Crear partido</h3>
  <form action="crear_partido.php" method="POST" class="mt-3">
    <input type="hidden" name="equipo_id" value="<?php echo $id_equipo; ?>">

    <div class="mb-3">
      <label for="cancha">Cancha:</label>
      <select name="cancha_id" class="form-select" required>
        <option value="">Seleccione una cancha</option>
        <?php while($cancha = $canchas->fetch_assoc()): ?>
          <option value="<?php echo $cancha['id']; ?>">
            <?php echo htmlspecialchars($cancha['nombre']); ?> — (<?php echo $cancha['latitud']; ?>, <?php echo $cancha['longitud']; ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="fecha">Fecha y hora:</label>
      <input type="datetime-local" name="fecha" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Crear partido</button>
  </form>

</body>
</html>
<h3 class="mt-5">Próximos partidos</h3>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Cancha</th>
      <th>Fecha</th>
      <th>Estado</th>
      <th>Ver ubicación</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $sql_partidos = "SELECT p.*, c.nombre AS cancha, c.latitud, c.longitud 
                     FROM partidos p 
                     JOIN canchas c ON p.cancha_id = c.id 
                     WHERE p.equipo_id = $id_equipo
                     ORDER BY p.fecha ASC";
    $partidos = $conn->query($sql_partidos);
    while ($p = $partidos->fetch_assoc()):
    ?>
      <tr>
        <td><?php echo htmlspecialchars($p['cancha']); ?></td>
        <td><?php echo htmlspecialchars($p['fecha']); ?></td>
        <td><?php echo htmlspecialchars($p['estado']); ?></td>
        <td><a href="https://www.google.com/maps?q=<?php echo $p['latitud']; ?>,<?php echo $p['longitud']; ?>" target="_blank">Ver en Maps</a></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
