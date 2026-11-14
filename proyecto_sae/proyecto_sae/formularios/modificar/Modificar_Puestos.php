<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$puesto = null;

if (isset($_POST["actualizar"])) {

    $id          = (int) $_POST["id"];
    $descripcion = trim($_POST["descripcion"]);
    $estatus     = $_POST["estatus"];

    if ($descripcion === "") {
        $error = "Escribe la descripción del puesto.";
    } else {
        try {
            $sql = "UPDATE puesto SET DESCRIPCION_PUESTO = ?, STATUS_PUESTO = ? WHERE ID_PUESTO = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssi", $descripcion, $estatus, $id);
            $stmt->execute();
            $stmt->close();
            $mensaje = "Puesto actualizado correctamente.";
        } catch (mysqli_sql_exception $e) {
            $error = "Ocurrió un error al actualizar: " . $e->getMessage();
        }
    }
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $stmt = $conexion->prepare("SELECT * FROM puesto WHERE ID_PUESTO = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $puesto = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$listaPuestos = null;

if ($puesto === null) {
    $listaPuestos = $conexion->query("SELECT * FROM puesto ORDER BY ID_PUESTO");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Puestos</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Modificar Puestos</h2>
        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if ($puesto !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $puesto["ID_PUESTO"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Id de puesto</label>
                        <input type="text" class="form-control" value="<?php echo $puesto["ID_PUESTO"]; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción del puesto</label>
                        <input type="text" name="descripcion" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($puesto["DESCRIPCION_PUESTO"]); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($puesto["STATUS_PUESTO"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($puesto["STATUS_PUESTO"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="<?= $base ?>/Cruds/crudpuestos.php" class="btn btn-secondary">Cancelar</a>

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
                        <?php if ($listaPuestos && $listaPuestos->num_rows > 0) { ?>
                            <?php while ($fila = $listaPuestos->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_PUESTO"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["DESCRIPCION_PUESTO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["STATUS_PUESTO"]); ?></td>
                                    <td><a href="?id=<?php echo $fila["ID_PUESTO"]; ?>" class="btn btn-sm btn-primary">Editar</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay puestos registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Puestos.php">Registrar nuevo puesto</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudpuestos.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de puestos - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>
