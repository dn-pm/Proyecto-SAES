<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$materia = null;

if (isset($_POST["actualizar"])) {

    $id          = $_POST["id"];
    $nombre_m    = $_POST["materia"];
    $descripcion = $_POST["descripcion"];
    $estatus     = $_POST["estatus"];

    $sql = "UPDATE materias SET NOMBRE_M = ?, DESCRIPCION_M = ?, ESTATUS_M = ? WHERE ID_MATERIA = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssi", $nombre_m, $descripcion, $estatus, $id);

    if ($stmt->execute()) {
        $mensaje = "Materia actualizada correctamente.";
    } else {
        $error = "Ocurrió un error al actualizar: " . $conexion->error;
    }

    $stmt->close();
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "SELECT * FROM materias WHERE ID_MATERIA = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $materia = $resultado->fetch_assoc();
    $stmt->close();
}

$listaMaterias = null;

if ($materia === null) {
    $listaMaterias = $conexion->query("SELECT * FROM materias ORDER BY ID_MATERIA DESC");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modificar Materias</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Modificar Materias</h2>

        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($materia !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $materia["ID_MATERIA"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Nombre de la materia</label>
                        <input type="text" name="materia" class="form-control" required
                               value="<?php echo htmlspecialchars($materia["NOMBRE_M"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción de la materia</label>
                        <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($materia["DESCRIPCION_M"]); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($materia["ESTATUS_M"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($materia["ESTATUS_M"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">
                        Guardar cambios
                    </button>

                    <a href="Modificar_Materias.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaMaterias && $listaMaterias->num_rows > 0) { ?>
                            <?php while ($fila = $listaMaterias->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_MATERIA"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBRE_M"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["DESCRIPCION_M"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS_M"]); ?></td>
                                    <td>
                                        <a href="?id=<?php echo $fila["ID_MATERIA"]; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay materias registradas todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Materias.php">Registrar nueva materia</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de materias - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

</body>

</html>
