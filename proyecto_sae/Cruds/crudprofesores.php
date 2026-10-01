<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE profesores SET ESTATUS = 'BAJA' WHERE ID_PROFESORES = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudprofesores.php");
    exit;
}

// ---------- GUARDAR (registrar o modificar) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id       = (int) ($_POST["idprof"] ?? 0);
    $clave    = trim($_POST["clave"] ?? "");
    $nombre   = trim($_POST["nombre"] ?? "");
    $apaterno = trim($_POST["apaterno"] ?? "");
    $amaterno = trim($_POST["amaterno"] ?? "");
    $correo   = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE profesores SET CLAVE = ?, NOMBRE = ?, APELLIDO_PATERNO = ?, APELLIDO_MATERNO = ?, CORREO = ?, TELEFONO = ? WHERE ID_PROFESORES = ?");
        $stmt->bind_param("ssssssi", $clave, $nombre, $apaterno, $amaterno, $correo, $telefono, $id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO profesores (CLAVE, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CORREO, TELEFONO) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $clave, $nombre, $apaterno, $amaterno, $correo, $telefono);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: crudprofesores.php");
    exit;
}

// ---------- EDITAR ----------
$edit = ["idprof" => 0, "clave" => "", "nombre" => "", "apaterno" => "", "amaterno" => "", "correo" => "", "telefono" => ""];
$abrirModal = false;

if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    $stmt = $mysqli->prepare("SELECT ID_PROFESORES AS idprof, CLAVE AS clave, NOMBRE AS nombre, APELLIDO_PATERNO AS apaterno, APELLIDO_MATERNO AS amaterno, CORREO AS correo, TELEFONO AS telefono FROM profesores WHERE ID_PROFESORES = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) {
        $edit = $fila;
        $abrirModal = true;
    }
}

if (isset($_GET["nuevo"])) {
    $abrirModal = true;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_PROFESORES AS idprof, CLAVE AS clave, NOMBRE AS nombre, APELLIDO_PATERNO AS apaterno, APELLIDO_MATERNO AS amaterno, CORREO AS correo, TELEFONO AS telefono FROM profesores WHERE ESTATUS = 'ALTA'");
$profesores = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Profesores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        thead.thead-dark th { background-color: #0d6efd; color: #ffffff; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>

<?php include __DIR__ . "/../NavBar/navbar.php"; ?>

<div class="container">
    <br><br>
    <h1 align="center">Catálogo de Profesores</h1>
    <br><br>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Clave</th>
                <th scope="col">Nombre</th>
                <th scope="col">Apellido Paterno</th>
                <th scope="col">Apellido Materno</th>
                <th scope="col">Email</th>
                <th scope="col">Teléfono</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($profesores as $profesor) { ?>
            <tr>
                <th scope="row"><?php echo $profesor["idprof"]; ?></th>
                <td><?php echo htmlspecialchars($profesor["clave"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["nombre"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["apaterno"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["amaterno"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["correo"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["telefono"]); ?></td>
                <td>
                    <a href="crudprofesores.php?nuevo=1" class="btn btn-sm btn-dark" title="Agregar" data-bs-toggle="modal" data-bs-target="#modalProfesor">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </td>
                <td>
                    <a href="crudprofesores.php?editar=<?php echo $profesor["idprof"]; ?>" class="btn btn-sm btn-primary" title="Modificar" data-bs-toggle="modal" data-bs-target="#modalProfesor">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudprofesores.php?baja=<?php echo $profesor["idprof"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este profesor?');">
                        <i class="bi bi-eject-fill"></i>
                    </a>
                </td>
            </tr>
<?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalProfesor" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="crudprofesores.php">
        <div class="modal-header">
          <h5 class="modal-title"><?php echo $edit["idprof"] > 0 ? "Modificar profesor" : "Registrar profesor"; ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idprof" value="<?php echo $edit["idprof"]; ?>">
          <div class="mb-3">
            <label class="form-label">Clave</label>
            <input type="text" name="clave" class="form-control" maxlength="15" required value="<?php echo htmlspecialchars($edit["clave"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" maxlength="30" required value="<?php echo htmlspecialchars($edit["nombre"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Apellido paterno</label>
            <input type="text" name="apaterno" class="form-control" maxlength="15" required value="<?php echo htmlspecialchars($edit["apaterno"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Apellido materno</label>
            <input type="text" name="amaterno" class="form-control" maxlength="15" required value="<?php echo htmlspecialchars($edit["amaterno"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" maxlength="50" required value="<?php echo htmlspecialchars($edit["correo"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="tel" name="telefono" class="form-control" maxlength="35" required value="<?php echo htmlspecialchars($edit["telefono"]); ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><?php echo $edit["idprof"] > 0 ? "Guardar cambios" : "Registrar"; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($abrirModal) { ?>
<script>
    var modal = new bootstrap.Modal(document.getElementById('modalProfesor'));
    modal.show();
</script>
<?php } ?>
</body>
</html>
