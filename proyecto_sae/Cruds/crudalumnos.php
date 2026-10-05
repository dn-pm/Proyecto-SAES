<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE alumnos SET ESTATUS = 'BAJA' WHERE ID_ALUMNOS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudalumnos.php");
    exit;
}

// ---------- GUARDAR (registrar o modificar) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id        = (int) ($_POST["idalum"] ?? 0);
    $matricula = trim($_POST["matricula"] ?? "");
    $nombre    = trim($_POST["nombre"] ?? "");
    $apaterno  = trim($_POST["apaterno"] ?? "");
    $amaterno  = trim($_POST["amaterno"] ?? "");
    $domicilio = trim($_POST["domicilio"] ?? "");
    $correo    = trim($_POST["correo"] ?? "");
    $telefono  = trim($_POST["telefono"] ?? "");

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE alumnos SET MATRICULA = ?, NOMBRE = ?, APELLIDO_PATERNO = ?, APELLIDO_MATERNO = ?, DOMICILIO = ?, CORREO = ?, TELEFONO = ? WHERE ID_ALUMNOS = ?");
        $stmt->bind_param("sssssssi", $matricula, $nombre, $apaterno, $amaterno, $domicilio, $correo, $telefono, $id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO alumnos (MATRICULA, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, DOMICILIO, CORREO, TELEFONO) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $matricula, $nombre, $apaterno, $amaterno, $domicilio, $correo, $telefono);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: crudalumnos.php");
    exit;
}

// ---------- EDITAR (cargar datos para el modal) ----------
$edit = ["idalum" => 0, "matricula" => "", "nombre" => "", "apaterno" => "", "amaterno" => "", "domicilio" => "", "correo" => "", "telefono" => ""];
$abrirModal = false;

if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    $stmt = $mysqli->prepare("SELECT ID_ALUMNOS AS idalum, MATRICULA AS matricula, NOMBRE AS nombre, APELLIDO_PATERNO AS apaterno, APELLIDO_MATERNO AS amaterno, DOMICILIO AS domicilio, CORREO AS correo, TELEFONO AS telefono FROM alumnos WHERE ID_ALUMNOS = ?");
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
$resultado = $mysqli->query("SELECT ID_ALUMNOS AS idalum, MATRICULA AS matricula, NOMBRE AS nombre, APELLIDO_PATERNO AS apaterno, APELLIDO_MATERNO AS amaterno, DOMICILIO AS domicilio, CORREO AS correo, TELEFONO AS telefono FROM alumnos WHERE ESTATUS = 'ALTA'");
$alumnos = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Alumnos</title>

    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/bootstrap-icons.css">

    <style>
        /* Color de la barra de la tabla separado del título */
        thead.thead-dark th { 
            background-color: #0d6efd; /* Aquí puedes cambiar el color de la barra de la tabla */
            color: #ffffff; 
        }
        /* Color del título principal separado */
        h1 { 
            color: #2c3e50; 
        }
    </style>
</head>
<body>

<?php include __DIR__ . "/../NavBar/navbar.php"; ?>

<div class="container">

    <br><br>
    <h1 align="center">Catálogo de Alumnos</h1>
    <br><br>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Matrícula</th>
                <th scope="col">Nombre</th>
                <th scope="col">Apellido Paterno</th>
                <th scope="col">Apellido Materno</th>
                <th scope="col">Domicilio</th>
                <th scope="col">Email</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($alumnos as $alumno) { ?>
            <tr>
                <th scope="row"><?php echo $alumno["idalum"]; ?></th>
                <td><?php echo htmlspecialchars($alumno["matricula"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["nombre"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["apaterno"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["amaterno"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["domicilio"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["correo"]); ?></td>
                
                <!-- Botón Agregar (+) en cada registro -->
                <td>
                    <a href="crudalumnos.php?nuevo=1" class="btn btn-sm btn-dark" title="Agregar">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </td>

                <!-- Botón Modificar -->
                <td>
                    <a href="crudalumnos.php?editar=<?php echo $alumno["idalum"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>

                <!-- Botón Baja -->
                <td>
                    <a href="crudalumnos.php?baja=<?php echo $alumno["idalum"]; ?>" class="btn btn-sm btn-secondary" title="Baja"
                       onclick="return confirm('¿Dar de baja este alumno?');">
                        <i class="bi bi-eject-fill"></i>
                    </a>
                </td>
            </tr>
<?php } ?>
        </tbody>
    </table>

</div>

<!-- Modal: se usa tanto para registrar como para modificar -->
<div class="modal fade" id="modalAlumno" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="crudalumnos.php">
        <div class="modal-header">
          <h5 class="modal-title"><?php echo $edit["idalum"] > 0 ? "Modificar alumno" : "Registrar alumno"; ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idalum" value="<?php echo $edit["idalum"]; ?>">

          <div class="mb-3">
            <label class="form-label">Matrícula</label>
            <input type="text" name="matricula" class="form-control" maxlength="15" required value="<?php echo htmlspecialchars($edit["matricula"]); ?>">
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
            <label class="form-label">Domicilio</label>
            <input type="text" name="domicilio" class="form-control" maxlength="80" required value="<?php echo htmlspecialchars($edit["domicilio"]); ?>">
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
          <button type="submit" class="btn btn-primary"><?php echo $edit["idalum"] > 0 ? "Guardar cambios" : "Registrar"; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('modalAlumno').addEventListener('hidden.bs.modal', function () {
        window.location.href = 'crudalumnos.php';
    });
</script>

<?php if ($abrirModal) { ?>
<script>
    var modal = new bootstrap.Modal(document.getElementById('modalAlumno'));
    modal.show();
</script>
<?php } ?>

</body>
</html>