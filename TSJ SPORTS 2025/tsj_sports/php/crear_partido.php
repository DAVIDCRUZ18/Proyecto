<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";


$id_usuario = $_SESSION['id_usuario'];

// ================================
// 1. Verificar que el usuario sea capitán de un equipo
// ================================
$capitanQuery = $conexion->prepare("
    SELECT e.id_equipo, e.nombre_equipo, ej.es_capitan
    FROM equipo_jugadores ej
    INNER JOIN equipos e ON e.id_equipo = ej.id_equipo
    WHERE ej.id_usuario = ? AND e.activo = 1
");
$capitanQuery->bind_param("i", $id_usuario);
$capitanQuery->execute();
$equipoResult = $capitanQuery->get_result();

if ($equipoResult->num_rows === 0) {
    die("Error: No perteneces a ningún equipo activo.");
}

$miEquipo = $equipoResult->fetch_assoc();
$id_mi_equipo = $miEquipo['id_equipo'];
$es_capitan = $miEquipo['es_capitan'];

// Solo los capitanes pueden crear partidos
if (!$es_capitan) {
    die("Error: Solo el capitán del equipo puede crear partidos.");
}

// ================================
// 2. Procesar formulario si es POST
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipo_visitante = intval($_POST['equipo_visitante']);
    $fecha_partido = $_POST['fecha_partido'];
    $lugar = trim($_POST['lugar']);
    
    // Validaciones
    $errores = [];
    
    if ($id_equipo_visitante === $id_mi_equipo) {
        $errores[] = "No puedes crear un partido contra tu propio equipo.";
    }
    
    if (empty($fecha_partido)) {
        $errores[] = "Debes seleccionar una fecha para el partido.";
    } else {
        $fecha_obj = new DateTime($fecha_partido);
        $ahora = new DateTime();
        if ($fecha_obj <= $ahora) {
            $errores[] = "La fecha del partido debe ser futura.";
        }
    }
    
    if (empty($lugar)) {
        $errores[] = "Debes especificar el lugar del partido.";
    }
    
    // Verificar que el equipo visitante existe y está activo
    $checkEquipo = $conexion->prepare("SELECT nombre_equipo FROM equipos WHERE id_equipo = ? AND activo = 1");
    $checkEquipo->bind_param("i", $id_equipo_visitante);
    $checkEquipo->execute();
    $equipoVisitante = $checkEquipo->get_result();
    
    if ($equipoVisitante->num_rows === 0) {
        $errores[] = "El equipo seleccionado no existe o no está activo.";
    }
    
    // Si no hay errores, crear el partido
    if (empty($errores)) {
        $insertPartido = $conexion->prepare("
            INSERT INTO partidos 
            (id_equipo_local, id_equipo_visitante, fecha_partido, lugar, estado, id_creador, goles_local, goles_visitante) 
            VALUES (?, ?, ?, ?, 'pendiente', ?, 0, 0)
        ");
        $insertPartido->bind_param("iissi", $id_mi_equipo, $id_equipo_visitante, $fecha_partido, $lugar, $id_usuario);
        
        if ($insertPartido->execute()) {
            $id_partido = $insertPartido->insert_id;
            
            // Obtener el capitán del equipo visitante para notificarle
            $getCapitan = $conexion->prepare("
                SELECT ej.id_usuario, u.email, e.nombre_equipo
                FROM equipo_jugadores ej
                INNER JOIN usuarios u ON u.id_usuario = ej.id_usuario
                INNER JOIN equipos e ON e.id_equipo = ej.id_equipo
                WHERE ej.id_equipo = ? AND ej.es_capitan = 1
            ");
            $getCapitan->bind_param("i", $id_equipo_visitante);
            $getCapitan->execute();
            $capitanVisitante = $getCapitan->get_result()->fetch_assoc();
            
            if ($capitanVisitante) {
                // Crear notificación
                $mensaje = "El equipo {$miEquipo['nombre_equipo']} te ha invitado a un partido el " . 
                          date('d/m/Y H:i', strtotime($fecha_partido)) . " en {$lugar}";
                
                $insertNotif = $conexion->prepare("
                    INSERT INTO notificaciones (id_usuario, tipo, mensaje, leida) 
                    VALUES (?, 'partido_invitacion', ?, 0)
                ");
                $insertNotif->bind_param("is", $capitanVisitante['id_usuario'], $mensaje);
                $insertNotif->execute();
            }
            
            $_SESSION['mensaje'] = "¡Partido creado exitosamente! Esperando confirmación del equipo rival.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: mi_equipo.php?id=" . $id_mi_equipo);
            exit;
        } else {
            $errores[] = "Error al crear el partido: " . $conexion->error;
        }
    }
}

// ================================
// 3. Obtener equipos disponibles
// ================================
$equiposQuery = $conexion->prepare("
    SELECT id_equipo, nombre_equipo, ciudad, 
           (SELECT COUNT(*) FROM equipo_jugadores WHERE id_equipo = equipos.id_equipo) as num_jugadores
    FROM equipos 
    WHERE activo = 1 AND id_equipo != ?
    ORDER BY nombre_equipo ASC
");
$equiposQuery->bind_param("i", $id_mi_equipo);
$equiposQuery->execute();
$equipos = $equiposQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// ================================
// 4. Obtener partidos pendientes creados por este equipo
// ================================
$pendientesQuery = $conexion->prepare("
    SELECT 
        p.id_partido,
        p.fecha_partido,
        p.lugar,
        p.estado,
        ev.nombre_equipo as equipo_visitante,
        p.fecha_creacion
    FROM partidos p
    INNER JOIN equipos ev ON ev.id_equipo = p.id_equipo_visitante
    WHERE p.id_equipo_local = ? AND p.estado = 'pendiente'
    ORDER BY p.fecha_partido ASC
");
$pendientesQuery->bind_param("i", $id_mi_equipo);
$pendientesQuery->execute();
$pendientes = $pendientesQuery->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Partido - TSJ SPORTS</title>
    <link rel="stylesheet" href="/TSJ SPORTS 2025/tsj_sports/css/crear_partido.css">
    
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>⚽ Crear Nuevo Partido</h1>
        <p>Equipo: <strong><?php echo htmlspecialchars($miEquipo['nombre_equipo']); ?></strong></p>
        <a href="../dashboard.php?php echo $id_mi_equipo; ?>" class="back-btn">← Volver a Dashboard</a>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>⏳ Partidos Pendientes de Confirmación</h2>
            <a href="responder_partido.php" class="btn btn-secondary">
                📬 Ver Invitaciones Recibidas
            </a>
        </div>
    </div>

    <!-- Mensajes de error/éxito -->
    <?php if (isset($errores) && !empty($errores)): ?>
        <div class="alert alert-error">
            <strong>❌ Error al crear el partido:</strong>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?>">
            <strong><?php echo htmlspecialchars($_SESSION['mensaje']); ?></strong>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Formulario de creación -->
    <div class="card">
        <h2>📋 Datos del Partido</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="equipo_visitante">🏆 Equipo Rival *</label>
                <select name="equipo_visitante" id="equipo_visitante" required>
                    <option value="">-- Selecciona un equipo --</option>
                    <?php foreach ($equipos as $equipo): ?>
                        <option value="<?php echo $equipo['id_equipo']; ?>">
                            <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                            <?php if ($equipo['ciudad']): ?>
                                - <?php echo htmlspecialchars($equipo['ciudad']); ?>
                            <?php endif; ?>
                            (<?php echo $equipo['num_jugadores']; ?> jugadores)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Selecciona el equipo contra el que quieres jugar</small>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="fecha_partido">📅 Fecha y Hora del Partido *</label>
                    <input 
                        type="datetime-local" 
                        name="fecha_partido" 
                        id="fecha_partido" 
                        min="<?php echo date('Y-m-d\TH:i'); ?>" 
                        required
                    >
                    <small>El partido debe ser en una fecha futura</small>
                </div>

                <div class="form-group">
                    <label for="lugar">📍 Lugar del Partido *</label>
                    <input 
                        type="text" 
                        name="lugar" 
                        id="lugar" 
                        placeholder="Ej: Cancha Municipal, Estadio Central..."
                        required
                        maxlength="200"
                    >
                    <small>Dónde se jugará el partido</small>
                </div>
            </div>

            <div class="form-group">
                <label>ℹ️ Información Importante</label>
                <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #2196F3;">
                    <ul style="margin-left: 20px; color: #0066cc;">
                        <li>El partido se creará con estado <strong>"Pendiente"</strong></li>
                        <li>El capitán del equipo rival recibirá una notificación</li>
                        <li>Debe aceptar la invitación para confirmar el partido</li>
                        <li>Podrás cancelar el partido si aún no ha sido aceptado</li>
                    </ul>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                ✅ Crear Partido y Enviar Invitación
            </button>
        </form>
    </div>

    <!-- Partidos pendientes -->
    <?php if (!empty($pendientes)): ?>
        <div class="card">
            <h2>⏳ Partidos Pendientes de Confirmación</h2>
            <p style="color: #666; margin-bottom: 15px;">
                Estos partidos están esperando respuesta del equipo rival
            </p>
            <?php foreach ($pendientes as $partido): ?>
                <div class="partido-item">
                    <div class="partido-info">
                        <strong>
                            <?php echo htmlspecialchars($miEquipo['nombre_equipo']); ?> 
                            vs 
                            <?php echo htmlspecialchars($partido['equipo_visitante']); ?>
                        </strong>
                        <small>
                            📅 <?php echo date('d/m/Y H:i', strtotime($partido['fecha_partido'])); ?> |
                            📍 <?php echo htmlspecialchars($partido['lugar']); ?> |
                            Creado: <?php echo date('d/m/Y', strtotime($partido['fecha_creacion'])); ?>
                        </small>
                    </div>
                    <div>
                        <span class="estado-badge estado-pendiente">⏰ Pendiente</span>
                        <form method="POST" action="cancelar_partido.php" style="display: inline; margin-left: 10px;">
                            <input type="hidden" name="id_partido" value="<?php echo $partido['id_partido']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Seguro que quieres cancelar este partido?')">
                                ❌ Cancelar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Ayuda -->
    <div class="card" style="background: #f8f9fa;">
        <h2>❓ ¿Necesitas ayuda?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 15px;">
            <div style="padding: 15px; background: white; border-radius: 8px;">
                <h3 style="color: #667eea; margin-bottom: 10px;">🤔 ¿Cómo funciona?</h3>
                <p style="font-size: 0.9em; color: #666;">
                    Creas el partido, el otro equipo recibe notificación y puede aceptar o rechazar.
                </p>
            </div>
            <div style="padding: 15px; background: white; border-radius: 8px;">
                <h3 style="color: #667eea; margin-bottom: 10px;">⚠️ ¿Y si no aceptan?</h3>
                <p style="font-size: 0.9em; color: #666;">
                    Puedes cancelar el partido y crear uno nuevo con otro equipo.
                </p>
            </div>
            <div style="padding: 15px; background: white; border-radius: 8px;">
                <h3 style="color: #667eea; margin-bottom: 10px;">📊 ¿Se registran stats?</h3>
                <p style="font-size: 0.9em; color: #666;">
                    Sí, una vez finalizado podrás registrar goles, asistencias y más.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Autocompletar fecha con 7 días de adelanto
    window.addEventListener('DOMContentLoaded', function() {
        const fechaInput = document.getElementById('fecha_partido');
        if (!fechaInput.value) {
            const fecha = new Date();
            fecha.setDate(fecha.getDate() + 7); // 7 días adelante
            fecha.setHours(18, 0); // 6:00 PM
            
            const año = fecha.getFullYear();
            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia = String(fecha.getDate()).padStart(2, '0');
            const hora = String(fecha.getHours()).padStart(2, '0');
            const minutos = String(fecha.getMinutes()).padStart(2, '0');
            
            fechaInput.value = `${año}-${mes}-${dia}T${hora}:${minutos}`;
        }
    });
</script>

</body>
</html>