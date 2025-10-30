<?php
function iniciarSesion($email) {
    session_start();
    $_SESSION['usuario'] = $email;
    $_SESSION['ultimo_acceso'] = time();
}

function verificarSesion() {
    session_start();
    if (!isset($_SESSION['usuario']) || (time() - $_SESSION['ultimo_acceso'] > 3600)) {
        header("Location: auth/login.html");
        exit();
    }
    $_SESSION['ultimo_acceso'] = time();
}

function cerrarSesion() {
    session_start();
    session_unset();
    session_destroy();
}
?>