<?php
require_once 'conexion.php';
require_once 'sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        die("Todos los campos son requeridos");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico inválido");
    }
    
    // Verificar si el email ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        die("Este correo ya está registrado");
    }
    
    // Hash de contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $insertStmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $nombre, $email, $passwordHash);
    
    if ($insertStmt->execute()) {
        iniciarSesion($email);
        header("Location: ../index.html");
    } else {
        echo "Error al registrar: " . $conexion->error;
    }
}
?>
