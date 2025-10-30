<?php
include 'conexion.php';

header('Content-Type: application/json');

$usuario = htmlspecialchars($_POST['usuario']);
$mensaje = htmlspecialchars($_POST['mensaje']);

$stmt = $conexion->prepare("INSERT INTO mensajes (usuario, mensaje) VALUES (?, ?)");
$stmt->bind_param("ss", $usuario, $mensaje);

if($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}

$stmt->close();
$conexion->close();



?>
