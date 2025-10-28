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

// Obtener datos del perfil
$query = $conexion->prepare("
    SELECT 
        p.foto_perfil,
        p.name,
        p.instagram,
        p.twitter,
        p.facebook,
        u.usuario,
        u.email
    FROM perfil p
    INNER JOIN usuarios u ON p.id = u.id_usuario
    WHERE p.id = ?
");
$query->bind_param("i", $id_usuario);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    // Si no existe perfil, crear uno básico
    $insert = $conexion->prepare("INSERT INTO perfil (id, name) VALUES (?, 'Jugador')");
    $insert->bind_param("i", $id_usuario);
    $insert->execute();
    
    // Volver a consultar
    $query->execute();
    $result = $query->get_result();
}

$perfil = $result->fetch_assoc();

// Consultar estadísticas desde la tabla usuarios
$statsQuery = $conexion->prepare("
    SELECT Rol 
    FROM usuarios 
    WHERE id_usuario = ?
");
$statsQuery->bind_param("i", $id_usuario);
$statsQuery->execute();
$statsResult = $statsQuery->get_result();
$stats = $statsResult->fetch_assoc();

// Aquí puedes agregar consultas reales para goles y partidos
// Por ahora usamos valores de ejemplo
$goles = 0; // Consultar desde tabla de estadísticas
$partidos = 0; // Consultar desde tabla de partidos

echo json_encode([
    'success' => true,
    'perfil' => [
        'foto_perfil' => $perfil['foto_perfil'] ?? 'https://via.placeholder.com/120',
        'nombre' => $perfil['name'] ?? 'Jugador',
        'usuario' => $perfil['usuario'],
        'email' => $perfil['email'],
        'instagram' => $perfil['instagram'] ?? '',
        'twitter' => $perfil['twitter'] ?? '',
        'facebook' => $perfil['facebook'] ?? '',
        'equipo' => $stats['Rol'] ?? 'Sin equipo',
        'goles' => $goles,
        'partidos' => $partidos
    ]
]);
?>