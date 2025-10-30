<?php
require_once 'conexion.php';
require_once 'sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $stmt = $conexion->prepare("SELECT id, password, nombre FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        if (password_verify($password, $usuario['password'])) {
            iniciarSesion($email);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            header("Location: ../index.html");
        } else {
            die("Credenciales incorrectas");
        }
    } else {
        die("Usuario no encontrado");
    }
}
?>
