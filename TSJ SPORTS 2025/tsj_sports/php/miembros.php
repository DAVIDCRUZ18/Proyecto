<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autenticado']));
}

$equipoId = $_GET['equipo_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion->query("INSERT INTO miembros_equipo (equipo_id, usuario_id) 
                         VALUES ($equipoId, {$_SESSION['usuario_id']})");
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Ya perteneces a este equipo']);
    }
}
