<?php
require_once __DIR__ . "/../../config/conexion.php";

$mostrarDatos = false;
$error = "";

if (isset($_POST["guardar"])) {
    $clavemateria  = $_POST["clavemateria"];
    $nombremateria = $_POST["nombremateria"];
    $creditos      = $_POST["creditos"];
    $descripcion   = $_POST["descripcion"];

    try {
        $sql = "INSERT INTO materias (CLAVE_MATERIA, NOMBRE_MATERIA, CREDITOS, DESCRIPCION) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $clavemateria, $nombremateria, $creditos, $descripcion);
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
    <title>Registro de Materias</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Materias</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Clave de la materia</label>
                    <input type="text" name="clavemateria" class="form-control" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre de la materia</label>
                    <input type="text" name="nombremateria" class="form-control" maxlength="50" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Créditos</label>
                    <input type="text" name="creditos" class="form-control" maxlength="5" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción de la materia</label>
                    <textarea name="descripcion" class="form-control" rows="3" maxlength="255" required></textarea>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar materia</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Materia guardada correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información de la materia</h4>

                <table class="table table-bordered">
                    <tr><th>Clave</th><td><?php echo htmlspecialchars($clavemateria); ?></td></tr>
                    <tr><th>Nombre de la materia</th><td><?php echo htmlspecialchars($nombremateria); ?></td></tr>
                    <tr><th>Créditos</th><td><?php echo htmlspecialchars($creditos); ?></td></tr>
                    <tr><th>Descripción</th><td><?php echo htmlspecialchars($descripcion); ?></td></tr>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Materias.php">Modificar una materia existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudmaterias.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de materias - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
