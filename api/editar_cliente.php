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

//Leemos el JSON que viene de Vue
$data = json_decode(file_get_contents("php://input"), true);

if(!$data ||!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID del cliente es requerido']);
    exit;
}

//Recogemos datos 
$id = $data['id'];
$farmacia = $data['farmacia'] ?? '';
$direccion = $data['direccion'] ?? '';
$provincia = $data['provincia'] ?? '';
$municipio = $data['municipio'] ?? '';
$codigo_postal = $data['codigo_postal'] ?? '';
$email = $data['email'] ?? '';
$telefono = $data['telefono'] ?? '';

$db = Database::getInstance();

$stmt = $db->prepare("UPDATE clientes SET farmacia = ?, direccion = ?, provincia = ?, municipio = ?, codigo_postal = ?, email = ?, telefono = ? WHERE id = ?");
$result = $stmt->execute([$farmacia, $direccion, $provincia, $municipio, $codigo_postal, $email, $telefono, $id]);

if($result) {
    echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el cliente']);
}

?>


