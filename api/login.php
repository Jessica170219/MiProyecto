<?php 
// 1. Permitir que el puerto de Vue (5173) acceda
header("Access-Control-Allow-Origin: http://localhost:5173");

// 2. Permitir que se envíen JSON y headers personalizados
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 3. Permitir los métodos necesarios
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// 4. Permitir el envío de Cookies/Sesiones
header("Access-Control-Allow-Credentials: true");

// 5. IMPORTANTE: Manejar la petición "preflight" de los navegadores
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}
header("Content-Type: application/json");
require_once 'config.php';


// 1. Recibir los datos de Vue (vienen como JSON, no como $_POST)
$input = json_decode(file_get_contents("php://input"), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

// 2. Validar que se recibieron ambos campos
if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email y contraseña son requeridos']);
    exit;
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT id, email, password FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(); 


if ($user && password_verify($password, $user['password'])) {
    // Guardamos datos en la sesión de PHP por seguridad
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['loggedin'] = true;

    echo json_encode([
        "success" => true, 
        "user" => [
            "id" => $user['id'],
            "email" => $user['email']
        ]
    ]);
} else {
    http_response_code(401); // Error de autenticación
    echo json_encode(["success" => false, "message" => "Email o contraseña incorrectos"]);
}
