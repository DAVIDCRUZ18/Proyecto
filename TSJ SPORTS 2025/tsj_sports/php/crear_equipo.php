<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];
$nombre_equipo = $_POST['nombre_equipo'];

// Crear equipo
$stmt = $conexion->prepare("INSERT INTO equipos (nombre_equipo, id_capitan) VALUES (?, ?)");
$stmt->bind_param("si", $nombre_equipo, $id_usuario);
$stmt->execute();
$id_equipo = $stmt->insert_id;

// Agregar al usuario como capitán
$rel = $conexion->prepare("INSERT INTO equipo_jugadores (id_equipo, id_usuario, es_capitan) VALUES (?, ?, 1)");
$rel->bind_param("ii", $id_equipo, $id_usuario);
$rel->execute();

header("Location: /TSJ SPORTS 2025/tsj_sports/dashboard.php");
