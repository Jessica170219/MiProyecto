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

// Inicializar variables
$mes_num = null;
$anio_num = null;

// Si se envía 'mes' con formato YYYY-MM (ej. 2025-05)
if (isset($_GET['mes']) && preg_match('/^\d{4}-\d{2}$/', $_GET['mes'])) {
    $mes_partes = explode('-', $_GET['mes']);
    $anio_num = (int)$mes_partes[0];
    $mes_num = (int)$mes_partes[1];
} 
// Si se envía 'mes' como número (1-12) y 'anio' como número
elseif (isset($_GET['mes']) && isset($_GET['anio'])) {
    $mes_num = (int)$_GET['mes'];
    $anio_num = (int)$_GET['anio'];
}
// Si solo se envía 'anio' (para filtro anual)
elseif (isset($_GET['anio']) && ctype_digit($_GET['anio'])) {
    $anio_num = (int)$_GET['anio'];
    $mes_num = null; // null indica que filtramos solo por año
}
// Si no hay filtros, usamos el mes actual
else {
    $anio_num = (int)date('Y');
    $mes_num = (int)date('n');
}

$db = Database::getInstance();

try {
    // Construir la consulta según si hay filtro de mes o solo año
    if ($mes_num !== null) {
        $sql = "SELECT lp.categoria, SUM(lp.total) as total
                FROM lineas_pedido lp
                INNER JOIN pedidos p ON lp.pedido_id = p.id
                WHERE MONTH(p.fecha) = ? AND YEAR(p.fecha) = ?
                GROUP BY lp.categoria";
        $stmt = $db->prepare($sql);
        $stmt->execute([$mes_num, $anio_num]);
    } else {
        $sql = "SELECT lp.categoria, SUM(lp.total) as total
                FROM lineas_pedido lp
                INNER JOIN pedidos p ON lp.pedido_id = p.id
                WHERE YEAR(p.fecha) = ?
                GROUP BY lp.categoria";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio_num]);
    }
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inicializar todas las categorías con 0 (incluyendo NADIE que corresponde a 'VACIA' en BD)
    $totales = [
        'FARMA' => 0,
        'MEDICA' => 0,
        'AMBOS' => 0,
        'NADIE' => 0
    ];
    
    foreach ($resultados as $row) {
        $categoria = $row['categoria'];
        // Mapear 'VACIA' a 'NADIE' (por si en BD se guarda como 'VACIA')
        if ($categoria === 'VACIA') {
            $categoria = 'NADIE';
        }
        if (isset($totales[$categoria])) {
            $totales[$categoria] = (float)$row['total'];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'totales' => $totales,
        'filtro' => $mes_num !== null ? "mes $mes_num/$anio_num" : "año $anio_num"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener totales: ' . $e->getMessage()]);
}
?>