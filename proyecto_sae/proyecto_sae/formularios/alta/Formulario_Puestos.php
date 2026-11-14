<?php
require_once __DIR__ . "/../../config/conexion.php";

$mostrarDatos = false;
$error = "";
$idNuevo = 0;
$descripcion = "";

if (isset($_POST["guardar"])) {
    $descripcion = trim($_POST["descripcion"]);

    if ($descripcion === "") {
        $error = "Escribe la descripción del puesto.";
    } else {
        try {
            $stmt = $conexion->prepare("INSERT INTO puesto (DESCRIPCION_PUESTO) VALUES (?)");
            $stmt->bind_param("s", $descripcion);
            $stmt->execute();
            $idNuevo = $stmt->insert_id;
            $stmt->close();
            $mostrarDatos = true;
        } catch (mysqli_sql_exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Puestos</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Puestos</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Descripción del puesto</label>
                    <input type="text" name="descripcion" class="form-control" maxlength="50" value="<?php echo $mostrarDatos ? "" : htmlspecialchars($descripcion); ?>" required>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar puesto</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Puesto guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información del puesto</h4>

                <table class="table table-bordered">
                    <tr><th>Id de puesto</th><td><?php echo $idNuevo; ?></td></tr>
                    <tr><th>Descripción</th><td><?php echo htmlspecialchars($descripcion); ?></td></tr>
                    <tr><th>Estatus</th><td>ALTA</td></tr>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Puestos.php">Modificar un puesto existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudpuestos.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de puestos - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
