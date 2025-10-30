<?php
include 'conexion.php';

header('Content-Type: application/json');

// Obtener últimos 50 mensajes
if(!isset($_GET['accion'])) {
    $result = $conexion->query("SELECT * FROM mensajes ORDER BY fecha DESC LIMIT 50");
    $mensajes = [];
    
    while($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }
    
    echo json_encode(array_reverse($mensajes));
    exit;
}

// Obtener usuarios conectados (simulación)
if($_GET['accion'] === 'conectados') {
    $result = $conexion->query("SELECT COUNT(DISTINCT usuario) as conectados FROM mensajes WHERE fecha >= NOW() - INTERVAL 5 MINUTE");
    echo json_encode($result->fetch_assoc());
}

$conexion->close();
?>
