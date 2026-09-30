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

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE grupos SET NOMBRE_GRUPO = ?, TURNO = ?, SEMESTRE = ? WHERE ID_GRUPOS = ?");
        $stmt->bind_param("sssi", $nombregrupo, $turno, $semestre, $id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO grupos (NOMBRE_GRUPO, TURNO, SEMESTRE) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombregrupo, $turno, $semestre);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: crudgrupos.php");
    exit;
}

// ---------- EDITAR ----------
$edit = ["idgrupo" => 0, "nombregrupo" => "", "turno" => "", "semestre" => ""];
$abrirModal = false;

if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    $stmt = $mysqli->prepare("SELECT ID_GRUPOS AS idgrupo, NOMBRE_GRUPO AS nombregrupo, TURNO AS turno, SEMESTRE AS semestre FROM grupos WHERE ID_GRUPOS = ?");
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
$resultado = $mysqli->query("SELECT ID_GRUPOS AS idgrupo, NOMBRE_GRUPO AS nombregrupo, TURNO AS turno, SEMESTRE AS semestre FROM grupos WHERE ESTATUS = 'ALTA'");
$grupos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Grupos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        thead.thead-dark th { background-color: #0d6efd; color: #ffffff; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/proyecto_sae/principal.php">Proyecto SAE</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Catálogos</a>
          <ul class="dropdown-menu bg-dark">
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudalumnos.php">Alumnos</a>[span_5](start_span)[span_5](end_span)</li>
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudprofesores.php">Profesores</a>[span_6](start_span)[span_6](end_span)</li>
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudgrupos.php">Grupos</a>[span_7](start_span)[span_7](end_span)</li>
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudmaterias.php">Materias</a>[span_8](start_span)[span_8](end_span)</li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

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
                <td>
                    <a href="crudgrupos.php?nuevo=1" class="btn btn-sm btn-dark" title="Agregar" data-bs-toggle="modal" data-bs-target="#modalGrupo">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </td>
                <td>
                    <a href="crudgrupos.php?editar=<?php echo $grupo["idgrupo"]; ?>" class="btn btn-sm btn-primary" title="Modificar" data-bs-toggle="modal" data-bs-target="#modalGrupo">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><?php echo $edit["idgrupo"] > 0 ? "Guardar cambios" : "Registrar"; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($abrirModal) { ?>
<script>
    var modal = new bootstrap.Modal(document.getElementById('modalGrupo'));
    modal.show();
</script>
<?php } ?>
</body>
</html>
