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

// Verificar si es capitán
$check = $conexion->prepare("
    SELECT es_capitan 
    FROM equipo_jugadores 
    WHERE id_equipo = ? AND id_usuario = ?
");
$check->bind_param("ii", $id_equipo, $id_usuario);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No eres miembro de este equipo']);
    exit;
}

$miembro = $result->fetch_assoc();

// Si es capitán, no puede salir a menos que sea el único miembro
if ($miembro['es_capitan']) {
    $countMembers = $conexion->prepare("SELECT COUNT(*) as total FROM equipo_jugadores WHERE id_equipo = ?");
    $countMembers->bind_param("i", $id_equipo);
    $countMembers->execute();
    $count = $countMembers->get_result()->fetch_assoc()['total'];
    
    if ($count > 1) {
        echo json_encode(['success' => false, 'message' => 'Como capitán, primero debes transferir el liderazgo o eliminar el equipo']);
        exit;
    }
    
    // Si es el único miembro, eliminar el equipo
    $deleteTeam = $conexion->prepare("UPDATE equipos SET activo = 0 WHERE id_equipo = ?");
    $deleteTeam->bind_param("i", $id_equipo);
    $deleteTeam->execute();
}

// Eliminar al jugador del equipo
$delete = $conexion->prepare("DELETE FROM equipo_jugadores WHERE id_equipo = ? AND id_usuario = ?");
$delete->bind_param("ii", $id_equipo, $id_usuario);

if ($delete->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Has salido del equipo exitosamente'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al salir del equipo']);
}
?>