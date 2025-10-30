<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autenticado']));
}

$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {
        case 'POST':
            $datos = json_decode(file_get_contents('php://input'), true);
            $nombre = $conexion->real_escape_string($datos['nombre']);
            $descripcion = $conexion->real_escape_string($datos['descripcion']);
            
            $conexion->query("INSERT INTO equipos (nombre, descripcion, lider_id) 
                             VALUES ('$nombre', '$descripcion', {$_SESSION['usuario_id']})");
            
            $equipoId = $conexion->insert_id;
            $conexion->query("INSERT INTO miembros_equipo (equipo_id, usuario_id) 
                             VALUES ($equipoId, {$_SESSION['usuario_id']})");
            
            echo json_encode(['id' => $equipoId]);
            break;

        case 'GET':
            $result = $conexion->query("
                SELECT e.*, u.nombre as lider, 
                (SELECT COUNT(*) FROM miembros_equipo WHERE equipo_id = e.id) as miembros
                FROM equipos e
                JOIN usuarios u ON e.lider_id = u.id
            ");
            
            $equipos = [];
            while($fila = $result->fetch_assoc()) {
                $equipos[] = $fila;
            }
            echo json_encode($equipos);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
