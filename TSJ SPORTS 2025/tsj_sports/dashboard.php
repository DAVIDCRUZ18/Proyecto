<?php
session_start();
include __DIR__ . '/php/validar_sesion.php';
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

// Si no existe el perfil, lo creamos tomando el nombre desde la tabla usuarios
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
        $nombreUsuario = "Jugador_" . $id_usuario; // respaldo en caso de error
    }

    // Crear el perfil con el nombre real del usuario
    $insertPerfil = $conexion->prepare("INSERT INTO perfil (id, name) VALUES (?, ?)");
    $insertPerfil->bind_param("is", $id_usuario, $nombreUsuario);
    $insertPerfil->execute();

    // Establecer variable perfil para continuar
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
// 4. Estadísticas del jugador
// ================================
$statsQuery = $conexion->prepare("
    SELECT 
        COALESCE(SUM(goles), 0) as total_goles,
        COALESCE(SUM(asistencias), 0) as total_asistencias,
        COALESCE(SUM(tarjetas_amarillas), 0) as amarillas,
        COALESCE(SUM(tarjetas_rojas), 0) as rojas,
        COUNT(DISTINCT id_partido) as partidos_jugados
    FROM estadisticas_jugador
    WHERE id_usuario = ?
");
$statsQuery->bind_param("i", $id_usuario);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();

// ================================
// 5. Próximos partidos del equipo
// ================================
$proximosPartidos = [];
if ($perteneceEquipo) {
    $partidosQuery = $conexion->prepare("
        SELECT 
            p.id_partido,
            p.fecha_partido,
            p.lugar,
            p.estado,
            el.nombre_equipo as equipo_local,
            ev.nombre_equipo as equipo_visitante,
            p.goles_local,
            p.goles_visitante,
            (p.id_equipo_local = ?) as es_local
        FROM partidos p
        INNER JOIN equipos el ON el.id_equipo = p.id_equipo_local
        INNER JOIN equipos ev ON ev.id_equipo = p.id_equipo_visitante
        WHERE (p.id_equipo_local = ? OR p.id_equipo_visitante = ?)
        AND p.estado IN ('pendiente', 'aceptado')
        ORDER BY p.fecha_partido ASC
        LIMIT 5
    ");
    $id_equipo = $equipo['id_equipo'];
    $partidosQuery->bind_param("iii", $id_equipo, $id_equipo, $id_equipo);
    $partidosQuery->execute();
    $proximosPartidos = $partidosQuery->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ================================
// 6. Torneos disponibles
// ================================
$torneosQuery = $conexion->query("
    SELECT 
        t.id_torneo,
        t.nombre_torneo,
        t.descripcion,
        t.fecha_inicio,
        t.fecha_fin,
        t.costo_inscripcion,
        t.max_equipos,
        t.equipos_inscritos,
        t.estado
    FROM torneos t
    WHERE t.estado = 'inscripciones_abiertas'
    ORDER BY t.fecha_inicio ASC
    LIMIT 3
");
$torneos = $torneosQuery->fetch_all(MYSQLI_ASSOC);

// ================================
// 7. Verificar inscripción a torneos
// ================================
$torneosInscritos = [];
if ($perteneceEquipo) {
    $inscritosQuery = $conexion->prepare("
        SELECT t.nombre_torneo, t.fecha_inicio, ti.pagado
        FROM torneo_inscripciones ti
        INNER JOIN torneos t ON t.id_torneo = ti.id_torneo
        WHERE ti.id_equipo = ?
        AND t.estado != 'finalizado'
    ");
    $inscritosQuery->bind_param("i", $equipo['id_equipo']);
    $inscritosQuery->execute();
    $torneosInscritos = $inscritosQuery->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TSJ SPORTS</title>
    <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/dashboard.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        .dashboard-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .dashboard-header { background: linear-gradient(135deg, #343f74ff 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .dashboard-header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .user-info { display: flex; align-items: center; gap: 15px; margin-top: 15px; }
        .user-avatar { width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: #667eea; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .stat-value { font-size: 2.5em; font-weight: bold; color: #2f3966ff; }
        .stat-label { color: #666; margin-top: 5px; text-transform: uppercase; font-size: 0.85em; }
        
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .section { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section h2 { color: #333; margin-bottom: 20px; font-size: 1.5em; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        
        .partido-item { padding: 15px; border-left: 4px solid #667eea; background: #f8f9fa; margin-bottom: 10px; border-radius: 5px; }
        .partido-item .equipos { font-weight: bold; font-size: 1.1em; margin-bottom: 5px; }
        .partido-item .info { color: #666; font-size: 0.9em; }
        .estado-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-aceptado { background: #d4edda; color: #155724; }
        .estado-finalizado { background: #d1ecf1; color: #0c5460; }
        
        .torneo-card { border: 1px solid #e0e0e0; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .torneo-card h3 { color: #667eea; margin-bottom: 10px; }
        .torneo-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.9em; color: #666; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 1em; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: scale(1.05); }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 40px; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 8px 16px rgba(0,0,0,0.2); }
        .modal-content h2 { color: #667eea; margin-bottom: 20px; }
        .modal-content input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 1em; }
        .modal-content input:focus { outline: none; border-color: #667eea; }
        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        
        .no-equipo-alert { background: #3f285eff; border-left: 4px solid #917e47ff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .equipo-badge { background: #667eea; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; margin-top: 10px; }
        
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
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
                <a href="notificaciones.php" style="margin-left: auto; background: #ff4757; color: white; padding: 8px 16px; border-radius: 20px; text-decoration: none;">
                    🔔 <?php echo $notificaciones; ?> notificaciones
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">⚽ <?php echo $stats['total_goles']; ?></div>
            <div class="stat-label">Goles</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">🎯 <?php echo $stats['total_asistencias']; ?></div>
            <div class="stat-label">Asistencias</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">🏃 <?php echo $stats['partidos_jugados']; ?></div>
            <div class="stat-label">Partidos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">
                <?php if ($stats['amarillas'] > 0) echo '🟨 ' . $stats['amarillas']; ?>
                <?php if ($stats['rojas'] > 0) echo ' 🟥 ' . $stats['rojas']; ?>
                <?php if ($stats['amarillas'] == 0 && $stats['rojas'] == 0) echo '✅ 0'; ?>
            </div>
            <div class="stat-label">Tarjetas</div>
        </div>
    </div>

    <!-- Alert si no tiene equipo -->
    <?php if (!$perteneceEquipo): ?>
        <div class="no-equipo-alert">
            <h3>⚠️ No estás en ningún equipo</h3>
            <p>Para participar en partidos y torneos, necesitas crear o unirte a un equipo.</p>
            <div style="margin-top: 15px;">
                <button class="btn btn-primary" onclick="mostrarModal()">Crear/Unirse a Equipo</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grid de contenido -->
    <div class="content-grid">
        <!-- Columna principal -->
        <div>
            <!-- Próximos partidos -->
            <div class="section">
                <h2>🏆 Próximos Partidos</h2>
                <?php if ($perteneceEquipo && count($proximosPartidos) > 0): ?>
                    <?php foreach ($proximosPartidos as $partido): ?>
                        <div class="partido-item">
                            <div class="equipos">
                                <?php echo htmlspecialchars($partido['equipo_local']); ?> 
                                vs 
                                <?php echo htmlspecialchars($partido['equipo_visitante']); ?>
                            </div>
                            <div class="info">
                                📅 <?php echo date('d/m/Y H:i', strtotime($partido['fecha_partido'])); ?><br>
                                📍 <?php echo htmlspecialchars($partido['lugar'] ?? 'Por definir'); ?><br>
                                Estado: <span class="estado-badge estado-<?php echo $partido['estado']; ?>">
                                    <?php echo ucfirst($partido['estado']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($equipo['es_capitan']): ?>
                        <a href="php/crear_partido.php" class="btn btn-primary" style="margin-top: 15px;">➕ Crear Nuevo Partido</a>
                    <?php endif; ?>
                <?php elseif ($perteneceEquipo): ?>
                    <p style="color: #666;">No hay partidos programados.</p>
                    <?php if ($equipo['es_capitan']): ?>
                        <a href="php/crear_partido.php" class="btn btn-primary" style="margin-top: 15px;">➕ Crear Primer Partido</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: #999;">Únete a un equipo para ver partidos</p>
                <?php endif; ?>
            </div>

            <!-- Torneos inscritos -->
            <?php if (count($torneosInscritos) > 0): ?>
            <div class="section">
                <h2>🏅 Mis Torneos</h2>
                <?php foreach ($torneosInscritos as $torneo): ?>
                    <div class="torneo-card">
                        <h3><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h3>
                        <div class="torneo-info">
                            <span>📅 Inicio: <?php echo date('d/m/Y', strtotime($torneo['fecha_inicio'])); ?></span>
                            <span><?php echo $torneo['pagado'] ? '✅ Pagado' : '⚠️ Pendiente de pago'; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna lateral -->
        <div>
            <!-- Torneos disponibles -->
            <div class="section">
                <h2>🎯 Torneos Abiertos</h2>
                <?php if (count($torneos) > 0): ?>
                    <?php foreach ($torneos as $torneo): ?>
                        <div class="torneo-card">
                            <h3><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h3>
                            <p style="font-size: 0.9em; color: #666; margin: 10px 0;">
                                <?php echo htmlspecialchars(substr($torneo['descripcion'], 0, 100)); ?>...
                            </p>
                            <div class="torneo-info">
                                <span>💰 $<?php echo number_format($torneo['costo_inscripcion'], 0); ?></span>
                                <span>👥 <?php echo $torneo['equipos_inscritos']; ?>/<?php echo $torneo['max_equipos']; ?></span>
                            </div>
                            <?php if ($perteneceEquipo && $equipo['es_capitan']): ?>
                                <a href="inscribir_torneo.php?id=<?php echo $torneo['id_torneo']; ?>" 
                                   class="btn btn-success" style="width: 100%; margin-top: 10px; text-align: center;">
                                    Inscribirse
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666;">No hay torneos disponibles actualmente.</p>
                <?php endif; ?>
            </div>

            <!-- Acciones rápidas -->
            <div class="section">
                <h2>⚡ Acciones Rápidas</h2>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="/TSJ SPORTS 2025/tsj_sports/Perfil/perfil.php" class="btn btn-secondary">👤 Mi Perfil</a>
                    <?php if ($perteneceEquipo): ?>
                        <a href="mi_equipo.php?id=<?php echo $equipo['id_equipo']; ?>" class="btn btn-secondary">🛡️ Mi Equipo</a>
                        <a href="estadisticas.php" class="btn btn-secondary">📊 Estadísticas</a>
                    <?php endif; ?>
                    <a href="todos_torneos.php" class="btn btn-secondary">🏆 Ver Todos los Torneos</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/unirse a equipo -->
<div id="modalEquipo" class="modal">
    <div class="modal-content">
        <h2>🏅 Gestión de Equipo</h2>
        <p>Elige una opción para continuar:</p>
        
        <div style="display: flex; gap: 10px; margin: 20px 0;">
            <button class="btn btn-primary" onclick="mostrarCrear()" style="flex: 1;">Crear Equipo</button>
            <button class="btn btn-secondary" onclick="mostrarUnirse()" style="flex: 1;">Unirse a Equipo</button>
        </div>

        <form id="crearForm" action="/TSJ SPORTS 2025/tsj_sports/php/crear_equipo.php" method="POST" style="display:none;">
            <input type="text" name="nombre_equipo" placeholder="Nombre del equipo" required>
            <input type="text" name="ciudad" placeholder="Ciudad (opcional)">
            <textarea name="descripcion" placeholder="Descripción del equipo (opcional)" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px;"></textarea>
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">Crear Equipo</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>

        <form id="unirseForm" action="/TSJ SPORTS 2025/tsj_sports/php/unirse_equipo.php" method="POST" style="display:none;">
            <input type="number" name="id_equipo" placeholder="ID del equipo" required>
            <p style="font-size: 0.9em; color: #666;">Solicita el ID del equipo al capitán</p>
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">Solicitar Unión</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
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
        if (event.target == modal) {
            cerrarModal();
        }
    }
</script>

</body>
</html>