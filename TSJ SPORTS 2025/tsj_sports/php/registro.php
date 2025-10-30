<?php
$conexion = new mysqli("localhost", "root", "", "tsj_sports");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recibir datos del formulario
$usuario = $_POST['nombre'] ?? '';
$email   = $_POST['email'] ?? '';
$clave   = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

// Validar que no estén vacíos
if (!empty($usuario) && !empty($email) && !empty($_POST['password'])) {
    // Insertar con sentencia preparada
    $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, email, contrasena) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $usuario, $email, $clave);

    if ($stmt->execute()) {
        // ✅ Registro exitoso → redirigir al login
        header("Location: /TSJ SPORTS 2025/index.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Por favor complete todos los campos.";
}

$conexion->close();
?>