<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE unidad SET ESTATUS = 'BAJA' WHERE ID_UNIDAD = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudunidades.php");
    exit;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_UNIDAD AS idunidad, DESCRIPCION AS descripcion FROM unidad WHERE ESTATUS = 'ALTA' ORDER BY ID_UNIDAD");
$unidades = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Unidades</title>
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
    <h1 align="center">Catálogo de Unidades</h1>
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Unidad.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nueva unidad
        </a>
    </div>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Descripción</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php if (count($unidades) === 0) { ?>
            <tr>
                <td colspan="4" class="text-center text-muted">No hay unidades registradas.</td>
            </tr>
<?php } ?>
<?php foreach ($unidades as $unidad) { ?>
            <tr>
                <th scope="row"><?php echo $unidad["idunidad"]; ?></th>
                <td><?php echo htmlspecialchars($unidad["descripcion"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Unidad.php?id=<?php echo $unidad["idunidad"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudunidades.php?baja=<?php echo $unidad["idunidad"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja esta unidad?');">
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
