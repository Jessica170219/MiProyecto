<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

header("Content-Type: application/json");
require_once 'config.php';

// Obtener mes y año (por defecto mes actual)
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

$db = Database::getInstance();

try {
    // Suma por categoría de las líneas de pedidos del mes indicado
    // Se asume que la tabla lineas_pedido tiene columna 'categoria' y 'total', y que pedidos tiene 'fecha'
    $sql = "SELECT lp.categoria, SUM(lp.total) as total
            FROM lineas_pedido lp
            INNER JOIN pedidos p ON lp.pedido_id = p.id
            WHERE MONTH(p.fecha) = ? AND YEAR(p.fecha) = ?
            GROUP BY lp.categoria";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$mes, $anio]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inicializar todas las categorías con 0
    $totales = [
        'FARMA' => 0,
        'MEDICA' => 0,
        'AMBOS' => 0,
        'NADIE' => 0   // 'Nadie' en el frontend se corresponde con categoría VACIA
    ];
    
    foreach ($resultados as $row) {
        $categoria = $row['categoria'];
        if (isset($totales[$categoria])) {
            $totales[$categoria] = (float)$row['total'];
        }
    }
    
    echo json_encode(['success' => true, 'totales' => $totales, 'mes' => $mes, 'anio' => $anio]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener totales: ' . $e->getMessage()]);
}
?>