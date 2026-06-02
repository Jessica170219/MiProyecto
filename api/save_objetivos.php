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


$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
    exit;
}

$anio = $data['anio'] ?? date('Y');
$trimestre = $data['trimestre'] ?? ceil(date('n')/3);
$objetivos = $data['objetivos'] ?? [];

$db = Database::getInstance();

try {
    foreach ($objetivos as $categoria => $valor) {
        if($categoria === 'copromo') {
            // Guardar objetivo anual de copromo
            $sqlAnual = "INSERT INTO objetivos_anuales (anio, copromo) VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE copromo = VALUES(copromo)";
            $stmtAnual = $db->prepare($sqlAnual);
            $stmtAnual->execute([$anio, $valor]);
        
        } else {
        $sql = "INSERT INTO objetivos_trimestrales (anio, trimestre, categoria, valor) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio, $trimestre, $categoria, $valor]);
        } 
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>