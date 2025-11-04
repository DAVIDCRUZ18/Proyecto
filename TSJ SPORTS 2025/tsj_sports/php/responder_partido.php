<?php
session_start();
include __DIR__ . '/validar_sesion.php';
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/includes/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";


$id_usuario = $_SESSION['id_usuario'];
$mensaje_alerta = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

// Función para crear notificación
function crearNotificacion($conexion, $id_destinatario, $tipo, $mensaje) {
    $stmt = $conexion->prepare("
        INSERT INTO notificaciones (id_usuario, tipo, mensaje, leida, fecha_creacion)
        VALUES (?, ?, ?, 0, NOW())
    ");
    $stmt->bind_param("iss", $id_destinatario, $tipo, $mensaje);
    return $stmt->execute();
}

// Procesar respuesta a invitación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_partido'], $_POST['accion'])) {
    $id_partido = intval($_POST['id_partido']);
    $accion = $_POST['accion'];
    
    // Validar acción
    if (!in_array($accion, ['aceptar', 'rechazar'])) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Acción inválida.'];
        header("Location: responder_partido.php");
        exit;
    }

    // Iniciar transacción
    $conexion->begin_transaction();
    
    try {
        // Verificar permisos y obtener datos del partido
        $checkQuery = $conexion->prepare("
            SELECT p.id_partido, p.id_creador, p.fecha_partido,
                   el.nombre_equipo AS equipo_local, 
                   ev.nombre_equipo AS equipo_visitante
            FROM partidos p
            INNER JOIN equipos el ON el.id_equipo = p.id_equipo_local
            INNER JOIN equipos ev ON ev.id_equipo = p.id_equipo_visitante
            WHERE p.id_partido = ?
              AND ev.id_capitan = ?
              AND p.estado = 'pendiente'
            FOR UPDATE
        ");
        $checkQuery->bind_param("ii", $id_partido, $id_usuario);
        $checkQuery->execute();
        $result = $checkQuery->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No tienes permiso para responder esta invitación o ya fue procesada.");
        }

        $partido = $result->fetch_assoc();

        // Actualizar estado del partido
        $nuevo_estado = ($accion === 'aceptar') ? 'aceptado' : 'rechazado';
        $updateQuery = $conexion->prepare("UPDATE partidos SET estado = ? WHERE id_partido = ?");
        $updateQuery->bind_param("si", $nuevo_estado, $id_partido);
        $updateQuery->execute();

        // Crear notificación personalizada
        if ($accion === 'aceptar') {
            $mensaje_notif = "¡Buenas noticias! {$partido['equipo_visitante']} aceptó tu invitación de partido para el {$partido['fecha_partido']}.";
            $tipo_notif = "partido_aceptado";
        } else {
            $mensaje_notif = "{$partido['equipo_visitante']} rechazó tu invitación de partido del {$partido['fecha_partido']}.";
            $tipo_notif = "partido_rechazado";
        }

        crearNotificacion($conexion, $partido['id_creador'], $tipo_notif, $mensaje_notif);

        $conexion->commit();
        
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'texto' => $accion === 'aceptar' 
                ? '✅ Invitación aceptada correctamente.' 
                : '❌ Invitación rechazada.'
        ];
        
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => $e->getMessage()];
    }

    header("Location: responder_partido.php");
    exit;
}

