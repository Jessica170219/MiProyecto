<?php
session_start();
require_once 'erp/config.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || time() > $_SESSION['expire']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['name'];
$email = $_SESSION['email'];

// Parámetros de búsqueda y ordenamiento
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'farmacia';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

$allowedColumns = ['id', 'farmacia', 'direccion', 'provincia', 'municipio', 'codigo_postal', 'email', 'telefono', 'fecha_registro'];
if (!in_array($sort, $allowedColumns)) {
    $sort = 'farmacia';
}

$sql = "SELECT id, farmacia, direccion, provincia, municipio, codigo_postal, email, telefono, fecha_registro 
        FROM clientes";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $sql .= " WHERE farmacia LIKE ? 
              OR provincia LIKE ? 
              OR codigo_postal LIKE ?";
    $like = "%$busqueda%";
    $params = [$like, $like, $like];
    $types = "sss";
}

$sql .= " ORDER BY $sort $order";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!doctype html>
<html lang="es">
<head>
  <title>Clientes - SEID Farmacia</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
    .main-content.clientes-bg {
      background: #f8f9fc;
    }
    th.sortable {
      cursor: pointer;
      user-select: none;
    }
    th.sortable:hover {
      background-color: rgba(0,0,0,0.1);
    }
    .sort-indicator {
      margin-left: 5px;
      font-size: 0.8em;
    }
    .btn-editar {
      padding: 0.2rem 0.5rem;
      font-size: 0.8rem;
    }
  </style>
</head>
<body>

