<?php
session_start();

$conexion = new mysqli("localhost", "root", "", "tsj_sports");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conexion->prepare("SELECT id_usuario, usuario, contrasena FROM usuarios WHERE email = ? AND estado = 'activo' LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();

    if (password_verify($password, $usuario['contrasena'])) {
        // ✅ Actualizar estado de conexión SOLO si la contraseña es correcta
        $update = $conexion->prepare("UPDATE usuarios SET conectado = 1 WHERE id_usuario = ?");
        $update->bind_param("i", $usuario['id_usuario']);
        $update->execute();
        $update->close();

        // ✅ Guardar datos en sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['usuario'] = $usuario['usuario'];

        // ✅ Redirigir al dashboard
        header("Location: ../dashboard.php");
        exit();
    } else {
        echo "❌ Contraseña incorrecta";
    }
} else {
    echo "❌ Usuario no encontrado o inactivo";
}

$stmt->close();
$conexion->close();
?>