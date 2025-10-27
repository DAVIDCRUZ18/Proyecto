<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_partido = intval($_POST['id_partido']);
    $id_usuario = $_SESSION['id_usuario'];
    
    // Verificar que el usuario sea el creador o capitán del equipo local
    $verificar = $conexion->prepare("
        SELECT p.id_creador, p.id_equipo_local
        FROM partidos p
        INNER JOIN equipo_jugadores ej ON ej.id_equipo = p.id_equipo_local
        WHERE p.id_partido = ? AND (p.id_creador = ? OR (ej.id_usuario = ? AND ej.es_capitan = 1))
    ");
    $verificar->bind_param("iii", $id_partido, $id_usuario, $id_usuario);
    $verificar->execute();
    
    if ($verificar->get_result()->num_rows > 0) {
        $cancelar = $conexion->prepare("UPDATE partidos SET estado = 'cancelado' WHERE id_partido = ?");
        $cancelar->bind_param("i", $id_partido);
        $cancelar->execute();
        
        $_SESSION['mensaje'] = "Partido cancelado exitosamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "No tienes permisos para cancelar este partido.";
        $_SESSION['tipo_mensaje'] = "error";
    }
}

header("Location: ../dashboard.php");
exit;