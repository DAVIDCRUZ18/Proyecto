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

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

$instagram = trim($data['instagram'] ?? '');
$twitter = trim($data['twitter'] ?? '');
$facebook = trim($data['facebook'] ?? '');

// Limpiar símbolos @ si los pusieron
$instagram = ltrim($instagram, '@');
$twitter = ltrim($twitter, '@');

// Actualizar perfil
$update = $conexion->prepare("
    UPDATE perfil 
    SET instagram = ?, twitter = ?, facebook = ? 
    WHERE id = ?
");
$update->bind_param("sssi", $instagram, $twitter, $facebook, $id_usuario);

if ($update->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Perfil actualizado correctamente',
        'data' => [
            'instagram' => $instagram,
            'twitter' => $twitter,
            'facebook' => $facebook
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
}
?>