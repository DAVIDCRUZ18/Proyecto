<?php
require 'php/conexion.php';

$result = $conexion->query("SELECT NOW() AS hora");
if($result) {
    $row = $result->fetch_assoc();
    echo "¡Conexión exitosa! Hora del servidor: " . $row['hora'];
} else {
    echo "Error: " . $conexion->error;
}
?>