<?php
session_start();
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents('php://input'), true);

$nombre_equipo = trim($data['nombre_equipo'] ?? '');
$descripcion = trim($data['descripcion'] ?? '');
$ciudad = trim($data['ciudad'] ?? '');

// Validaciones
if (empty($nombre_equipo)) {
    echo json_encode(['success' => false, 'message' => 'El nombre del equipo es requerido']);
    exit;
}

if (strlen($nombre_equipo) < 3) {
    echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres']);
    exit;
}

// Verificar si ya existe un equipo con ese nombre
$check = $conexion->prepare("SELECT id_equipo FROM equipos WHERE nombre_equipo = ?");
$check->bind_param("s", $nombre_equipo);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un equipo con ese nombre']);
    exit;
}

// Crear el equipo (el creador es automáticamente el capitán)
$insert = $conexion->prepare("
    INSERT INTO equipos (nombre_equipo, descripcion, ciudad, id_capitan, activo) 
    VALUES (?, ?, ?, ?, 1)
");
$insert->bind_param("sssi", $nombre_equipo, $descripcion, $ciudad, $id_usuario);

if ($insert->execute()) {
    $id_equipo = $conexion->insert_id;
    
    // Agregar al creador como jugador del equipo
    $addPlayer = $conexion->prepare("
        INSERT INTO equipo_jugadores (id_equipo, id_usuario, es_capitan, fecha_union) 
        VALUES (?, ?, 1, NOW())
    ");
    $addPlayer->bind_param("ii", $id_equipo, $id_usuario);
    $addPlayer->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Equipo creado exitosamente',
        'id_equipo' => $id_equipo
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el equipo']);
}
?>