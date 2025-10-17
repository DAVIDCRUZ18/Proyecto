<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    $id = $_SESSION['id_usuario'];

    $conexion = new mysqli("localhost", "root", "", "tsj_sports");
    if (!$conexion->connect_error) {
        $update = $conexion->prepare("UPDATE usuarios SET conectado = 0 WHERE id_usuario = ?");
        $update->bind_param("i", $id);
        $update->execute();
        $update->close();
        $conexion->close();
    }
}

// Destruir la sesión
session_unset();
session_destroy();

// Redirigir al login
header("Location: /TSJ SPORTS 2025/index.html");
exit();
?>
