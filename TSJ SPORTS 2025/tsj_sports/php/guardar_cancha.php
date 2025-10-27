<?php
include("conexion.php"); // tu conexión mysqli

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $superficie = $_POST['superficie'] ?? '';
    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';

    if (!$nombre || !$direccion || !$tipo || !$superficie || !$lat || !$lng) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO canchas (nombre, direccion, tipo, superficie, lat, lng) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdd", $nombre, $direccion, $tipo, $superficie, $lat, $lng);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
    }

    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>