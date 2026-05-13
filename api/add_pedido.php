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

$data = json_decode(file_get_contents("php://input"), true);
if(!$data || empty($data)){
    echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
    exit;
}
//Preparamos los datos

$fecha = $data['fecha'] ?? '';
$cliente_id = $data['cliente_id'] ?? '';
$total = $data['total'] ?? '';
$lineas = $data['lineas'] ?? [];

if(!$cliente_id || !$total || empty($lineas)){
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

try{
    $db->beginTransaction();

    // Insertar el pedido
    $stmt = $db->prepare("INSERT INTO pedidos (fecha, cliente_id, total) VALUES (? ,?, ?)"); 
    $pedidoResultado = $stmt->execute([$fecha, $cliente_id, $total]);

    if(!$pedidoResultado){
        throw new Exception("Error al insertar el pedido");
    }

    //Obtenemos el id del pedido recién insertado
    $pedido_id = $db->lastInsertId();

    //Insertamos las lineas de peiddo 

    $stmtLineas = $db->prepare("INSERT INTO lineas_pedido (pedido_id, producto_nombre, cantidad, dto, precio_unitario, total, categoria) VALUES (?,?,?,?,?,?,?)");
  
    foreach($lineas as $linea){
       $producto_nombre = $linea['producto_nombre'] ?? '';
       $cantidad = $linea['cantidad'] ?? 0;
       $dto = $linea['dto'] ?? 0;
       $precio_unitario = $linea['precio_unitario'] ?? 0;
       $totalLinea =$linea['total'] ?? 0; 
       $categoria = $linea['categoria'] ?? '';

       if(!$producto_nombre || $cantidad <= 0 || $precio_unitario <= 0){
           throw new Exception("Datos inválidos en una línea de pedido");
       }
       $lineaResultado = $stmtLineas->execute([$pedido_id, $producto_nombre, $cantidad, $dto, $precio_unitario, $totalLinea, $categoria]);

       if(!$lineaResultado){
           throw new Exception("Error al insertar una línea de pedido");
       }
    }
    $db->commit(); 
    echo json_encode([
        'success' => true,
        'message' => 'Pedido creado exitosamente',
        'pedido_id' => $pedido_id, 
        'lineas' => $lineas
    ]); 
}catch(Exception $e){
    $db->rollBack(); 
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el pedido: ' . $e->getMessage()
    ]);
}
?>
