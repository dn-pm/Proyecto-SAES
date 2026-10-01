<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$grupo = null;

if (isset($_POST["actualizar"])) {

        $id      = (int) $_POST["id"];
        $nombregrupo = $_POST["nombregrupo"];
        $turno = $_POST["turno"];
        $semestre = $_POST["semestre"];
        $estatus = $_POST["estatus"];

    try {
        $sql = "UPDATE grupos SET NOMBRE_GRUPO = ?, TURNO = ?, SEMESTRE = ?, ESTATUS = ? WHERE ID_GRUPOS = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $nombregrupo, $turno, $semestre, $estatus, $id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Grupo actualizado correctamente.";
    } catch (mysqli_sql_exception $e) {
        $error = "Ocurrió un error al actualizar: " . $e->getMessage();
    }
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $stmt = $conexion->prepare("SELECT * FROM grupos WHERE ID_GRUPOS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $grupo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$listaGrupos = null;

if ($grupo === null) {
    $listaGrupos = $conexion->query("SELECT * FROM grupos ORDER BY ID_GRUPOS DESC");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Grupos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Modificar Grupos</h2>
        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if ($grupo !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $grupo["ID_GRUPOS"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Nombre del grupo</label>
                        <input type="text" name="nombregrupo" class="form-control" maxlength="20" required
                               value="<?php echo htmlspecialchars($grupo["NOMBRE_GRUPO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Turno</label>
                        <input type="text" name="turno" class="form-control" maxlength="15" required
                               value="<?php echo htmlspecialchars($grupo["TURNO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Semestre</label>
                        <input type="text" name="semestre" class="form-control" maxlength="10" required
                               value="<?php echo htmlspecialchars($grupo["SEMESTRE"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($grupo["ESTATUS"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($grupo["ESTATUS"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="Modificar_Grupos.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Turno</th>
                            <th>Semestre</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaGrupos && $listaGrupos->num_rows > 0) { ?>
                            <?php while ($fila = $listaGrupos->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_GRUPOS"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBRE_GRUPO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["TURNO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["SEMESTRE"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS"]); ?></td>
                                    <td><a href="?id=<?php echo $fila["ID_GRUPOS"]; ?>" class="btn btn-sm btn-primary">Editar</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay grupos registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Grupos.php">Registrar nuevo grupo</a>
                &nbsp;|&nbsp;
                <a href="../../principal.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de grupos - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