// Obtener invitaciones pendientes
$query = $conexion->prepare("
    SELECT p.id_partido, 
           el.nombre_equipo AS equipo_local, 
           ev.nombre_equipo AS equipo_visitante,
           p.fecha_partido,
           DATEDIFF(p.fecha_partido, CURDATE()) AS dias_restantes
    FROM partidos p
    INNER JOIN equipos el ON el.id_equipo = p.id_equipo_local
    INNER JOIN equipos ev ON ev.id_equipo = p.id_equipo_visitante
    WHERE ev.id_capitan = ?
      AND p.estado = 'pendiente'
      AND p.fecha_partido >= CURDATE()
    ORDER BY p.fecha_partido ASC
");
$query->bind_param("i", $id_usuario);
$query->execute();
$invitaciones = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitaciones de Partidos | TSJ Sports</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        /* Alertas */
        .alerta {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        .alerta.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alerta.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cards de invitaciones */
        .invitaciones-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .equipos {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 20px 0;
        }

        .equipo {
            text-align: center;
            flex: 1;
        }

        .equipo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #667eea;
        }

        .equipo-nombre {
            font-weight: bold;
            color: #333;
            font-size: 0.9em;
        }

        .vs {
            font-size: 1.5em;
            font-weight: bold;
            color: #999;
            margin: 0 15px;
        }

        .detalles {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .detalle-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
            color: #555;
            font-size: 0.9em;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .badge.urgente {
            background: #ff6b6b;
            color: white;
        }

        .badge.proximo {
            background: #ffd93d;
            color: #333;
        }

        .badge.normal {
            background: #51cf66;
            color: white;
        }

        /* Botones de acción */
        .acciones {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1em;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .btn-aceptar {
            background: #28a745;
            color: white;
        }

        .btn-aceptar:hover {
            background: #218838;
        }

        .btn-rechazar {
            background: #dc3545;
            color: white;
        }

        .btn-rechazar:hover {
            background: #c82333;
        }

        /* Estado vacío */
        .no-data {
            background: white;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .no-data-icon {
            font-size: 5em;
            margin-bottom: 20px;
        }

        .no-data h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .no-data p {
            color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .invitaciones-grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📬 Invitaciones de Partidos</h1>

        <?php if ($mensaje_alerta): ?>
            <div class="alerta <?= $mensaje_alerta['tipo']; ?>">
                <span><?= htmlspecialchars($mensaje_alerta['texto']); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($invitaciones->num_rows > 0): ?>
            <div class="invitaciones-grid">
                <?php while ($inv = $invitaciones->fetch_assoc()): 
                    $dias = $inv['dias_restantes'];
                    $badge_class = $dias <= 2 ? 'urgente' : ($dias <= 7 ? 'proximo' : 'normal');
                    $badge_texto = $dias == 0 ? '¡HOY!' : ($dias == 1 ? 'Mañana' : "En {$dias} días");
                ?>
                    <div class="card">
                        <div class="card-header">
                            <span class="badge <?= $badge_class; ?>"><?= $badge_texto; ?></span>
                        </div>

                        <div class="equipos">
                            <div class="equipo">
                                <div style="width:60px;height:60px;background:linear-gradient(135deg, #667eea, #764ba2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;color:white;font-size:24px;">⚽</div>
                                <div class="equipo-nombre"><?= htmlspecialchars($inv['equipo_local']); ?></div>
                                <small style="color:#999;">Local</small>
                            </div>

                            <div class="vs">VS</div>

                            <div class="equipo">
                                <div style="width:60px;height:60px;background:linear-gradient(135deg, #f093fb, #f5576c);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;color:white;font-size:24px;">⚽</div>
                                <div class="equipo-nombre"><?= htmlspecialchars($inv['equipo_visitante']); ?></div>
                                <small style="color:#999;">Visitante</small>
                            </div>
                        </div>

                        <div class="detalles">
                            <div class="detalle-item">
                                <span>📅</span>
                                <strong><?= date('d/m/Y', strtotime($inv['fecha_partido'])); ?></strong>
                            </div>
                        </div>

                        <form method="POST" class="acciones" onsubmit="return confirm('¿Estás seguro de esta acción?');">
                            <input type="hidden" name="id_partido" value="<?= $inv['id_partido']; ?>">
                            <button type="submit" name="accion" value="aceptar" class="btn btn-aceptar">
                                ✓ Aceptar
                            </button>
                            <button type="submit" name="accion" value="rechazar" class="btn btn-rechazar">
                                ✗ Rechazar
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <div class="no-data-icon">🎉</div>
                <h2>No tienes invitaciones pendientes</h2>
                <p>Cuando recibas invitaciones de otros equipos aparecerán aquí.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-ocultar alertas después de 5 segundos
        setTimeout(() => {
            const alerta = document.querySelector('.alerta');
            if (alerta) {
                alerta.style.transition = 'opacity 0.5s';
                alerta.style.opacity = '0';
                setTimeout(() => alerta.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>