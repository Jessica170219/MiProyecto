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

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id_pedido) {
    echo json_encode(["success" => false, "message" => "ID de pedido no proporcionado"]);
    exit;
}

$db = Database::getInstance();

try{
    $sql = "SELECT
        lp.id,
        lp.producto_nombre,
        lp.cantidad,
        lp.dto,
        lp.precio_unitario,
        lp.total,
        lp.categoria
    FROM lineas_pedido lp
    WHERE lp.pedido_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id_pedido]);
    $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "lineas" => $lineas]);
} catch(Exception $e){
    echo json_encode(["success" => false, "message" => "Error al obtener las líneas del pedido: " . $e->getMessage()]);
}
?> 