<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];
$equipo_id = $_POST['equipo_id'] ?? 0;
$cancha_id = $_POST['cancha_id'] ?? 0;
$fecha = $_POST['fecha'] ?? null;

// Verificar que el usuario pertenece al equipo
$check = $conexion->prepare("SELECT 1 FROM equipo_jugadores WHERE id_usuario = ? AND id_equipo = ?");
$check->bind_param("ii", $id_usuario, $equipo_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
  echo "Error: No puedes crear partidos para un equipo al que no perteneces.";
  exit;
}

// Crear partido
$sql = $conexion->prepare("INSERT INTO partidos (equipo_id, cancha_id, fecha) VALUES (?, ?, ?)");
$sql->bind_param("iis", $equipo_id, $cancha_id, $fecha);

if ($sql->execute()) {
  header("Location: equipo.php?id=$equipo_id&ok=1");
  exit;
} else {
  echo "Error al crear el partido: " . $sql->error;
}
