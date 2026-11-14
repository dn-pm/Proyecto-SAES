<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$unidad = null;

if (isset($_POST["actualizar"])) {

    $id          = (int) $_POST["id"];
    $descripcion = $_POST["descripcion"];
    $estatus     = $_POST["estatus"];

    try {
        $sql = "UPDATE unidad SET DESCRIPCION = ?, ESTATUS = ? WHERE ID_UNIDAD = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssi", $descripcion, $estatus, $id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Unidad actualizada correctamente.";
    } catch (mysqli_sql_exception $e) {
        $error = "Ocurrió un error al actualizar: " . $e->getMessage();
    }
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $stmt = $conexion->prepare("SELECT * FROM unidad WHERE ID_UNIDAD = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $unidad = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$listaUnidades = null;

if ($unidad === null) {
    $listaUnidades = $conexion->query("SELECT * FROM unidad ORDER BY ID_UNIDAD");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Unidades</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Modificar Unidades</h2>
        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if ($unidad !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $unidad["ID_UNIDAD"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Id de unidad</label>
                        <input type="text" class="form-control" value="<?php echo $unidad["ID_UNIDAD"]; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción de la unidad</label>
                        <textarea name="descripcion" class="form-control" rows="3" maxlength="255" required><?php echo htmlspecialchars($unidad["DESCRIPCION"]); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($unidad["ESTATUS"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($unidad["ESTATUS"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="<?= $base ?>/Cruds/crudunidades.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaUnidades && $listaUnidades->num_rows > 0) { ?>
                            <?php while ($fila = $listaUnidades->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_UNIDAD"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["DESCRIPCION"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS"]); ?></td>
                                    <td><a href="?id=<?php echo $fila["ID_UNIDAD"]; ?>" class="btn btn-sm btn-primary">Editar</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay unidades registradas todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Unidad.php">Registrar nueva unidad</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudunidades.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de unidades - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>
