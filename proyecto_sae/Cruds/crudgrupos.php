<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE grupos SET ESTATUS = 'BAJA' WHERE ID_GRUPOS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudgrupos.php");
    exit;
}

// ---------- GUARDAR (registrar o modificar) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id          = (int) ($_POST["idgrupo"] ?? 0);
    $nombregrupo = trim($_POST["nombregrupo"] ?? "");
    $turno       = trim($_POST["turno"] ?? "");
    $semestre    = trim($_POST["semestre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE grupos SET NOMBRE_GRUPO = ?, TURNO = ?, SEMESTRE = ?, DESCRIPCION = ? WHERE ID_GRUPOS = ?");
        $stmt->bind_param("ssssi", $nombregrupo, $turno, $semestre, $descripcion, $id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO grupos (NOMBRE_GRUPO, TURNO, SEMESTRE, DESCRIPCION) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombregrupo, $turno, $semestre, $descripcion);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: crudgrupos.php");
    exit;
}

// ---------- EDITAR ----------
$edit = ["idgrupo" => 0, "nombregrupo" => "", "turno" => "", "semestre" => "", "descripcion" => ""];
$abrirModal = false;

if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    $stmt = $mysqli->prepare("SELECT ID_GRUPOS AS idgrupo, NOMBRE_GRUPO AS nombregrupo, TURNO AS turno, SEMESTRE AS semestre, DESCRIPCION AS descripcion FROM grupos WHERE ID_GRUPOS = ?");
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
$resultado = $mysqli->query("SELECT ID_GRUPOS AS idgrupo, NOMBRE_GRUPO AS nombregrupo, TURNO AS turno, SEMESTRE AS semestre, DESCRIPCION AS descripcion FROM grupos WHERE ESTATUS = 'ALTA'");
$grupos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Grupos</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/bootstrap-icons.css">
    <style>
        thead.thead-dark th { background-color: #0d6efd; color: #ffffff; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>

<?php include __DIR__ . "/../NavBar/navbar.php"; ?>

<div class="container">
    <br><br>
    <h1 align="center">Catálogo de Grupos</h1>
    <br><br>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre del Grupo</th>
                <th scope="col">Turno</th>
                <th scope="col">Semestre</th>
                <th scope="col">Descripción</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($grupos as $grupo) { ?>
            <tr>
                <th scope="row"><?php echo $grupo["idgrupo"]; ?></th>
                <td><?php echo htmlspecialchars($grupo["nombregrupo"]); ?></td>
                <td><?php echo htmlspecialchars($grupo["turno"]); ?></td>
                <td><?php echo htmlspecialchars($grupo["semestre"]); ?></td>
                <td><?php echo htmlspecialchars($grupo["descripcion"]); ?></td>
                <td>
                    <a href="crudgrupos.php?nuevo=1" class="btn btn-sm btn-dark" title="Agregar">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </td>
                <td>
                    <a href="crudgrupos.php?editar=<?php echo $grupo["idgrupo"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudgrupos.php?baja=<?php echo $grupo["idgrupo"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este grupo?');">
                        <i class="bi bi-eject-fill"></i>
                    </a>
                </td>
            </tr>
<?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalGrupo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="crudgrupos.php">
        <div class="modal-header">
          <h5 class="modal-title"><?php echo $edit["idgrupo"] > 0 ? "Modificar grupo" : "Registrar grupo"; ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idgrupo" value="<?php echo $edit["idgrupo"]; ?>">
          <div class="mb-3">
            <label class="form-label">Nombre del Grupo</label>
            <input type="text" name="nombregrupo" class="form-control" maxlength="20" required value="<?php echo htmlspecialchars($edit["nombregrupo"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Turno</label>
            <input type="text" name="turno" class="form-control" maxlength="15" required value="<?php echo htmlspecialchars($edit["turno"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Semestre</label>
            <input type="text" name="semestre" class="form-control" maxlength="10" required value="<?php echo htmlspecialchars($edit["semestre"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3" maxlength="255" required><?php echo htmlspecialchars($edit["descripcion"]); ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><?php echo $edit["idgrupo"] > 0 ? "Guardar cambios" : "Registrar"; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('modalGrupo').addEventListener('hidden.bs.modal', function () {
        window.location.href = 'crudgrupos.php';
    });
</script>

<?php if ($abrirModal) { ?>
<script>
    var modal = new bootstrap.Modal(document.getElementById('modalGrupo'));
    modal.show();
</script>
<?php } ?>
</body>
</html>
