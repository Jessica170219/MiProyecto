<?php
session_start();
require_once 'erp/config.php'; 


if (mysqli_connect_error()) {
    die("❌ ERROR conexión: " . mysqli_connect_error());
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// Validaciones básicas
if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Email o contraseña inválidos';
    header('Location: login.php');
    exit;
}

// Consulta PREPARADA (✅ ANTI-SQL INJECTION)
$stmt = mysqli_prepare($conn, "SELECT id, email, password, nombre, activo FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Verificar credenciales Y activo
if (!$row || !password_verify($password, $row['password']) || !$row['activo']) {
    $_SESSION['error'] = 'Email o contraseña incorrectos';
    header('Location: login.php');
    exit;
}
// ✅ USUARIO VÁLIDO - Actualizar último login
$stmt = mysqli_prepare($conn, "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $row['id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Crear sesión SEGURA
$_SESSION['loggedin'] = true;
$_SESSION['user_id'] = $row['id'];
$_SESSION['email'] = $row['email'];
$_SESSION['name'] = $row['nombre'];
$_SESSION['start'] = time();
$_SESSION['expire'] = $_SESSION['start'] + (1 * 3600); // 1 hora

mysqli_close($conn);
header('Location: ../dashboard.php');
exit;


?>