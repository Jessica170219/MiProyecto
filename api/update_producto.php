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
$nombre = $data['nombre'] ?? '';
$grupo = $data['grupo'] ?? '';
$gama = $data['gama'] ?? '';
$cn = $data['cn'] ?? '';
$pvl = $data['pvl'] ?? 0;
$pvp = $data['pvp'] ?? 0;
$iva = $data['iva'] ?? 0;
$importe = $data['importe'] ?? 0;

$db = Database::getInstance();

$stmt = $db->prepare("UPDATE productos SET nombre = ?, grupo = ?, gama = ?, cn = ?, pvl = ?, pvp = ?, iva = ?, importe = ? WHERE id = ?");
$result = $stmt->execute([$nombre, $grupo, $gama, $cn, $pvl, $pvp, $iva, $importe, $id]);

if($result) {
    echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto']);
}

?>
