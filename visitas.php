<?php
session_start();
require_once 'erp/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || time() > $_SESSION['expire']) {
    session_destroy();
    header('Location: login.php'); 
    exit;
}

$mensaje = '';
$busqueda_farmacia = isset($_GET['busqueda_farmacia']) ? trim($_GET['busqueda_farmacia']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Obtener lista de farmacias para autocompletar (datalist)
$sql_farmacias = "SELECT farmacia FROM clientes ORDER BY farmacia ASC";
$result_farmacias = mysqli_query($conn, $sql_farmacias);
$farmacias_list = [];
while ($row = mysqli_fetch_assoc($result_farmacias)) {
    $farmacias_list[] = htmlspecialchars($row['farmacia']);
}

// Procesar el formulario de agregar visita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $farmacia = trim($_POST['farmacia'] ?? 'f');
    $observacion = trim($_POST['observacion'] ?? '');

    if (empty($farmacia)) {
        $mensaje = '<div class="alert alert-danger">El nombre de la farmacia es obligatorio.</div>';
    } else {
        $sql = "INSERT INTO visitas (fecha, farmacia, observacion) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $fecha, $farmacia, $observacion);
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = '<div class="alert alert-success">Visita registrada correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al registrar: ' . mysqli_error($conn) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

// Consulta de visitas con filtros
$sql_visitas = "SELECT id, fecha, farmacia, observacion FROM visitas WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda_farmacia)) {
    $sql_visitas .= " AND farmacia LIKE ?";
    $like = "%$busqueda_farmacia%";
    $params[] = $like;
    $types .= "s";
}
if (!empty($fecha_desde)) {
    $sql_visitas .= " AND fecha >= ?";
    $params[] = $fecha_desde;
    $types .= "s";
}
if (!empty($fecha_hasta)) {
    $sql_visitas .= " AND fecha <= ?";
    $params[] = $fecha_hasta;
    $types .= "s";
}

$sql_visitas .= " ORDER BY fecha DESC, id DESC";

$stmt = mysqli_prepare($conn, $sql_visitas);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result_visitas = mysqli_stmt_get_result($stmt);
$visitas = mysqli_fetch_all($result_visitas, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!doctype html>
<html lang="es">
<head>
  <title>Visitas - SEID Farmacia</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
    .main-content.visitas-bg {
      background: #f8f9fc;
    }
    .form-quick {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 2rem;
    }
    /* Mejora para móviles: aumentar tamaño de toque en botones */
    @media (max-width: 768px) {
      .btn {
        padding: 0.6rem 1rem;
      }
      input, select, .btn {
        font-size: 16px; /* Evita zoom en iOS */
      }
    }
  </style>
</head>
<body>

<?php require_once 'erp/sidebar.php'; ?>

<div class="main-content visitas-bg" id="main-content">
  <nav class="navbar navbar-top">
    <div class="d-flex align-items-center">
      <h4 class="mb-0 text-dark"><i class="fas fa-calendar-check mr-2 text-seid-azul"></i>Registro de Visitas</h4>
    </div>
    <div class="dropdown">
      <a class="dropdown-toggle d-flex align-items-center text-dark" href="#" role="button" data-toggle="dropdown">
        <div class="rounded-circle bg-seid-azul text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.2rem;">
          <i class="fa fa-user"></i>
        </div>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="#"><i class="fas fa-user mr-2"></i>Perfil</a>
        <a class="dropdown-item" href="#"><i class="fas fa-cog mr-2"></i>Ajustes</a>
        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Salir</a>
      </div>
    </div>
  </nav>

  <?php echo $mensaje; ?>

  <!-- Formulario rápido de visita -->
  <div class="form-quick">
    <h5><i class="fas fa-plus-circle"></i> Registrar nueva visita</h5>
    <form method="POST" class="mt-3">
      <input type="hidden" name="accion" value="agregar">
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="fecha">Fecha *</label>
          <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group col-md-4">
          <label for="farmacia">Farmacia *</label>
          <input type="text" class="form-control" id="farmacia" name="farmacia" list="farmaciasList" placeholder="Escribe o elige" autocomplete="off" required>
          <datalist id="farmaciasList">
            <?php foreach ($farmacias_list as $farm): ?>
              <option value="<?php echo $farm; ?>">
            <?php endforeach; ?>
          </datalist>
          <small class="form-text text-muted">Aparecerán sugerencias mientras escribes.</small>
        </div>
        <div class="form-group col-md-4">
          <label for="observacion">Observación</label>
          <input type="text" class="form-control" id="observacion" name="observacion" placeholder="Breve nota...">
        </div>
      </div>
      <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar visita</button>
    </form>
  </div>

  <!-- Filtros y tabla de visitas -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Listado de visitas</h6>
    </div>
    <div class="card-body">
      <form method="GET" class="form-inline mb-3 flex-wrap">
        <div class="form-group mr-2 mb-2">
          <input type="text" name="busqueda_farmacia" class="form-control" placeholder="Buscar farmacia" value="<?php echo htmlspecialchars($busqueda_farmacia); ?>">
        </div>
        <div class="form-group mr-2 mb-2">
          <input type="date" name="fecha_desde" class="form-control" placeholder="Desde" value="<?php echo $fecha_desde; ?>">
        </div>
        <div class="form-group mr-2 mb-2">
          <input type="date" name="fecha_hasta" class="form-control" placeholder="Hasta" value="<?php echo $fecha_hasta; ?>">
        </div>
        <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="visitas.php" class="btn btn-secondary ml-2 mb-2"><i class="fas fa-times"></i> Limpiar</a>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="thead-dark">
            <th>Fecha</th>
              <th>Farmacia</th>
              <th>Observación</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($visitas) > 0): ?>
              <?php foreach ($visitas as $v): ?>
                
                  <td><?php echo date('d/m/Y', strtotime($v['fecha'])); ?></td>
                  <td><?php echo htmlspecialchars($v['farmacia']); ?></td>
                  <td><?php echo nl2br(htmlspecialchars($v['observacion'])); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="3" class="text-center">No hay visitas registradas.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>