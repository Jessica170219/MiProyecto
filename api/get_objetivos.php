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

$db = Database::getInstance();
$sql = "SELECT categoria, valor FROM objetivos_trimestrales WHERE anio = ? AND trimestre = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$anio, $trimestre]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$objetivos = [
    'facturacion' => 0,
    'intima' => 0,
    'menopausia' => 0,
    'furaderm' => 0,
    'gastro' => 0,
    'copromo' => 0
];

//Obtenemos el objetivo anual de copromo 
$sqlAnual = "SELECT copromo FROM objetivos_anuales WHERE anio = ?";
$stmtAnual=$db->prepare($sqlAnual);
$stmtAnual->execute([$anio]);
$copromo=$stmtAnual->fetchColumn() ?? 0;
$objetivos['copromo'] = (float)$copromo;

foreach ($resultados as $row) {
    $objetivos[$row['categoria']] = (float)$row['valor'];
}
echo json_encode([
    'success' => true, 
    'objetivos' => $objetivos
]);
?>