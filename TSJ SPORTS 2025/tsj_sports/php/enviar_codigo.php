// ============================================
// ARCHIVO 1: php/recuperar/enviar_codigo.php
// ============================================
<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email requerido']);
    exit;
}

// Verificar si el email existe
$query = $conexion->prepare("SELECT id_usuario, usuario FROM usuarios WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No existe una cuenta con ese email']);
    exit;
}

$usuario = $result->fetch_assoc();

// Generar código de 6 dígitos
$codigo = rand(100000, 999999);
$expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Guardar o actualizar el código en la base de datos
$check = $conexion->prepare("SELECT id FROM recuperacion_password WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    // Actualizar código existente
    $update = $conexion->prepare("UPDATE recuperacion_password SET codigo = ?, expiracion = ?, usado = 0 WHERE email = ?");
    $update->bind_param("sss", $codigo, $expiracion, $email);
    $update->execute();
} else {
    // Insertar nuevo código
    $insert = $conexion->prepare("INSERT INTO recuperacion_password (email, codigo, expiracion, usado) VALUES (?, ?, ?, 0)");
    $insert->bind_param("sss", $email, $codigo, $expiracion);
    $insert->execute();
}

// OPCIÓN 1: Enviar email real (requiere configuración de email)
// Aquí necesitarías configurar PHPMailer o una librería similar
/*
require '../vendor/autoload.php';
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'tu-password-app';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->setFrom('noreply@tsjsports.com', 'TSJ SPORTS');
$mail->addAddress($email);
$mail->Subject = 'Código de Recuperación - TSJ SPORTS';
$mail->Body = "Hola {$usuario['usuario']},\n\nTu código de recuperación es: {$codigo}\n\nEste código expira en 15 minutos.\n\nSi no solicitaste este código, ignora este mensaje.\n\nSaludos,\nEquipo TSJ SPORTS";

if ($mail->send()) {
    echo json_encode(['success' => true, 'message' => 'Código enviado a tu email']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar el email']);
}
*/

// ============================================
// ARCHIVO 2: php/recuperar/verificar_codigo.php
// ============================================
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$codigo = trim($data['code'] ?? '');

if (empty($email) || empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Verificar código
$query = $conexion->prepare("
    SELECT * FROM recuperacion_password 
    WHERE email = ? AND codigo = ? AND usado = 0 AND expiracion > NOW()
");
$query->bind_param("ss", $email, $codigo);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Código inválido o expirado']);
    exit;
}

// Generar token temporal
$token = bin2hex(random_bytes(32));
$expiracion_token = date('Y-m-d H:i:s', strtotime('+30 minutes'));

// Actualizar con token
$update = $conexion->prepare("UPDATE recuperacion_password SET token = ?, expiracion = ? WHERE email = ? AND codigo = ?");
$update->bind_param("ssss", $token, $expiracion_token, $email, $codigo);
$update->execute();

echo json_encode([
    'success' => true,
    'token' => $token,
    'message' => 'Código verificado correctamente'
]);

// ============================================
// ARCHIVO 3: php/recuperar/cambiar_contrasena.php
// ============================================
?>
<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . "/TSJ SPORTS 2025/tsj_sports/php/conexion.php";

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$token = trim($data['token'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($email) || empty($token) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

// Verificar token
$query = $conexion->prepare("
    SELECT * FROM recuperacion_password 
    WHERE email = ? AND token = ? AND usado = 0 AND expiracion > NOW()
");
$query->bind_param("ss", $email, $token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Token inválido o expirado']);
    exit;
}

// Hash de la nueva contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Actualizar contraseña del usuario
$update = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE email = ?");
$update->bind_param("ss", $password_hash, $email);

if ($update->execute()) {
    // Marcar el código como usado
    $marcar = $conexion->prepare("UPDATE recuperacion_password SET usado = 1 WHERE email = ?");
    $marcar->bind_param("s", $email);
    $marcar->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Contraseña actualizada correctamente'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
}
?>