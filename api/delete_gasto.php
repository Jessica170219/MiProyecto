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


$data = json_decode(file_get_contents("php://input"), true);

if(!$data || empty($data)){
    echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
    exit;
}

$db = Database::getInstance();
$id = $data['id'] ?? 0;
$stmt = $db->prepare("DELETE FROM gastos WHERE id = ?");
$result = $stmt->execute([$id]);
if ($result) {
    echo json_encode(['success' => true, 'message' => 'Gasto eliminado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}

?>