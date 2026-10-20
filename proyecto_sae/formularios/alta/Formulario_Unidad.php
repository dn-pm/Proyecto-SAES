<?php
require_once __DIR__ . "/../../config/conexion.php";

$mostrarDatos = false;
$error = "";

if (isset($_POST["guardar"])) {
    $idUnidad    = (int)$_POST["id_unidad"];
    $descripcion = $_POST["descripcion"];

    try {
        $sql = "INSERT INTO unidad (ID_UNIDAD, DESCRIPCION) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("is", $idUnidad, $descripcion);
        $stmt->execute();
        $stmt->close();
        $mostrarDatos = true;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Ya existe una unidad con ese Id.";
        } else {
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
    <title>Registro de Unidades</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Unidades</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Id de unidad</label>
                    <input type="number" name="id_unidad" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción de la unidad</label>
                    <textarea name="descripcion" class="form-control" rows="3" maxlength="255" required></textarea>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar unidad</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Unidad guardada correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información de la unidad</h4>

                <table class="table table-bordered">
                    <tr><th>Id de unidad</th><td><?php echo htmlspecialchars($idUnidad); ?></td></tr>
                    <tr><th>Descripción</th><td><?php echo htmlspecialchars($descripcion); ?></td></tr>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Unidad.php">Modificar una unidad existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudunidades.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de unidades - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
