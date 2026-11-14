<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE puesto SET STATUS_PUESTO = 'BAJA' WHERE ID_PUESTO = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudpuestos.php");
    exit;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_PUESTO AS idpuesto, DESCRIPCION_PUESTO AS descripcion FROM puesto WHERE STATUS_PUESTO = 'ALTA' ORDER BY DESCRIPCION_PUESTO");
$puestos = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Puestos</title>
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
    <h1 align="center">Catálogo de Puestos</h1>
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Puestos.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nuevo puesto
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
<?php if (count($puestos) === 0) { ?>
            <tr>
                <td colspan="4" class="text-center text-muted">No hay puestos registrados.</td>
            </tr>
<?php } ?>
<?php foreach ($puestos as $puesto) { ?>
            <tr>
                <th scope="row"><?php echo $puesto["idpuesto"]; ?></th>
                <td><?php echo htmlspecialchars($puesto["descripcion"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Puestos.php?id=<?php echo $puesto["idpuesto"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudpuestos.php?baja=<?php echo $puesto["idpuesto"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este puesto?');">
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
