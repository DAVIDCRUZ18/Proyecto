<?php
// 🧠 Evitar error por sesión ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1️⃣ Verificar si hay sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /TSJ SPORTS 2025/index.html');
    exit();
}

// 2️⃣ Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "", "tsj_sports");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];

// 3️⃣ Buscar usuario por ID
$stmt = $conexion->prepare("SELECT estado, conectado FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // ❌ Usuario no existe
    session_unset();
    session_destroy();
    header('Location: /TSJ SPORTS 2025/index.html');
    exit();
}

$usuario = $resultado->fetch_assoc();
$stmt->close();
$conexion->close();

// 4️⃣ Validar si el usuario está activo
if ($usuario['estado'] !== 'activo') {
    session_unset();
    session_destroy();
    header('Location: /TSJ SPORTS 2025/index.html?error=desactivado');
    exit();
}

// 5️⃣ (Opcional) Validar si está realmente conectado
if ($usuario['conectado'] != 1) {
    session_unset();
    session_destroy();
    header('Location: /TSJ SPORTS 2025/index.html?error=sinconexion');
    exit();
}
?>