<?php
$conexion = new mysqli("localhost", "root", "", "tsj_sports");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recibir datos del formulario
$usuario = trim($_POST['nombre'] ?? '');
$email   = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$clave   = password_hash($password, PASSWORD_DEFAULT);

// Validar campos
if (!empty($usuario) && !empty($email) && !empty($password)) {
    
    // Rol por defecto
    $rol = "jugador";
    $estado = "activo";
    $conectado = '0';
    
    // Insertar nuevo usuario con rol por defecto
    $stmt = $conexion->prepare("
        INSERT INTO usuarios (usuario, email, contrasena, Rol, estado, conectado)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $usuario, $email, $clave, $rol, $estado, $conectado);

    if ($stmt->execute()) {
        // ✅ Registro exitoso → redirigir al login
        header("Location: /TSJ SPORTS 2025/index.html");
        exit();
    } else {
        echo "❌ Error al registrar usuario: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "⚠️ Por favor complete todos los campos.";
}

$conexion->close();
?>