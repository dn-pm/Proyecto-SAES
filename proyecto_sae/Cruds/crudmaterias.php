<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE materias SET ESTATUS = 'BAJA' WHERE ID_MATERIAS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudmaterias.php");
    exit;
}

// ---------- GUARDAR (registrar o modificar) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id            = (int) ($_POST["idmateria"] ?? 0);
    $clavemateria  = trim($_POST["clavemateria"] ?? "");
    $nombremateria = trim($_POST["nombremateria"] ?? "");
    $creditos      = trim($_POST["creditos"] ?? "");

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE materias SET CLAVE_MATERIA = ?, NOMBRE_MATERIA = ?, CREDITOS = ? WHERE ID_MATERIAS = ?");
        $stmt->bind_param("sssi", $clavemateria, $nombremateria, $creditos, $id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO materias (CLAVE_MATERIA, NOMBRE_MATERIA, CREDITOS) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $clavemateria, $nombremateria, $creditos);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: crudmaterias.php");
    exit;
}

// ---------- EDITAR ----------
$edit = ["idmateria" => 0, "clavemateria" => "", "nombremateria" => "", "creditos" => ""];
$abrirModal = false;

if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    $stmt = $mysqli->prepare("SELECT ID_MATERIAS AS idmateria, CLAVE_MATERIA AS clavemateria, NOMBRE_MATERIA AS nombremateria, CREDITOS AS creditos FROM materias WHERE ID_MATERIAS = ?");
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
$resultado = $mysqli->query("SELECT ID_MATERIAS AS idmateria, CLAVE_MATERIA AS clavemateria, NOMBRE_MATERIA AS nombremateria, CREDITOS AS creditos FROM materias WHERE ESTATUS = 'ALTA'");
$materias = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Materias</title>
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
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudalumnos.php">Alumnos</a>[span_9](start_span)[span_9](end_span)</li>
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudprofesores.php">Profesores</a>[span_10](start_span)[span_10](end_span)</li>
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudgrupos.php">Grupos</a>[span_11](start_span)[span_11](end_span)</li>
            <li><a class="dropdown-item text-light" href="/proyecto_sae/Cruds/crudmaterias.php">Materias</a>[span_12](start_span)[span_12](end_span)</li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <br><br>
    <h1 align="center">Catálogo de Materias</h1>
    <br><br>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Clave Materia</th>
                <th scope="col">Nombre Materia</th>
                <th scope="col">Créditos</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($materias as $materia) { ?>
            <tr>
                <th scope="row"><?php echo $materia["idmateria"]; ?></th>
                <td><?php echo htmlspecialchars($materia["clavemateria"]); ?></td>
                <td><?php echo htmlspecialchars($materia["nombremateria"]); ?></td>
                <td><?php echo htmlspecialchars($materia["creditos"]); ?></td>
                <td>
                    <a href="crudmaterias.php?nuevo=1" class="btn btn-sm btn-dark" title="Agregar" data-bs-toggle="modal" data-bs-target="#modalMateria">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </td>
                <td>
                    <a href="crudmaterias.php?editar=<?php echo $materia["idmateria"]; ?>" class="btn btn-sm btn-primary" title="Modificar" data-bs-toggle="modal" data-bs-target="#modalMateria">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudmaterias.php?baja=<?php echo $materia["idmateria"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja esta materia?');">
                        <i class="bi bi-eject-fill"></i>
                    </a>
                </td>
            </tr>
<?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalMateria" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="crudmaterias.php">
        <div class="modal-header">
          <h5 class="modal-title"><?php echo $edit["idmateria"] > 0 ? "Modificar materia" : "Registrar materia"; ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idmateria" value="<?php echo $edit["idmateria"]; ?>">
          <div class="mb-3">
            <label class="form-label">Clave de Materia</label>
            <input type="text" name="clavemateria" class="form-control" maxlength="15" required value="<?php echo htmlspecialchars($edit["clavemateria"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre de Materia</label>
            <input type="text" name="nombremateria" class="form-control" maxlength="50" required value="<?php echo htmlspecialchars($edit["nombremateria"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Créditos</label>
            <input type="text" name="creditos" class="form-control" maxlength="5" required value="<?php echo htmlspecialchars($edit["creditos"]); ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><?php echo $edit["idmateria"] > 0 ? "Guardar cambios" : "Registrar"; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($abrirModal) { ?>
<script>
    var modal = new bootstrap.Modal(document.getElementById('modalMateria'));
    modal.show();
</script>
<?php } ?>
</body>
</html>
