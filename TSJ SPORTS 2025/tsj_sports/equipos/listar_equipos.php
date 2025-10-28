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

// Obtener todos los equipos con información del capitán y cantidad de jugadores
$query = $conexion->prepare("
    SELECT 
        e.id_equipo,
        e.nombre_equipo,
        e.escudo,
        e.descripcion,
        e.ciudad,
        e.fecha_creacion,
        u.usuario AS capitan_nombre,
        COUNT(DISTINCT ej.id_usuario) AS total_jugadores,
        MAX(CASE WHEN ej.id_usuario = ? THEN 1 ELSE 0 END) AS es_miembro,
        MAX(CASE WHEN ej.id_usuario = ? AND ej.es_capitan = 1 THEN 1 ELSE 0 END) AS soy_capitan
    FROM equipos e
    LEFT JOIN usuarios u ON e.id_capitan = u.id_usuario
    LEFT JOIN equipo_jugadores ej ON e.id_equipo = ej.id_equipo
    WHERE e.activo = 1
    GROUP BY e.id_equipo, e.nombre_equipo, e.escudo, e.descripcion, e.ciudad, e.fecha_creacion, u.usuario
    ORDER BY e.fecha_creacion DESC
");

$query->bind_param("ii", $id_usuario, $id_usuario);
$query->execute();
$result = $query->get_result();

$equipos = [];
while ($row = $result->fetch_assoc()) {
    $equipos[] = [
        'id_equipo' => $row['id_equipo'],
        'nombre_equipo' => $row['nombre_equipo'],
        'escudo' => $row['escudo'] ?? 'https://via.placeholder.com/100',
        'descripcion' => $row['descripcion'] ?? 'Sin descripción',
        'ciudad' => $row['ciudad'] ?? 'Sin ciudad',
        'capitan_nombre' => $row['capitan_nombre'],
        'total_jugadores' => $row['total_jugadores'],
        'fecha_creacion' => $row['fecha_creacion'],
        'es_miembro' => (bool)$row['es_miembro'],
        'soy_capitan' => (bool)$row['soy_capitan']
    ];
}

echo json_encode([
    'success' => true,
    'equipos' => $equipos
]);
?>