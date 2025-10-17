<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

// Verificar si llegó el ID por GET
if (!isset($_GET['id'])) {
    die("ID de equipo no proporcionado.");
}

$id_equipo = intval($_GET['id']);

// Consultar información del equipo
$sql = "SELECT * FROM equipos WHERE id_equipo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_equipo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("El equipo no existe.");
}

$equipo = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipo <?php echo htmlspecialchars($equipo['nombre_equipo']); ?></title>
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php"; ?>

    <div>
        <h1><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></h1>