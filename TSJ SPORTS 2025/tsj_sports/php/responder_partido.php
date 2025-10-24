<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];
$id_partido = intval($_GET['id']);
$accion = $_GET['accion']; // 'aceptar' o 'rechazar'

// Verificar que el usuario es capitán del equipo visitante
$checkQuery = $conexion->prepare("
    SELECT p.*, el.nombre_equipo as equipo_local
    FROM partidos p
    INNER JOIN equipos el ON el.id_equipo = p.id_equipo_local
    INNER JOIN equipo_jugadores ej ON ej.id_equipo = p.id_equipo_visitante
    WHERE p.id_partido = ? AND ej.id_usuario = ? AND ej.es_capitan = 1 AND p.estado = 'pendiente'
");
$checkQuery->bind_param("ii", $id_partido, $id_usuario);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows === 0) {
    die("Error: No tienes permiso para responder esta invitación.");
}

$partido = $result->fetch_assoc();

// Actualizar estado
if ($accion === 'aceptar') {
    $nuevo_estado = 'aceptado';
    $mensaje_notif = "Tu invitación de partido fue aceptada";
} else {
    $nuevo_estado = 'rechazado';
    $mensaje_notif = "Tu invitación de partido fue rechazada";
}

$updateQuery = $conexion->prepare("UPDATE partidos SET estado = ? WHERE id_partido = ?");
$updateQuery->bind_param("si", $nuevo_estado, $id_partido);
$updateQuery->execute();

// Notificar al creador
$notifQuery = $conexion->prepare("
    INSERT INTO notificaciones (id_usuario, tipo, mensaje, leida) 
    VALUES (?, 'partido_" . $accion . "do', ?, 0)
");
$notifQuery->bind_param("is", $partido['id_creador'], $mensaje_notif);
$notifQuery->execute();

$_SESSION['mensaje'] = $accion === 'aceptar' ? 
    "¡Partido aceptado! Ya está confirmado en tu calendario." : 
    "Invitación rechazada.";
$_SESSION['tipo_mensaje'] = "success";

header("Location: dashboard.php");
exit;