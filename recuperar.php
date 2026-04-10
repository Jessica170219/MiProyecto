<!doctype html>
<html lang="es">
<head>
  <title>Recuperar Contraseña - SEID Farmacia</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Bootstrap 4 CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    body { 
      background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%); 
      font-family: 'Segoe UI', sans-serif; 
    }
    .logo { max-height: 60px; margin-bottom: 20px; }
    .card-sign { 
      box-shadow: 0 10px 30px rgba(0,91,150,0.2); 
      border: none; 
      border-radius: 15px; 
      border-top: 4px solid #005B96;
    }
    .btn-primary { 
      background: #005B96; 
      border-color: #005B96; 
      font-weight: bold; 
    }
    .btn-primary:hover { 
      background: #003D6B; 
      border-color: #003D6B; 
    }
    .text-success { color: #005B96 !important; }
    .alert { border-radius: 10px; }
  </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-3">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 col-xl-5">
        <!-- Logo SEID -->
        <div class="text-center mb-4">
          <a href="login.php" class="logo">
            <img src="img/logoS.png" alt="SEID Farmacia Logo" class="img-fluid">
          </a>
          <h4 class="text-muted mt-2">Recuperar Contraseña</h4>
        </div>

        <!-- Card Recuperar -->
        <div class="card card-sign">
          <div class="card-body p-4">
            <?php
            if ($_POST['email']) {
              // CONEXIÓN A BASE DE DATOS (ajusta tus datos)
              $servidor = "localhost";
              $usuario = "root";
              $password = "";
              $base_datos = "farmacia_seid"; // Cambia por tu BD

              try {
                $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
                $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $email = $_POST['email'];
                
                // Verificar si el email existe
                $stmt = $conexion->prepare("SELECT id, email FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                  // Generar token único
                  $token = md5(uniqid($email, true));
                  
                  // Guardar token en BD con expiración (1 hora)
                  $stmt = $conexion->prepare("UPDATE users SET reset_token = ?, token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
                  $stmt->execute([$token, $email]);

                  // Enviar email (configura tu servidor SMTP)
                  $reset_link = "http://localhost/seid/reset.php?token=" . $token;
                  $asunto = "Recuperar Contraseña - SEID Farmacia";
                  $mensaje = "
                  <h2>SEID Farmacia - Recuperar Contraseña</h2>
                  <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
                  <a href='$reset_link' style='background: #005B96; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a>
                  <p>El enlace expira en 1 hora.</p>
                  <p>Si no solicitaste esto, ignora este email.</p>
                  ";
                  $headers = "MIME-Version: 1.0\r\n";
                  $headers .= "Content-type: text/html; charset=utf-8\r\n";
                  $headers .= "From: SEID Farmacia <no-reply@seid.com>\r\n";

                  if (mail($email, $asunto, $mensaje, $headers)) {
                    echo '<div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x mb-3 d-block"></i>
                            ¡Email enviado!<br>
                            Revisa tu bandeja de entrada (y spam) para el enlace de recuperación.
                          </div>';
                  } else {
                    echo '<div class="alert alert-warning text-center">
                            Error al enviar email. Contacta soporte.
                          </div>';
                  }
                } else {
                  echo '<div class="alert alert-danger text-center">
                          Email no encontrado en nuestra base de datos.
                        </div>';
                }
              } catch(PDOException $e) {
                echo '<div class="alert alert-danger text-center">Error de conexión: ' . $e->getMessage() . '</div>';
              }
            }
          ?>

            <form action="recuperar.php" method="post">
              <div class="form-group mb-4">
                <label class="font-weight-bold text-dark mb-3">Ingresa tu Email:</label>
                <div class="input-group input-group-lg">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0">
                      <i class="fas fa-envelope text-success"></i>
                    </span>
                  </div>
                  <input type="email" class="form-control border-left-0 pl-0" name="email" 
                         placeholder="tu-email@ejemplo.com" required>
                </div>
              </div>

              <div class="row">
                <div class="col-6">
                  <a href="login.php" class="btn btn-outline-secondary btn-lg btn-block">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                  </a>
                </div>
                <div class="col-6">
                  <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Email
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
