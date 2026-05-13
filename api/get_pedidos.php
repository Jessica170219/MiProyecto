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

$db = Database::getInstance();
$stmt = $db->prepare("
    SELECT 
        p.id,
        p.fecha,
        p.cliente_id,
        p.total,
        c.farmacia AS farmacia
    FROM pedidos p
    INNER JOIN clientes c ON p.cliente_id = c.id
    ORDER BY p.fecha DESC");


$stmt->execute();
$pedidos= $stmt->fetchAll();
echo json_encode([
    "success" => true, 
    "pedidos" => $pedidos
    ]);
?>
