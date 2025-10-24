<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];
$id_partido = intval($_POST['id_partido']);

// Verificar que el usuario es el creador del partido
$checkQuery = $conexion->prepare("
    SELECT p.id_creador, p.estado, p.id_equipo_local
    FROM partidos p
    INNER JOIN equipo_jugadores ej ON ej.id_equipo = p.id_equipo_local
    WHERE p.id_partido = ? AND ej.id_usuario = ? AND ej.es_capitan = 1
");
$checkQuery->bind_param("ii", $id_partido, $id_usuario);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows === 0) {
    $_SESSION['mensaje'] = "No tienes permiso para cancelar este partido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: crear_partido.php");
    exit;
}

$partido = $result->fetch_assoc();

// Solo se pueden cancelar partidos pendientes
if ($partido['estado'] !== 'pendiente') {
    $_SESSION['mensaje'] = "Solo puedes cancelar partidos que estén pendientes de confirmación.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: crear_partido.php");
    exit;
}

// Cancelar el partido
$updateQuery = $conexion->prepare("UPDATE partidos SET estado = 'cancelado' WHERE id_partido = ?");
$updateQuery->bind_param("i", $id_partido);

if ($updateQuery->execute()) {
    $_SESSION['mensaje'] = "Partido cancelado exitosamente.";
    $_SESSION['tipo_mensaje'] = "success";
} else {
    $_SESSION['mensaje'] = "Error al cancelar el partido.";
    $_SESSION['tipo_mensaje'] = "error";
}

header("Location: crear_partido.php");
exit;