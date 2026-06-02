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

$anio = $_GET['anio'] ?? date('Y');
$trimestre = $_GET['trimestre'] ?? ceil(date('n')/3);

// Calcular meses del trimestre
$mes_inicio = ($trimestre - 1) * 3 + 1;
$mes_fin = $trimestre * 3;
$fecha_inicio = "$anio-" . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . "-01";
$fecha_fin = "$anio-" . str_pad($mes_fin, 2, '0', STR_PAD_LEFT) . "-31";

$db = Database::getInstance();

// 1. Facturación total del trimestre (suma de total de pedidos)
$sql = "SELECT SUM(total) as total FROM pedidos WHERE fecha BETWEEN ? AND ?";
$stmt = $db->prepare($sql);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$facturacion = $stmt->fetchColumn() ?: 0;

// 2. Distribuciones por categoría de gama
$categorias = [
    'intima' => 12,
    'furaderm' => 12,
    'menopausia' => 6,
    'gastro' => 15
];
$distribuciones = [];

foreach ($categorias as $cat => $umbral) {
    // Obtener farmacias que han pedido >=12 unidades de productos con esa gama en el trimestre
    $sql = "SELECT lp.pedido_id, p.cliente_id, SUM(lp.cantidad) as total_unidades
            FROM lineas_pedido lp
            JOIN pedidos p ON lp.pedido_id = p.id
            JOIN productos pr ON lp.producto_nombre = pr.nombre
            WHERE pr.gama = ? AND p.fecha BETWEEN ? AND ?
            GROUP BY p.cliente_id
            HAVING SUM(lp.cantidad) >= ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$cat, $fecha_inicio, $fecha_fin,$umbral]);
    $farmacias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contamos cada farmacia solo una vez (ya agrupado por cliente_id)
    $distribuciones[$cat] = count($farmacias);
}


//Calculo de distribuciones para copromo (anual)

$fecha_inicio_anio = "$anio-01-01";
$sqlCopromo = "SELECT COUNT(DISTINCT p.cliente_id) as total
               FROM lineas_pedido lp
               JOIN pedidos p ON lp.pedido_id = p.id
               JOIN productos pr ON lp.producto_nombre = pr.nombre
               WHERE pr.gama = 'copromo'
                 AND p.fecha BETWEEN ? AND ?";
$stmt = $db->prepare($sqlCopromo);
$stmt->execute([$fecha_inicio_anio, $fecha_fin]);
$distribuciones['copromo'] = (int)$stmt->fetchColumn();


// Inicializar valores por defecto si faltan
$todas_categorias = array_merge($categorias, ['copromo']);
foreach ($todas_categorias as $cat) {
    if (!isset($distribuciones[$cat])) {
        $distribuciones[$cat] = 0;
    }
}

echo json_encode([
    'success' => true,
    'facturacion' => (float)$facturacion,
    'distribuciones' => $distribuciones
]);
?>