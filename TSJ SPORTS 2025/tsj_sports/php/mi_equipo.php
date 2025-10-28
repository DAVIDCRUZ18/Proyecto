<?php
session_start();
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo = $_SESSION['tipo_mensaje'] === 'success' ? 'success' : 'error';
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <title>Mensaje</title>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: '<?php echo $tipo; ?>',
                title: '<?php echo ($tipo === "success" ? "Éxito" : "Error"); ?>',
                text: '<?php echo addslashes($mensaje); ?>',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'crear_partido.php';
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
