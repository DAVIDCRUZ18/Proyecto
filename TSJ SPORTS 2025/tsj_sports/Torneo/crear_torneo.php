<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";
include '../includes/verificar_admin.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para crear un torneo.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$errores = [];
$datos = [
    'nombre_torneo' => '',
    'descripcion' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'costo_inscripcion' => '0',
    'max_equipos' => '4'
];

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitizar y obtener datos
    $datos['nombre_torneo'] = trim($_POST["nombre_torneo"] ?? '');
    $datos['descripcion'] = trim($_POST["descripcion"] ?? '');
    $datos['fecha_inicio'] = trim($_POST["fecha_inicio"] ?? '');
    $datos['fecha_fin'] = trim($_POST["fecha_fin"] ?? '');
    $datos['costo_inscripcion'] = trim($_POST["costo_inscripcion"] ?? '0');
    $datos['max_equipos'] = trim($_POST["max_equipos"] ?? '');

    // ==============================
    // VALIDACIONES
    // ==============================
    
    // Validar nombre del torneo
    if (empty($datos['nombre_torneo'])) {
        $errores[] = "El nombre del torneo es obligatorio.";
    } elseif (strlen($datos['nombre_torneo']) < 3) {
        $errores[] = "El nombre del torneo debe tener al menos 3 caracteres.";
    } elseif (strlen($datos['nombre_torneo']) > 100) {
        $errores[] = "El nombre del torneo no puede exceder 100 caracteres.";
    }

    // Validar fechas
    if (empty($datos['fecha_inicio']) || empty($datos['fecha_fin'])) {
        $errores[] = "Debes ingresar ambas fechas.";
    } else {
        $fecha_inicio_ts = strtotime($datos['fecha_inicio']);
        $fecha_fin_ts = strtotime($datos['fecha_fin']);
        $fecha_actual = strtotime(date('Y-m-d'));

        if ($fecha_inicio_ts < $fecha_actual) {
            $errores[] = "La fecha de inicio no puede ser anterior a hoy.";
        }
        
        if ($fecha_fin_ts <= $fecha_inicio_ts) {
            $errores[] = "La fecha de fin debe ser posterior a la fecha de inicio.";
        }

        // Validar que el torneo no dure más de 1 año
        $diferencia_dias = ($fecha_fin_ts - $fecha_inicio_ts) / (60 * 60 * 24);
        if ($diferencia_dias > 365) {
            $errores[] = "El torneo no puede durar más de 1 año.";
        }
    }

    // Validar costo
    if (!is_numeric($datos['costo_inscripcion'])) {
        $errores[] = "El costo de inscripción debe ser un número válido.";
    } else {
        $costo = floatval($datos['costo_inscripcion']);
        if ($costo < 0) {
            $errores[] = "El costo de inscripción no puede ser negativo.";
        }
        if ($costo > 1000000) {
            $errores[] = "El costo de inscripción es demasiado alto.";
        }
    }

    // Validar máximo de equipos
    if (!is_numeric($datos['max_equipos'])) {
        $errores[] = "El máximo de equipos debe ser un número válido.";
    } else {
        $max_equipos = intval($datos['max_equipos']);
        if ($max_equipos < 2) {
            $errores[] = "El torneo debe tener al menos 2 equipos.";
        }
        if ($max_equipos > 64) {
            $errores[] = "El torneo no puede tener más de 64 equipos.";
        }
    }

    // Verificar nombre duplicado
    if (empty($errores)) {
        $checkNombre = $conexion->prepare("
            SELECT id_torneo 
            FROM torneos 
            WHERE nombre_torneo = ? 
            AND estado != 'cancelado'
        ");
        $checkNombre->bind_param("s", $datos['nombre_torneo']);
        $checkNombre->execute();
        if ($checkNombre->get_result()->num_rows > 0) {
            $errores[] = "Ya existe un torneo activo con ese nombre.";
        }
    }

    // Si no hay errores, guardar en BD
    if (empty($errores)) {
        $conexion->begin_transaction();
        
        try {
            $stmt = $conexion->prepare("
                INSERT INTO torneos 
                (nombre_torneo, descripcion, fecha_inicio, fecha_fin, costo_inscripcion, 
                 max_equipos, equipos_inscritos, estado, creado_por, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, 0, 'inscripciones_abiertas', ?, NOW())
            ");
            
            $stmt->bind_param(
                "ssssdii", 
                $datos['nombre_torneo'], 
                $datos['descripcion'], 
                $datos['fecha_inicio'], 
                $datos['fecha_fin'], 
                $costo, 
                $max_equipos, 
                $id_usuario
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error al guardar el torneo: " . $stmt->error);
            }

            $id_torneo = $conexion->insert_id;

            // Registrar en log de actividad (opcional)
            $logStmt = $conexion->prepare("
                INSERT INTO logs_actividad (id_usuario, accion, descripcion, fecha)
                VALUES (?, 'crear_torneo', ?, NOW())
            ");
            $log_desc = "Creó el torneo: " . $datos['nombre_torneo'];
            $logStmt->bind_param("is", $id_usuario, $log_desc);
            $logStmt->execute();

            $conexion->commit();

            $_SESSION['mensaje'] = "✅ Torneo '" . htmlspecialchars($datos['nombre_torneo']) . "' creado exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: ../dashboard.php?seccion=torneos");
            exit;

        } catch (Exception $e) {
            $conexion->rollback();
            $errores[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Torneo - TSJ Sports</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .body {
            background: #f4f7fa;
            font-family: Arial, sans-serif;
            color: #333;
            padding: 20px;
            background-image: url("img.png");
        }
        .form-torneo {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        .error-box {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .error-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .error-box li {
            color: #c00;
            margin: 5px 0;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #4CAF50;
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            background: #45a049;
        }
        .btn-secondary {
            background: #757575;
            color: white;
        }
        .btn-secondary:hover {
            background: #616161;
        }
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🏆 Crear Nuevo Torneo</h1>
    
    <?php if (!empty($errores)): ?>
        <div class="error-box">
            <strong>⚠️ Por favor corrige los siguientes errores:</strong>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST" class="form-torneo">
        <div class="form-group">
            <label class="required">Nombre del Torneo:</label>
            <input 
                type="text" 
                name="nombre_torneo" 
                maxlength="100"
                value="<?php echo htmlspecialchars($datos['nombre_torneo']); ?>"
                required
                placeholder="Ej: Copa TSJ 2025"
            >
            <small>Mínimo 3 caracteres, máximo 100</small>
        </div>

        <div class="form-group">
            <label>Descripción:</label>
            <textarea 
                name="descripcion" 
                maxlength="500"
                placeholder="Describe el torneo, reglas, premios, etc."
            ><?php echo htmlspecialchars($datos['descripcion']); ?></textarea>
            <small>Opcional - Máximo 500 caracteres</small>
        </div>

        <div class="form-group">
            <label class="required">Fecha de Inicio:</label>
            <input 
                type="date" 
                name="fecha_inicio" 
                value="<?php echo htmlspecialchars($datos['fecha_inicio']); ?>"
                min="<?php echo date('Y-m-d'); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label class="required">Fecha de Fin:</label>
            <input 
                type="date" 
                name="fecha_fin" 
                value="<?php echo htmlspecialchars($datos['fecha_fin']); ?>"
                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Costo de Inscripción:</label>
            <input 
                type="number" 
                name="costo_inscripcion" 
                step="0.01" 
                min="0"
                max="1000000"
                value="<?php echo htmlspecialchars($datos['costo_inscripcion']); ?>"
                placeholder="0.00"
            >
            <small>En pesos colombianos (COP) - Dejar en 0 si es gratuito</small>
        </div>

        <div class="form-group">
            <label class="required">Máximo de Equipos:</label>
            <input 
                type="number" 
                name="max_equipos" 
                min="2" 
                max="64"
                value="<?php echo htmlspecialchars($datos['max_equipos']); ?>"
                required
            >
            <small>Entre 2 y 64 equipos</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                ✅ Crear Torneo
            </button>
            <a href="../dashboard.php?seccion=torneos" class="btn btn-secondary">
                ← Cancelar
            </a>
        </div>
    </form>
</div>
</body>
</html>