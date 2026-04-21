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

$mes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');

$stmt = $db->prepare("SELECT lavados,comida,gasolina FROM topes_mensuales WHERE mes = ?"); 
$stmt->execute([$mes]);
$topes =$stmt->fetch(PDO::FETCH_ASSOC);


if($topes) {
    echo json_encode([
        "success" => true, 
        "topes" =>[
            'lavados' => $topes['lavados'],
            'comida' => $topes['comida'],
            'gasolina' => $topes['gasolina']
        ]
    ]);
} else {
    echo json_encode([
        "success" => false, 
        "message" => "No se encontraron topes para el mes especificado."
    ]);
}
?>