<?php
require_once __DIR__ . "/../../config/conexion.php";

$mostrarDatos = false;
$error = "";

if (isset($_POST["guardar"])) {
    $nombregrupo = $_POST["nombregrupo"];
    $turno       = $_POST["turno"];
    $semestre    = $_POST["semestre"];
    $descripcion = $_POST["descripcion"];

    try {
        $sql = "INSERT INTO grupos (NOMBRE_GRUPO, TURNO, SEMESTRE, DESCRIPCION) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $nombregrupo, $turno, $semestre, $descripcion);
        $stmt->execute();
        $stmt->close();
        $mostrarDatos = true;
    } catch (mysqli_sql_exception $e) {
        $error = "Ocurrió un error al guardar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Grupos</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Grupos</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Nombre del grupo</label>
                    <input type="text" name="nombregrupo" class="form-control" maxlength="20" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Turno</label>
                    <input type="text" name="turno" class="form-control" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Semestre</label>
                    <input type="text" name="semestre" class="form-control" maxlength="10" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción del grupo</label>
                    <textarea name="descripcion" class="form-control" rows="3" maxlength="255" required></textarea>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar grupo</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Grupo guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información del grupo</h4>

                <table class="table table-bordered">
                    <tr><th>Nombre del grupo</th><td><?php echo htmlspecialchars($nombregrupo); ?></td></tr>
                    <tr><th>Turno</th><td><?php echo htmlspecialchars($turno); ?></td></tr>
                    <tr><th>Semestre</th><td><?php echo htmlspecialchars($semestre); ?></td></tr>
                    <tr><th>Descripción</th><td><?php echo htmlspecialchars($descripcion); ?></td></tr>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Grupos.php">Modificar un grupo existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudgrupos.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de grupos - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
