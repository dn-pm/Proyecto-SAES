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

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_GRUPOS AS idgrupo, NOMBRE_GRUPO AS nombregrupo, TURNO AS turno, SEMESTRE AS semestre, DESCRIPCION AS descripcion FROM grupos WHERE ESTATUS = 'ALTA'");
$grupos = $resultado->fetch_all(MYSQLI_ASSOC);
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
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Grupos.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nuevo grupo
        </a>
    </div>

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
            </tr>
        </thead>
        <tbody>
<?php if (count($grupos) === 0) { ?>
            <tr>
                <td colspan="7" class="text-center text-muted">No hay grupos registrados.</td>
            </tr>
<?php } ?>
<?php foreach ($grupos as $grupo) { ?>
            <tr>
                <th scope="row"><?php echo $grupo["idgrupo"]; ?></th>
                <td><?php echo htmlspecialchars($grupo["nombregrupo"]); ?></td>
                <td><?php echo htmlspecialchars($grupo["turno"]); ?></td>
                <td><?php echo htmlspecialchars($grupo["semestre"]); ?></td>
                <td><?php echo htmlspecialchars($grupo["descripcion"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Grupos.php?id=<?php echo $grupo["idgrupo"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
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

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
