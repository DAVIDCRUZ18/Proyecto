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
    $userQuery = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_usuario = ?");
    $userQuery->bind_param("i", $id_usuario);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $usuarioData = $userResult->fetch_assoc();
        $nombreUsuario = $usuarioData['usuario'];
    } else {
        $nombreUsuario = "Jugador_" . $id_usuario;
    }

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

// ================================
// 4. CARGAR TODOS LOS EQUIPOS - VERSIÓN SIMPLIFICADA
// ================================
// Primero intentemos una consulta simple
$equiposQuery = "SELECT * FROM equipos WHERE activo = 1 ORDER BY fecha_creacion DESC";
$equiposResult = $conexion->query($equiposQuery);

$equipos = [];
$error_query = null;

if ($equiposResult) {
    while ($row = $equiposResult->fetch_assoc()) {
        // Para cada equipo, obtener información adicional
        
        // Obtener capitán
        $capitanQuery = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_usuario = ?");
        $capitanQuery->bind_param("i", $row['id_capitan']);
        $capitanQuery->execute();
        $capitanResult = $capitanQuery->get_result();
        $capitan = $capitanResult->fetch_assoc();
        
        // Contar jugadores
        $jugadoresQuery = $conexion->prepare("SELECT COUNT(*) as total FROM equipo_jugadores WHERE id_equipo = ?");
        $jugadoresQuery->bind_param("i", $row['id_equipo']);
        $jugadoresQuery->execute();
        $jugadoresResult = $jugadoresQuery->get_result();
        $jugadores = $jugadoresResult->fetch_assoc();
        
        // Contar partidos
        $partidosQuery = $conexion->prepare("SELECT COUNT(*) as total FROM partidos WHERE id_equipo_local = ? OR id_equipo_visitante = ?");
        $partidosQuery->bind_param("ii", $row['id_equipo'], $row['id_equipo']);
        $partidosQuery->execute();
        $partidosResult = $partidosQuery->get_result();
        $partidos = $partidosResult->fetch_assoc();
        
        // Verificar si es mi equipo
        $miEquipoQuery = $conexion->prepare("SELECT COUNT(*) as total FROM equipo_jugadores WHERE id_equipo = ? AND id_usuario = ?");
        $miEquipoQuery->bind_param("ii", $row['id_equipo'], $id_usuario);
        $miEquipoQuery->execute();
        $miEquipoResult = $miEquipoQuery->get_result();
        $miEquipo = $miEquipoResult->fetch_assoc();
        
        $equipos[] = [
            'id_equipo' => $row['id_equipo'],
            'nombre_equipo' => $row['nombre_equipo'],
            'escudo' => $row['escudo'],
            'ciudad' => $row['ciudad'],
            'descripcion' => $row['descripcion'],
            'fecha_creacion' => $row['fecha_creacion'],
            'capitan_nombre' => $capitan['usuario'] ?? 'Sin capitán',
            'total_jugadores' => $jugadores['total'] ?? 0,
            'total_partidos' => $partidos['total'] ?? 0,
            'es_mi_equipo' => $miEquipo['total'] ?? 0
        ];
    }
} else {
    $error_query = $conexion->error;
}

