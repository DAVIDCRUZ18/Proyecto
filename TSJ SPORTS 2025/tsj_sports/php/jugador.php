<?php
header('Content-Type: application/json');
include("conexion.php");

$jugador_id = 1; // En el futuro, podrías usar sesiones o $_GET para obtenerlo

$sql = "SELECT j.nombre, j.goles, j.partidos, e.nombre AS equipo 
        FROM jugadores j 
        JOIN equipos e ON j.equipo_id = e.id 
        WHERE j.id = $jugador_id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "Jugador no encontrado"]);
}
?>

