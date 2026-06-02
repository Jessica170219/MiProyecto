<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

// Validar formato email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Validar longitud contraseña
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

$db = Database::getInstance();

// Verificar si el email ya existe
$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
    exit;
}

// Hashear contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar usuario
$stmt = $db->prepare("INSERT INTO usuarios (email, password) VALUES (?, ?)");
if ($stmt->execute([$email, $hash])) {
    echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el usuario']);
}
?>