// DEBUG: Contar equipos
$total_equipos = count($equipos);

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

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
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
            border: none;
            cursor: pointer;
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
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
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

        .equipo-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .equipo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .equipo-card.mi-equipo {
            border: 3px solid #667eea;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }

        .equipo-card.mi-equipo::before {
            content: '⭐ Tu Equipo';
            position: absolute;
            top: 10px;
            right: -35px;
            background: #667eea;
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 12px;
            font-weight: bold;
        }

        .escudo-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #f0f0f0;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .escudo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 3px;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .stats-item i {
            color: #667eea;
        }

        .no-equipos {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .no-equipos i {
            font-size: 64px;
            color: #667eea;
            margin-bottom: 20px;
        }

        .filtros-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .debug-info {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="dashboard-header" style="background: rgba(0,0,0,0.2); padding: 20px; margin-bottom: 20px;">
        <div class="container mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 style="font-size: 2rem; font-weight: bold; color: white;">⚽ TSJ SPORTS Dashboard</h1>
                </div>
                <div class="user-info" style="display: flex; align-items: center; gap: 20px;">
                    <div style="color: white;">
                        <h3 style="font-weight: 600;"><?php echo htmlspecialchars($perfil['name']); ?></h3>
                        <?php if ($perteneceEquipo): ?>
                            <span style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                                <?php echo $equipo['es_capitan'] ? '👑' : ''; ?>
                            </span>
                        <?php else: ?>
                            <p style="opacity: 0.9;">Sin equipo</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($notificaciones > 0): ?>
                        <a href="/TSJ SPORTS 2025/tsj_sports/php/responder_partido.php" style="background: #ff4757; color: white; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: 600;">
                            🔔 <?php echo $notificaciones; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- DEBUG INFO -->
        <!--<div class="debug-info">
            <strong>🔍 Información de Depuración:</strong><br>
            Total de equipos encontrados: <strong><?php echo $total_equipos; ?></strong><br>
            Usuario ID: <strong><?php echo $id_usuario; ?></strong><br>
            <?php if ($error_query): ?>
                <span style="color: red;">Error en consulta: <?php echo htmlspecialchars($error_query); ?></span><br>
            <?php endif; ?>
            <?php if ($total_equipos > 0): ?>
                Equipos: 
                <?php foreach($equipos as $eq): ?>
                    <?php echo htmlspecialchars($eq['nombre_equipo']); ?> (ID: <?php echo $eq['id_equipo']; ?>), 
                <?php endforeach; ?>
            <?php endif; ?>
        </div> -->

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

        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">🏆 Equipos Disponibles</h2>
                <p class="text-white opacity-90">Total: <?php echo $total_equipos; ?> equipos</p>
            </div>
            <button class="btn-primary" onclick="mostrarModal()">
                <i class="fas fa-plus-circle mr-2"></i>Crear/Unirse
            </button>
        </div>

        <!-- Filtros -->
        <?php if ($total_equipos > 0): ?>
        <div class="filtros-container">
            <div class="flex gap-4 items-center">
                <div class="flex-1">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-box" 
                        placeholder="🔍 Buscar equipo..."
                        onkeyup="filtrarEquipos()"
                    >
                </div>
                <select id="filtroOrden" class="search-box" style="width: auto; min-width: 200px;" onchange="ordenarEquipos()">
                    <option value="recientes">Más recientes</option>
                    <option value="nombre">Nombre A-Z</option>
                    <option value="jugadores">Más jugadores</option>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <!-- Listado de equipos -->
        <div id="lista-equipos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($total_equipos === 0): ?>
                <div class="col-span-full no-equipos">
                    <i class="fas fa-users-slash"></i>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No hay equipos disponibles</h3>
                    <p class="text-gray-600 mb-4">Sé el primero en crear un equipo</p>
                    <button class="btn-primary" onclick="mostrarModal()">
                        <i class="fas fa-plus-circle mr-2"></i>Crear Equipo
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($equipos as $equipoItem): ?>
                    <div class="equipo-card <?php echo $equipoItem['es_mi_equipo'] > 0 ? 'mi-equipo' : ''; ?>" 
                         data-nombre="<?php echo strtolower($equipoItem['nombre_equipo']); ?>" 
                         data-jugadores="<?php echo $equipoItem['total_jugadores']; ?>">
                        
                        <div class="escudo-container">
                            <?php 
                            $escudoUrl = !empty($equipoItem['escudo']) 
                                ? '/TSJ SPORTS 2025/tsj_sports/uploads/escudos/' . $equipoItem['escudo']
                                : 'https://via.placeholder.com/100/667eea/ffffff?text=' . substr($equipoItem['nombre_equipo'], 0, 2);
                            ?>
                            <img src="<?php echo htmlspecialchars($escudoUrl); ?>" 
                                 alt="<?php echo htmlspecialchars($equipoItem['nombre_equipo']); ?>">
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-800 text-center mb-2">
                            <?php echo htmlspecialchars($equipoItem['nombre_equipo']); ?>
                        </h3>
                        
                        <div class="text-center mb-3">
                            <span class="badge badge-info">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($equipoItem['ciudad'] ?: 'Sin ciudad'); ?>
                            </span>
                            <span class="badge badge-success">
                                ID: <?php echo $equipoItem['id_equipo']; ?>
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm text-center mb-4" style="min-height: 40px;">
                            <?php echo htmlspecialchars($equipoItem['descripcion'] ?: 'Sin descripción'); ?>
                        </p>

                        <div class="border-t pt-3 mb-3">
                            <div class="stats-item mb-2">
                                <i class="fas fa-crown"></i>
                                <span><strong>Capitán:</strong> <?php echo htmlspecialchars($equipoItem['capitan_nombre']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <div class="stats-item">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $equipoItem['total_jugadores']; ?> jugadores</span>
                                </div>
                                <div class="stats-item">
                                    <i class="fas fa-futbol"></i>
                                    <span><?php echo $equipoItem['total_partidos']; ?> partidos</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <?php if ($equipoItem['es_mi_equipo'] > 0): ?>
                                <span class="text-blue-600 font-semibold">
                                    <i class="fas fa-check-circle"></i> Eres miembro
                                </span>
                            <?php else: ?>
                                <button class="btn-secondary w-full" onclick="solicitarUnion(<?php echo $equipoItem['id_equipo']; ?>)">
                                    <i class="fas fa-user-plus mr-2"></i>Unirse
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="text-center mt-2 text-xs text-gray-400">
                            Creado el <?php echo date('d/m/Y', strtotime($equipoItem['fecha_creacion'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalEquipo" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold text-center mb-3">🏅 Gestión de Equipo</h2>
            <p class="text-gray-600 text-center mb-4">Elige una opción:</p>

            <div style="display: flex; gap: 10px; margin: 20px 0;">
                <button class="btn-primary" onclick="mostrarCrear()" style="flex: 1;">Crear</button>
                <button class="btn-secondary" onclick="mostrarUnirse()" style="flex: 1;">Unirse</button>
            </div>

            <form id="crearForm" action="/TSJ SPORTS 2025/tsj_sports/php/crear_equipo.php" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="text" name="nombre_equipo" placeholder="Nombre del equipo" required class="w-full mb-2 p-2 border rounded">
                <input type="text" name="ciudad" placeholder="Ciudad" class="w-full mb-2 p-2 border rounded">
                <textarea name="descripcion" placeholder="Descripción" rows="3" class="w-full p-2 border rounded mb-2"></textarea>
                <input type="file" name="escudo" accept="image/*" class="w-full mb-2 p-2 border rounded">
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Crear</button>
                    <button type="button" class="btn-secondary flex-1" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>

            <form id="unirseForm" action="/TSJ SPORTS 2025/tsj_sports/php/unirse_equipo.php" method="POST" style="display:none;">
                <input type="number" name="id_equipo" placeholder="ID del equipo" required class="w-full mb-2 p-2 border rounded">
                <p class="text-xs text-gray-500 mb-3">Solicita el ID al capitán</p>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Solicitar</button>
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

        function solicitarUnion(idEquipo) {
            const form = document.getElementById('unirseForm');
            form.querySelector('input[name="id_equipo"]').value = idEquipo;
            mostrarModal();
            mostrarUnirse();
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                cerrarModal();
            }
        };

        function filtrarEquipos() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const equipos = document.querySelectorAll('.equipo-card');
            
            equipos.forEach(equipo => {
                const nombre = equipo.getAttribute('data-nombre');
                if (nombre.includes(searchTerm)) {
                    equipo.style.display = 'block';
                } else {
                    equipo.style.display = 'none';
                }
            });
        }

        function ordenarEquipos() {
            const orden = document.getElementById('filtroOrden').value;
            const container = document.getElementById('lista-equipos');
            const equipos = Array.from(document.querySelectorAll('.equipo-card'));
            
            equipos.sort((a, b) => {
                if (orden === 'nombre') {
                    return a.getAttribute('data-nombre').localeCompare(b.getAttribute('data-nombre'));
                } else if (orden === 'jugadores') {
                    return parseInt(b.getAttribute('data-jugadores')) - parseInt(a.getAttribute('data-jugadores'));
                }
                return 0;
            });
            
            equipos.forEach(equipo => container.appendChild(equipo));
        }
    </script>
</body>
</html>