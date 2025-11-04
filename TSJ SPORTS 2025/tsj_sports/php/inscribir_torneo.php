<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/validar_sesion.php";
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];

// Verificar que el usuario sea capitán
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
if (!$miEquipo['es_capitan']) {
    die("Error: Solo el capitán del equipo puede inscribir al equipo en torneos.");
}

$id_equipo = $miEquipo['id_equipo'];

// Obtener ID del torneo
$id_torneo = isset($_GET['id_torneo']) ? intval($_GET['id_torneo']) : 0;

// Obtener información del torneo
$torneoQuery = $conexion->prepare("
    SELECT * FROM torneos WHERE id_torneo = ? AND estado = 'inscripciones_abiertas'
");
$torneoQuery->bind_param("i", $id_torneo);
$torneoQuery->execute();
$torneoResult = $torneoQuery->get_result();

if ($torneoResult->num_rows === 0) {
    die("Error: Torneo no encontrado o inscripciones cerradas.");
}

$torneo = $torneoResult->fetch_assoc();

// Verificar si ya está inscrito
$inscritoQuery = $conexion->prepare("
    SELECT * FROM torneo_inscripciones WHERE id_torneo = ? AND id_equipo = ?
");
$inscritoQuery->bind_param("ii", $id_torneo, $id_equipo);
$inscritoQuery->execute();
$yaInscrito = $inscritoQuery->get_result()->num_rows > 0;

// Verificar si hay cupos disponibles
$cuposDisponibles = $torneo['max_equipos'] - $torneo['equipos_inscritos'];

// Procesar inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$yaInscrito && $cuposDisponibles > 0) {
    $comprobante = '';
    
    // Manejar subida de comprobante si existe
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/uploads/comprobantes/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "comprobante_" . $id_torneo . "_" . $id_equipo . "_" . time() . "." . $extension;
        $ruta_completa = $upload_dir . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_completa)) {
            $comprobante = $nombre_archivo;
        }
    }
    
    // Insertar inscripción
    $insertInscripcion = $conexion->prepare("
        INSERT INTO torneo_inscripciones (id_torneo, id_equipo, comprobante_pago, pagado) 
        VALUES (?, ?, ?, 0)
    ");
    $insertInscripcion->bind_param("iis", $id_torneo, $id_equipo, $comprobante);
    
    if ($insertInscripcion->execute()) {
        // Actualizar contador de equipos inscritos
        $conexion->query("UPDATE torneos SET equipos_inscritos = equipos_inscritos + 1 WHERE id_torneo = $id_torneo");
        
        // Crear notificación
        $mensaje = "Tu equipo {$miEquipo['nombre_equipo']} ha sido inscrito en el torneo {$torneo['nombre_torneo']}. Pendiente de pago.";
        $insertNotif = $conexion->prepare("INSERT INTO notificaciones (id_usuario, tipo, mensaje, leida) VALUES (?, 'torneo_inscripcion', ?, 0)");
        $insertNotif->bind_param("is", $id_usuario, $mensaje);
        $insertNotif->execute();
        
        $_SESSION['mensaje'] = "¡Inscripción exitosa! Recuerda realizar el pago para confirmar tu participación.";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: /TSJ SPORTS 2025/tsj_sports/dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscribir Torneo - TSJ SPORTS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .torneo-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .info-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; }
        .btn { padding: 14px 28px; border: none; border-radius: 8px; cursor: pointer; font-size: 1em; font-weight: bold; width: 100%; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
        .alert-success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .back-btn { display: inline-block; padding: 10px 20px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 6px; margin-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🏆 Inscripción a Torneo</h1>
        <p>Equipo: <strong><?php echo htmlspecialchars($miEquipo['nombre_equipo']); ?></strong></p>
        <a href="/TSJ SPORTS 2025/tsj_sports/dashboard.php" class="back-btn">← Volver al Dashboard</a>
    </div>

    <?php if ($yaInscrito): ?>
        <div class="alert alert-success">
            <strong>✅ Ya estás inscrito en este torneo</strong>
            <p>Tu equipo ya está registrado en este torneo.</p>
        </div>
        <a href="/TSJ SPORTS 2025/tsj_sports/dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
    <?php elseif ($cuposDisponibles <= 0): ?>
        <div class="alert alert-warning">
            <strong>⚠️ Torneo Completo</strong>
            <p>Lo sentimos, no hay cupos disponibles para este torneo.</p>
        </div>
        <a href="todos_torneos.php" class="btn btn-primary">Ver Otros Torneos</a>
    <?php else: ?>
        <div class="card">
            <h2>📋 Información del Torneo</h2>
            <div class="torneo-info">
                <h3 style="color: #667eea; margin-bottom: 15px;"><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h3>
                <p style="color: #666; margin-bottom: 15px;"><?php echo htmlspecialchars($torneo['descripcion']); ?></p>
                
                <div class="info-item">
                    <strong>📅 Fecha de inicio:</strong>
                    <span><?php echo date('d/m/Y', strtotime($torneo['fecha_inicio'])); ?></span>
                </div>
                <div class="info-item">
                    <strong>📅 Fecha de fin:</strong>
                    <span><?php echo date('d/m/Y', strtotime($torneo['fecha_fin'])); ?></span>
                </div>
                <div class="info-item">
                    <strong>💰 Costo de inscripción:</strong>
                    <span>$<?php echo number_format($torneo['costo_inscripcion'], 0); ?></span>
                </div>
                <div class="info-item">
                    <strong>👥 Cupos disponibles:</strong>
                    <span><?php echo $cuposDisponibles; ?> de <?php echo $torneo['max_equipos']; ?></span>
                </div>
                <?php if ($torneo['premio_descripcion']): ?>
                <div class="info-item" style="border-bottom: none;">
                    <strong>🏅 Premio:</strong>
                    <span><?php echo htmlspecialchars($torneo['premio_descripcion']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($torneo['reglas']): ?>
                <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <strong style="color: #0066cc;">📜 Reglas del Torneo:</strong>
                    <p style="margin-top: 10px; color: #333;"><?php echo nl2br(htmlspecialchars($torneo['reglas'])); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>📄 Comprobante de Pago (Opcional)</label>
                    <input type="file" name="comprobante" accept="image/*,.pdf">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Puedes subir el comprobante ahora o después. Formatos: JPG, PNG, PDF
                    </small>
                </div>

                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <strong style="color: #856404;">⚠️ Importante:</strong>
                    <ul style="margin-left: 20px; margin-top: 10px; color: #856404;">
                        <li>La inscripción quedará pendiente hasta confirmar el pago</li>
                        <li>Debes realizar la transferencia al número de cuenta indicado</li>
                        <li>Tu participación se confirmará una vez verificado el pago</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary">
                    ✅ Confirmar Inscripción
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>