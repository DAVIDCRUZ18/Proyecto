<?php
session_start();

// ================================
//  Verificar sesión activa
// ================================
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /TSJ SPORTS 2025/tsj_sports/inicio.html');
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];

// ================================
// 1. Verificar/Crear perfil
// ================================
$perfilQuery = $conexion->prepare("SELECT * FROM perfil WHERE id = ?");
$perfilQuery->bind_param("i", $id_usuario);
$perfilQuery->execute();
$perfilResult = $perfilQuery->get_result();

if ($perfilResult->num_rows === 0) {
    // Obtener el nombre real desde la tabla usuarios
    $userQuery = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_usuario = ?");
    $userQuery->bind_param("i", $id_usuario);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $usuarioData = $userResult->fetch_assoc();
        $nombreUsuario = $usuarioData['usuario'];
    } else {
        $nombreUsuario = "Jugador_" . $id_usuario; // respaldo
    }

    // Crear perfil
    $insertPerfil = $conexion->prepare("INSERT INTO perfil (id, name) VALUES (?, ?)");
    $insertPerfil->bind_param("is", $id_usuario, $nombreUsuario);
    $insertPerfil->execute();

    $perfil = ['id' => $id_usuario, 'name' => $nombreUsuario];
} else {
    $perfil = $perfilResult->fetch_assoc();
}

// ================================
// 2. Verificar si pertenece a un equipo
// ================================
$equipoQuery = $conexion->prepare("
    SELECT e.id_equipo, e.nombre_equipo, e.escudo, e.ciudad, ej.es_capitan
    FROM equipo_jugadores ej
    INNER JOIN equipos e ON e.id_equipo = ej.id_equipo
    WHERE ej.id_usuario = ? AND e.activo = 1
");
$equipoQuery->bind_param("i", $id_usuario);
$equipoQuery->execute();
$equipoResult = $equipoQuery->get_result();

$perteneceEquipo = $equipoResult->num_rows > 0;
$equipo = $perteneceEquipo ? $equipoResult->fetch_assoc() : null;

// ================================
// 3. Obtener notificaciones no leídas
// ================================
$notifQuery = $conexion->prepare("
    SELECT COUNT(*) as total FROM notificaciones 
    WHERE id_usuario = ? AND leida = 0
");
$notifQuery->bind_param("i", $id_usuario);
$notifQuery->execute();
$notificaciones = $notifQuery->get_result()->fetch_assoc()['total'];

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos - TSJ SPORTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #ccc;
            color: #333;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #bbb;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            z-index: 50;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .no-equipo-alert {
            background: white;
            max-width: 500px;
            margin: 40px auto;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .no-equipo-alert h3 {
            color: #4f46e5;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .no-equipo-alert p {
            color: #333;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="dashboard-header">
        <h1>⚽ TSJ SPORTS Dashboard</h1>
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($perfil['name'], 0, 1)); ?></div>
            <div>
                <h3><?php echo htmlspecialchars($perfil['name']); ?></h3>
                <?php if ($perteneceEquipo): ?>
                    <span class="equipo-badge">
                        <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                        <?php echo $equipo['es_capitan'] ? '👑 Capitán' : ''; ?>
                    </span>
                <?php else: ?>
                    <p style="opacity: 0.9;">Sin equipo asignado</p>
                <?php endif; ?>
            </div>
            <?php if ($notificaciones > 0): ?>
                <a href="/TSJ SPORTS 2025/tsj_sports/php/responder_partido.php" style="margin-left: auto; background: #ff4757; color: white; padding: 8px 16px; border-radius: 20px; text-decoration: none;">
                    🔔 <?php echo $notificaciones; ?> notificaciones
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mensaje si no pertenece a ningún equipo -->
    <?php if (!$perteneceEquipo): ?>
        <div class="no-equipo-alert">
            <h3>⚠️ No estás en ningún equipo</h3>
            <p>Para participar en partidos y torneos, necesitas crear o unirte a un equipo.</p>
            <div style="margin-top: 15px;">
                <button class="btn-primary" onclick="mostrarModal()">Crear / Unirse a Equipo</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Listado de equipos -->
        <div id="lista-equipos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center p-12">
                <div class="loading-spinner"></div>
                <p class="text-white mt-4 text-lg">Cargando equipos...</p>
            </div>
        </div>
    </div>

    <!-- Modal Crear / Unirse -->
    <div id="modalEquipo" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold text-center mb-3">🏅 Gestión de Equipo</h2>
            <p class="text-gray-600 text-center mb-4">Elige una opción para continuar:</p>

            <div style="display: flex; gap: 10px; margin: 20px 0;">
                <button class="btn-primary" onclick="mostrarCrear()" style="flex: 1;">Crear Equipo</button>
                <button class="btn-secondary" onclick="mostrarUnirse()" style="flex: 1;">Unirse a Equipo</button>
            </div>

            <!-- Formulario Crear -->
            <form id="crearForm" action="/TSJ SPORTS 2025/tsj_sports/php/crear_equipo.php" method="POST" style="display:none;">
                <input type="text" name="nombre_equipo" placeholder="Nombre del equipo" required class="w-full mb-2 p-2 border rounded">
                <input type="text" name="ciudad" placeholder="Ciudad (opcional)" class="w-full mb-2 p-2 border rounded">
                <textarea name="descripcion" placeholder="Descripción (opcional)" rows="3" class="w-full p-2 border rounded mb-2"></textarea>

                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Crear</button>
                    <button type="button" class="btn-secondary flex-1" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>

            <!-- Formulario Unirse -->
            <form id="unirseForm" action="/TSJ SPORTS 2025/tsj_sports/php/unirse_equipo.php" method="POST" style="display:none;">
                <input type="number" name="id_equipo" placeholder="ID del equipo" required class="w-full mb-2 p-2 border rounded">
                <p class="text-xs text-gray-500 mb-3">Solicita el ID del equipo al capitán</p>

                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Solicitar Unión</button>
                    <button type="button" class="btn-secondary flex-1" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalEquipo');

        function mostrarModal() {
            modal.style.display = 'flex';
        }

        function cerrarModal() {
            modal.style.display = 'none';
            document.getElementById('crearForm').style.display = 'none';
            document.getElementById('unirseForm').style.display = 'none';
        }

        function mostrarCrear() {
            document.getElementById('crearForm').style.display = 'block';
            document.getElementById('unirseForm').style.display = 'none';
        }

        function mostrarUnirse() {
            document.getElementById('unirseForm').style.display = 'block';
            document.getElementById('crearForm').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target === modal) {
                cerrarModal();
            }
        };
    </script>
</body>
</html>
