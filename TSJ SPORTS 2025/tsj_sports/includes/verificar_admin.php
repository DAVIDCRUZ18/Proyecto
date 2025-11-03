<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$query = $conexion->prepare("SELECT Rol FROM usuarios WHERE id_usuario = ?");
$query->bind_param("i", $id_usuario);
$query->execute();
$result = $query->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario || strtolower($usuario['Rol']) !== 'admin') {
    $_SESSION['mensaje'] = "❌ Acceso restringido: solo administradores pueden ingresar.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../dashboard.php");
    exit;
}
?>