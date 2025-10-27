<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $id_equipo = intval($_POST['id_equipo']);
    
    // Verificar que el equipo existe
    $checkEquipo = $conexion->prepare("SELECT * FROM equipos WHERE id_equipo = ? AND activo = 1");
    $checkEquipo->bind_param("i", $id_equipo);
    $checkEquipo->execute();
    
    if ($checkEquipo->get_result()->num_rows === 0) {
        $_SESSION['mensaje'] = "Equipo no encontrado.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../dashboard.php");
        exit;
    }
    
    // Verificar que no esté en otro equipo
    $checkMiembro = $conexion->prepare("SELECT * FROM equipo_jugadores WHERE id_usuario = ?");
    $checkMiembro->bind_param("i", $id_usuario);
    $checkMiembro->execute();
    
    if ($checkMiembro->get_result()->num_rows > 0) {
        $_SESSION['mensaje'] = "Ya perteneces a un equipo.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../dashboard.php");
        exit;
    }
    
    // Agregar al equipo
    $insert = $conexion->prepare("INSERT INTO equipo_jugadores (id_equipo, id_usuario, es_capitan) VALUES (?, ?, 0)");
    $insert->bind_param("ii", $id_equipo, $id_usuario);
    
    if ($insert->execute()) {
        $_SESSION['mensaje'] = "¡Te has unido al equipo exitosamente!";
        $_SESSION['tipo_mensaje'] = "success";
    }
}

header("Location: ../dashboard.php");
exit;