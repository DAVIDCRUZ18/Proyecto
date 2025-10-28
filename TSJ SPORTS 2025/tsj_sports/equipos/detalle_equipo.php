<?php
session_start();
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

$id_equipo = intval($_GET['id_equipo'] ?? 0);

if ($id_equipo === 0) {
    echo json_encode(['success' => false, 'message' => 'ID de equipo inválido']);
    exit;
}

// Obtener información del equipo
$query = $conexion->prepare("
    SELECT 
        e.*,
        u.usuario AS capitan_nombre,
        u.email AS capitan_email
    FROM equipos e
    LEFT JOIN usuarios u ON e.id_capitan = u.id_usuario
    WHERE e.id_equipo = ? AND e.activo = 1
");
$query->bind_param("i", $id_equipo);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
    exit;
}

$equipo = $result->fetch_assoc();

// Obtener jugadores del equipo
$jugadores = $conexion->prepare("
    SELECT 
        ej.id_relacion,
        ej.numero_camiseta,
        ej.es_capitan,
        ej.fecha_union,
        u.id_usuario,
        u.usuario,
        u.email,
        p.foto_perfil
    FROM equipo_jugadores ej
    INNER JOIN usuarios u ON ej.id_usuario = u.id_usuario
    LEFT JOIN perfil p ON u.id_usuario = p.id
    WHERE ej.id_equipo = ?
    ORDER BY ej.es_capitan DESC, ej.fecha_union ASC
");
$jugadores->bind_param("i", $id_equipo);
$jugadores->execute();
$resultJugadores = $jugadores->get_result();

$lista_jugadores = [];
while ($jugador = $resultJugadores->fetch_assoc()) {
    $lista_jugadores[] = [
        'id_usuario' => $jugador['id_usuario'],
        'usuario' => $jugador['usuario'],
        'email' => $jugador['email'],
        'foto_perfil' => $jugador['foto_perfil'] ?? 'https://via.placeholder.com/50',
        'numero_camiseta' => $jugador['numero_camiseta'],
        'es_capitan' => (bool)$jugador['es_capitan'],
        'fecha_union' => $jugador['fecha_union']
    ];
}

// Calcular estadísticas del equipo
$stats = $conexion->prepare("
    SELECT 
        COUNT(DISTINCT p.id_partido) as partidos_totales,
        SUM(CASE WHEN p.estado = 'finalizado' AND p.goles_local > p.goles_visitante AND p.id_equipo_local = ? THEN 1
                 WHEN p.estado = 'finalizado' AND p.goles_visitante > p.goles_local AND p.id_equipo_visitante = ? THEN 1 
                 ELSE 0 END) as victorias,
        SUM(CASE WHEN p.estado = 'finalizado' AND p.goles_local = p.goles_visitante AND (p.id_equipo_local = ? OR p.id_equipo_visitante = ?) THEN 1 ELSE 0 END) as empates,
        SUM(CASE WHEN p.estado = 'finalizado' AND p.goles_local < p.goles_visitante AND p.id_equipo_local = ? THEN 1
                 WHEN p.estado = 'finalizado' AND p.goles_visitante < p.goles_local AND p.id_equipo_visitante = ? THEN 1 
                 ELSE 0 END) as derrotas
    FROM partidos p
    WHERE (p.id_equipo_local = ? OR p.id_equipo_visitante = ?)
");
$stats->bind_param("iiiiiiii", $id_equipo, $id_equipo, $id_equipo, $id_equipo, $id_equipo, $id_equipo, $id_equipo, $id_equipo);
$stats->execute();
$estadisticas = $stats->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'equipo' => [
        'id_equipo' => $equipo['id_equipo'],
        'nombre_equipo' => $equipo['nombre_equipo'],
        'escudo' => $equipo['escudo'] ?? 'https://via.placeholder.com/150',
        'descripcion' => $equipo['descripcion'] ?? 'Sin descripción',
        'ciudad' => $equipo['ciudad'] ?? 'Sin ciudad',
        'capitan_nombre' => $equipo['capitan_nombre'],
        'fecha_creacion' => $equipo['fecha_creacion']
    ],
    'jugadores' => $lista_jugadores,
    'estadisticas' => [
        'partidos' => $estadisticas['partidos_totales'] ?? 0,
        'victorias' => $estadisticas['victorias'] ?? 0,
        'empates' => $estadisticas['empates'] ?? 0,
        'derrotas' => $estadisticas['derrotas'] ?? 0
    ]
]);
?>