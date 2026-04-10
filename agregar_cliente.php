<?php
require_once 'erp/config.php'; 
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmacia = trim($_POST['farmacia'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $municipio = trim($_POST['municipio'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (empty($farmacia)) {
        $response['message'] = 'El campo "Farmacia" es obligatorio.';
    } else {
        $sql = "INSERT INTO clientes (farmacia, direccion, provincia, municipio, codigo_postal, email, telefono) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssss', 
            $farmacia,
            $direccion,
            $provincia,
            $municipio,
            $codigo_postal,
            $email,
            $telefono
        );

        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Farmacia agregada correctamente.';
        } else {
            $response['message'] = 'Error al agregar: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>