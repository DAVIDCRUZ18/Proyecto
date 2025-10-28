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

// Verificar que se subió un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
    exit;
}

$file = $_FILES['foto'];

// Validar tipo de archivo
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Formato de imagen no válido']);
    exit;
}

// Validar tamaño (máximo 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'La imagen es muy grande (máx 5MB)']);
    exit;
}

// Crear carpeta si no existe
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/uploads/perfiles/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Obtener foto anterior para eliminarla
$query = $conexion->prepare("SELECT foto_perfil FROM perfil WHERE id = ?");
$query->bind_param("i", $id_usuario);
$query->execute();
$result = $query->get_result();
$oldProfile = $result->fetch_assoc();

// Eliminar foto anterior si existe
if ($oldProfile && $oldProfile['foto_perfil'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldProfile['foto_perfil'])) {
    unlink($_SERVER['DOCUMENT_ROOT'] . $oldProfile['foto_perfil']);
}

// Generar nombre único
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'perfil_' . $id_usuario . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Mover archivo
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
    exit;
}

// Ruta relativa para la base de datos
$relativePath = "/TSJ SPORTS 2025/tsj_sports/uploads/perfiles/" . $filename;

// Actualizar en base de datos
$update = $conexion->prepare("UPDATE perfil SET foto_perfil = ? WHERE id = ?");
$update->bind_param("si", $relativePath, $id_usuario);

if ($update->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Foto actualizada correctamente',
        'foto_url' => $relativePath
    ]);
} else {
    // Si falla la BD, eliminar el archivo
    unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos']);
}
?>