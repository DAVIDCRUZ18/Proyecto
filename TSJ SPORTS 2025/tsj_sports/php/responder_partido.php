<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];

// Si llegan parámetros para responder (aceptar o rechazar)
if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id_partido = intval($_GET['id']);
    $accion = $_GET['accion']; // aceptar o rechazar

    // Verificar que el usuario es capitán del visitante
    $checkQuery = $conexion->prepare("
        SELECT p.*, el.nombre_equipo AS equipo_local, ev.nombre_equipo AS equipo_visitante
        FROM partidos p
        INNER JOIN equipos el ON el.id_equipo = p.id_equipo_local
        INNER JOIN equipos ev ON ev.id_equipo = p.id_equipo_visitante
        WHERE p.id_partido = ?
          AND ev.id_capitan = ?
          AND p.estado = 'pendiente'
    ");
    $checkQuery->bind_param("ii", $id_partido, $id_usuario);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows === 0) {
        die("Error: No tienes permiso para responder esta invitación.");
    }

    $partido = $result->fetch_assoc();

    // Actualizar estado
    $nuevo_estado = ($accion === 'aceptar') ? 'aceptado' : 'rechazado';
    $mensaje_notif = ($accion === 'aceptar')
        ? "Tu invitación de partido fue aceptada"
        : "Tu invitación de partido fue rechazada";

    $updateQuery = $conexion->prepare("UPDATE partidos SET estado = ? WHERE id_partido = ?");
    $updateQuery->bind_param("si", $nuevo_estado, $id_partido);
    $updateQuery->execute();

    // Notificación al creador
    $notifQuery = $conexion->prepare("
        INSERT INTO notificaciones (id_usuario, tipo, mensaje, leida)
        VALUES (?, ?, ?, 0)
    ");
    $tipo_notif = "partido_" . $accion . "do";
    $notifQuery->bind_param("iss", $partido['id_creador'], $tipo_notif, $mensaje_notif);
    $notifQuery->execute();

    $_SESSION['mensaje'] = "Invitación " . $nuevo_estado . " correctamente.";
    header("Location: responder_partido.php");
    exit;
}

// Si no hay parámetros: mostrar invitaciones pendientes
$query = $conexion->prepare("
    SELECT p.id_partido, el.nombre_equipo AS equipo_local, ev.nombre_equipo AS equipo_visitante, p.fecha_partido
    FROM partidos p
    INNER JOIN equipos el ON el.id_equipo = p.id_equipo_local
    INNER JOIN equipos ev ON ev.id_equipo = p.id_equipo_visitante
    WHERE ev.id_capitan = ?
      AND p.estado = 'pendiente'
");
$query->bind_param("i", $id_usuario);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Invitaciones Recibidas</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border-bottom: 1px solid #ccc; text-align: center; }
        th { background: #333; color: white; }
        a.btn { padding: 6px 12px; text-decoration: none; border-radius: 6px; color: white; }
        .aceptar { background: #28a745; }
        .rechazar { background: #dc3545; }
        .no-data { text-align: center; padding: 30px; }
    </style>
</head>
<body>
    <h1>📬 Invitaciones Recibidas</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Equipo Local</th>
                <th>Equipo Visitante</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['equipo_local']); ?></td>
                    <td><?= htmlspecialchars($row['equipo_visitante']); ?></td>
                    <td><?= htmlspecialchars($row['fecha_partido']); ?></td>
                    <td>
                        <a href="responder_partido.php?id=<?= $row['id_partido']; ?>&accion=aceptar" class="btn aceptar">Aceptar</a>
                        <a href="responder_partido.php?id=<?= $row['id_partido']; ?>&accion=rechazar" class="btn rechazar">Rechazar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-data">🎉 No tienes invitaciones pendientes.</p>
    <?php endif; ?>
</body>
</html>