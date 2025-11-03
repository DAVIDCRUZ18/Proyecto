<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Debes iniciar sesión para crear un torneo.");
}

// Verificar si el usuario es administrador (opcional)
$id_usuario = $_SESSION['id_usuario'];
// Aquí podrías validar si el usuario tiene rol de admin o permisos especiales

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_torneo = trim($_POST["nombre_torneo"]);
    $descripcion = trim($_POST["descripcion"]);
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    $costo = floatval($_POST["costo_inscripcion"]);
    $max_equipos = intval($_POST["max_equipos"]);

    // ==============================
    // VALIDACIONES
    // ==============================
    $errores = [];

    if (empty($nombre_torneo)) {
        $errores[] = "El nombre del torneo es obligatorio.";
    }
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $errores[] = "Debes ingresar ambas fechas.";
    } elseif (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        $errores[] = "La fecha de fin no puede ser anterior a la de inicio.";
    }
    if ($costo < 0) {
        $errores[] = "El costo de inscripción no puede ser negativo.";
    }
    if ($max_equipos < 2) {
        $errores[] = "El torneo debe tener al menos 2 equipos.";
    }

    // Si no hay errores, guardar en BD
    if (empty($errores)) {
        $stmt = $conexion->prepare("
            INSERT INTO torneos 
            (nombre_torneo, descripcion, fecha_inicio, fecha_fin, costo_inscripcion, max_equipos, equipos_inscritos, estado, creado_por)
            VALUES (?, ?, ?, ?, ?, ?, 0, 'inscripciones_abiertas', ?)
        ");
        $stmt->bind_param("ssssdis", $nombre_torneo, $descripcion, $fecha_inicio, $fecha_fin, $costo, $max_equipos, $id_usuario);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "✅ Torneo creado exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: ../dashboard.php");
            exit;
        } else {
            $errores[] = "Error al guardar el torneo: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Torneo</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<div class="container">
    <h1>🏆 Crear Nuevo Torneo</h1>

    <?php if (!empty($errores)): ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="form-torneo">
        <label>Nombre del Torneo:</label>
        <input type="text" name="nombre_torneo" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="3"></textarea>

        <label>Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" required>

        <label>Fecha de Fin:</label>
        <input type="date" name="fecha_fin" required>

        <label>Costo de Inscripción:</label>
        <input type="number" name="costo_inscripcion" step="0.01" value="0">

        <label>Máximo de Equipos:</label>
        <input type="number" name="max_equipos" min="2" required>

        <button type="submit" class="btn btn-primary">Crear Torneo</button>
        <a href="../dashboard.php" class="btn btn-secondary">← Volver</a>
    </form>
</div>
</body>
</html>
