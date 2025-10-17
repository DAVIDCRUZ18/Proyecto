<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/conexion.php";

$id_usuario = $_SESSION['id_usuario'];
$id_equipo = intval($_POST['codigo_equipo']); // puedes mejorar esto si usas códigos de invitación

// Unirse al equipo
$stmt = $conexion->prepare("INSERT INTO equipo_jugadores (id_equipo, id_usuario) VALUES (?, ?)");
$stmt->bind_param("ii", $id_equipo, $id_usuario);
$stmt->execute();

header("Location: /TSJ SPORTS 2025/tsj_sports/dashboard.php");
