<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$materia = null;

if (isset($_POST["actualizar"])) {

        $id      = (int) $_POST["id"];
        $clavemateria = $_POST["clavemateria"];
        $nombremateria = $_POST["nombremateria"];
        $creditos = $_POST["creditos"];
        $estatus = $_POST["estatus"];

    try {
        $sql = "UPDATE materias SET CLAVE_MATERIA = ?, NOMBRE_MATERIA = ?, CREDITOS = ?, ESTATUS = ? WHERE ID_MATERIAS = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $clavemateria, $nombremateria, $creditos, $estatus, $id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Materia actualizada correctamente.";
    } catch (mysqli_sql_exception $e) {
        $error = "Ocurrió un error al actualizar: " . $e->getMessage();
    }
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $stmt = $conexion->prepare("SELECT * FROM materias WHERE ID_MATERIAS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $materia = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$listaMaterias = null;

if ($materia === null) {
    $listaMaterias = $conexion->query("SELECT * FROM materias ORDER BY ID_MATERIAS DESC");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Materias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Modificar Materias</h2>
        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if ($materia !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $materia["ID_MATERIAS"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Clave de la materia</label>
                        <input type="text" name="clavemateria" class="form-control" maxlength="15" required
                               value="<?php echo htmlspecialchars($materia["CLAVE_MATERIA"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre de la materia</label>
                        <input type="text" name="nombremateria" class="form-control" maxlength="50" required
                               value="<?php echo htmlspecialchars($materia["NOMBRE_MATERIA"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Créditos</label>
                        <input type="text" name="creditos" class="form-control" maxlength="5" required
                               value="<?php echo htmlspecialchars($materia["CREDITOS"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($materia["ESTATUS"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($materia["ESTATUS"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="Modificar_Materias.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Créditos</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaMaterias && $listaMaterias->num_rows > 0) { ?>
                            <?php while ($fila = $listaMaterias->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_MATERIAS"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["CLAVE_MATERIA"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBRE_MATERIA"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["CREDITOS"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS"]); ?></td>
                                    <td><a href="?id=<?php echo $fila["ID_MATERIAS"]; ?>" class="btn btn-sm btn-primary">Editar</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay materias registradas todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Materias.php">Registrar nueva materia</a>
                &nbsp;|&nbsp;
                <a href="../../principal.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de materias - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
