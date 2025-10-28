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
$id_equipo = intval($data['id_equipo'] ?? 0);

if ($id_equipo === 0) {
    echo json_encode(['success' => false, 'message' => 'ID de equipo inválido']);
    exit;
}

// Verificar si el equipo existe y está activo
$check = $conexion->prepare("SELECT id_equipo FROM equipos WHERE id_equipo = ? AND activo = 1");
$check->bind_param("i", $id_equipo);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'El equipo no existe o no está activo']);
    exit;
}

// Verificar si ya es miembro
$checkMember = $conexion->prepare("SELECT id_relacion FROM equipo_jugadores WHERE id_equipo = ? AND id_usuario = ?");
$checkMember->bind_param("ii", $id_equipo, $id_usuario);
$checkMember->execute();

if ($checkMember->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya eres miembro de este equipo']);
    exit;
}

// Agregar al jugador al equipo
$insert = $conexion->prepare("
    INSERT INTO equipo_jugadores (id_equipo, id_usuario, es_capitan, fecha_union) 
    VALUES (?, ?, 0, NOW())
");
$insert->bind_param("ii", $id_equipo, $id_usuario);

if ($insert->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Te has unido al equipo exitosamente'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al unirse al equipo']);
}
?>