<?php require_once 'erp/sidebar.php'; ?>

 
  <!-- Contenido Principal -->
  <div class="main-content clientes-bg" id="main-content">
    <nav class="navbar navbar-top">
      <div class="d-flex align-items-center">
        <h4 class="mb-0 text-dark"><i class="fas fa-users mr-2 text-seid-azul"></i>Clientes</h4>
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

    <div id="mensaje"></div>

    <!-- Barra de búsqueda y botón agregar -->
    <div class="row mb-3 mt-3">
      <div class="col-md-8">
        <form method="GET" class="form-inline" id="formBuscar">
          <div class="input-group w-100">
            <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre, provincia o código postal" value="<?php echo htmlspecialchars($busqueda); ?>">
            <div class="input-group-append">
              <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Buscar</button>
              <?php if (!empty($busqueda)): ?>
                <a href="clientes.php?<?php echo http_build_query(['sort' => $sort, 'order' => strtolower($order)]); ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Limpiar</a>
              <?php endif; ?>
            </div>
          </div>
          <input type="hidden" name="sort" value="<?php echo $sort; ?>">
          <input type="hidden" name="order" value="<?php echo strtolower($order); ?>">
        </form>
      </div>
      <div class="col-md-4 text-right">
        <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregar"><i class="fas fa-plus"></i> Añadir Farmacia</button>
      </div>
    </div>

    <!-- Tabla de clientes con columna de acciones -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="thead-dark">
          <th class="sortable" onclick="window.location='?sort=id&order=<?php echo ($sort=='id' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">ID <span class="sort-indicator"><?php echo ($sort=='id') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=farmacia&order=<?php echo ($sort=='farmacia' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Farmacia <span class="sort-indicator"><?php echo ($sort=='farmacia') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=direccion&order=<?php echo ($sort=='direccion' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Dirección <span class="sort-indicator"><?php echo ($sort=='direccion') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=provincia&order=<?php echo ($sort=='provincia' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Provincia <span class="sort-indicator"><?php echo ($sort=='provincia') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=municipio&order=<?php echo ($sort=='municipio' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Municipio <span class="sort-indicator"><?php echo ($sort=='municipio') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=codigo_postal&order=<?php echo ($sort=='codigo_postal' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Código Postal <span class="sort-indicator"><?php echo ($sort=='codigo_postal') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=email&order=<?php echo ($sort=='email' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Email <span class="sort-indicator"><?php echo ($sort=='email') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            <th class="sortable" onclick="window.location='?sort=telefono&order=<?php echo ($sort=='telefono' && $order=='ASC') ? 'desc' : 'asc'; echo !empty($busqueda) ? '&busqueda='.urlencode($busqueda) : ''; ?>'">Teléfono <span class="sort-indicator"><?php echo ($sort=='telefono') ? ($order=='ASC' ? '▲' : '▼') : ''; ?></span></th>
            
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($clientes) > 0): ?>
            <?php foreach ($clientes as $cliente): ?>
              <tr data-id="<?php echo $cliente['id']; ?>"
                  data-farmacia="<?php echo htmlspecialchars($cliente['farmacia']); ?>"
                  data-direccion="<?php echo htmlspecialchars($cliente['direccion']); ?>"
                  data-provincia="<?php echo htmlspecialchars($cliente['provincia']); ?>"
                  data-municipio="<?php echo htmlspecialchars($cliente['municipio']); ?>"
                  data-codigo_postal="<?php echo htmlspecialchars($cliente['codigo_postal']); ?>"
                  data-email="<?php echo htmlspecialchars($cliente['email']); ?>"
                  data-telefono="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                <td><?php echo htmlspecialchars($cliente['farmacia']); ?></td>
                <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                <td><?php echo htmlspecialchars($cliente['provincia']); ?></td>
                <td><?php echo htmlspecialchars($cliente['municipio']); ?></td>
                <td><?php echo htmlspecialchars($cliente['codigo_postal']); ?></td>
                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                
                <td>
                  <button class="btn btn-primary btn-sm btn-editar" onclick="editarCliente(this)"><i class="fas fa-edit"></i> Editar</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-center">No se encontraron resultados.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal para agregar farmacia  -->
  <div class="modal fade" id="modalAgregar" tabindex="-1" role="dialog" aria-labelledby="modalAgregarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAgregarLabel">Añadir Nueva Farmacia</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formAgregar">
          <div class="modal-body">
            <div class="form-group">
              <label for="farmacia">Farmacia *</label>
              <input type="text" class="form-control" id="farmacia" name="farmacia" required>
            </div>
            <div class="form-group">
              <label for="direccion">Dirección</label>
              <input type="text" class="form-control" id="direccion" name="direccion">
            </div>
            <div class="form-group">
              <label for="provincia">Provincia</label>
              <input type="text" class="form-control" id="provincia" name="provincia">
            </div>
            <div class="form-group">
              <label for="municipio">Municipio</label>
              <input type="text" class="form-control" id="municipio" name="municipio">
            </div>
            <div class="form-group">
              <label for="codigo_postal">Código Postal</label>
              <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="form-group">
              <label for="telefono">Teléfono</label>
              <input type="text" class="form-control" id="telefono" name="telefono">
            </div>
            <small class="text-muted">* Campo obligatorio</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Farmacia</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal para editar farmacia -->
  <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditarLabel">Editar Farmacia</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formEditar">
          <div class="modal-body">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group">
              <label for="edit_farmacia">Farmacia *</label>
              <input type="text" class="form-control" id="edit_farmacia" name="farmacia" required>
            </div>
            <div class="form-group">
              <label for="edit_direccion">Dirección</label>
              <input type="text" class="form-control" id="edit_direccion" name="direccion">
            </div>
            <div class="form-group">
              <label for="edit_provincia">Provincia</label>
              <input type="text" class="form-control" id="edit_provincia" name="provincia">
            </div>
            <div class="form-group">
              <label for="edit_municipio">Municipio</label>
              <input type="text" class="form-control" id="edit_municipio" name="municipio">
            </div>
            <div class="form-group">
              <label for="edit_codigo_postal">Código Postal</label>
              <input type="text" class="form-control" id="edit_codigo_postal" name="codigo_postal">
            </div>
            <div class="form-group">
              <label for="edit_email">Email</label>
              <input type="email" class="form-control" id="edit_email" name="email">
            </div>
            <div class="form-group">
              <label for="edit_telefono">Teléfono</label>
              <input type="text" class="form-control" id="edit_telefono" name="telefono">
            </div>
            <small class="text-muted">* Campo obligatorio</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Actualizar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    // Función para abrir el modal de editar con los datos de la fila
    function editarCliente(btn) {
      var row = btn.closest('tr');
      var id = row.getAttribute('data-id');
      var farmacia = row.getAttribute('data-farmacia');
      var direccion = row.getAttribute('data-direccion');
      var provincia = row.getAttribute('data-provincia');
      var municipio = row.getAttribute('data-municipio');
      var codigo_postal = row.getAttribute('data-codigo_postal');
      var email = row.getAttribute('data-email');
      var telefono = row.getAttribute('data-telefono');

      document.getElementById('edit_id').value = id;
      document.getElementById('edit_farmacia').value = farmacia;
      document.getElementById('edit_direccion').value = direccion;
      document.getElementById('edit_provincia').value = provincia;
      document.getElementById('edit_municipio').value = municipio;
      document.getElementById('edit_codigo_postal').value = codigo_postal;
      document.getElementById('edit_email').value = email;
      document.getElementById('edit_telefono').value = telefono;

      $('#modalEditar').modal('show');
    }

    // Envío del formulario de agregar (fetch)
    document.getElementById('formAgregar').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('agregar_cliente.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('mensaje').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
          $('#modalAgregar').modal('hide');
          this.reset();
          location.reload(); // recarga con los parámetros actuales (se mantienen por la URL)
        } else {
          document.getElementById('mensaje').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('mensaje').innerHTML = '<div class="alert alert-danger">Error en la solicitud.</div>';
      });
    });

    // Envío del formulario de editar (fetch)
    document.getElementById('formEditar').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('editar_cliente.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('mensaje').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
          $('#modalEditar').modal('hide');
          location.reload(); // recarga para reflejar cambios
        } else {
          document.getElementById('mensaje').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('mensaje').innerHTML = '<div class="alert alert-danger">Error en la solicitud.</div>';
      });
    });
  </script>

 
</body>
</html>