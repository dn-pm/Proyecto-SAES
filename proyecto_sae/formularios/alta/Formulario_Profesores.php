<?php
require_once __DIR__ . "/../../config/conexion.php";

$mostrarDatos = false;
$error = "";

if (isset($_POST["guardar"])) {
    $clave           = $_POST["clave"];
    $nombre          = $_POST["nombre"];
    $apellidoPaterno = $_POST["apellidoPaterno"];
    $apellidoMaterno = $_POST["apellidoMaterno"];
    $correo          = $_POST["correo"];
    $telefono        = $_POST["telefono"];

    try {
        $sql = "INSERT INTO profesores (CLAVE, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CORREO, TELEFONO) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssss", $clave, $nombre, $apellidoPaterno, $apellidoMaterno, $correo, $telefono);
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
    <title>Registro de Profesores</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Profesores</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Clave</label>
                    <input type="text" name="clave" class="form-control" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" maxlength="30" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Apellido paterno</label>
                    <input type="text" name="apellidoPaterno" class="form-control" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Apellido materno</label>
                    <input type="text" name="apellidoMaterno" class="form-control" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control" maxlength="50" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" class="form-control" maxlength="35" required>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar profesor</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Profesor guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información del profesor</h4>

                <table class="table table-bordered">
                    <tr><th>Clave</th><td><?php echo htmlspecialchars($clave); ?></td></tr>
                    <tr><th>Nombre</th><td><?php echo htmlspecialchars($nombre); ?></td></tr>
                    <tr><th>Apellido paterno</th><td><?php echo htmlspecialchars($apellidoPaterno); ?></td></tr>
                    <tr><th>Apellido materno</th><td><?php echo htmlspecialchars($apellidoMaterno); ?></td></tr>
                    <tr><th>Correo electrónico</th><td><?php echo htmlspecialchars($correo); ?></td></tr>
                    <tr><th>Teléfono</th><td><?php echo htmlspecialchars($telefono); ?></td></tr>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Profesores.php">Modificar un profesor existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudprofesores.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de profesores - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
