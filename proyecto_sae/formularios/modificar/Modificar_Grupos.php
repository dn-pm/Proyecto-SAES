<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$grupo = null;

if (isset($_POST["actualizar"])) {

    $id          = $_POST["id"];
    $nombre_g    = $_POST["grupo"];
    $descripcion = $_POST["descripcion"];
    $estatus     = $_POST["estatus"];

    $sql = "UPDATE grupo SET NOMBRE_G = ?, DESCRIPCION_G = ?, ESTATUS_G = ? WHERE ID_GRUPO = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssi", $nombre_g, $descripcion, $estatus, $id);

    if ($stmt->execute()) {
        $mensaje = "Grupo actualizado correctamente.";
    } else {
        $error = "Ocurrió un error al actualizar: " . $conexion->error;
    }

    $stmt->close();
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "SELECT * FROM grupo WHERE ID_GRUPO = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $grupo = $resultado->fetch_assoc();
    $stmt->close();
}

$listaGrupos = null;

if ($grupo === null) {
    $listaGrupos = $conexion->query("SELECT * FROM grupo ORDER BY ID_GRUPO DESC");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modificar Grupos</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Modificar Grupos</h2>

        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($grupo !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $grupo["ID_GRUPO"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Nombre del grupo</label>
                        <input type="text" name="grupo" class="form-control" required
                               value="<?php echo htmlspecialchars($grupo["NOMBRE_G"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción del grupo</label>
                        <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($grupo["DESCRIPCION_G"]); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($grupo["ESTATUS_G"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($grupo["ESTATUS_G"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">
                        Guardar cambios
                    </button>

                    <a href="Modificar_Grupos.php" class="btn btn-secondary">Cancelar</a>

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
                        <?php if ($listaGrupos && $listaGrupos->num_rows > 0) { ?>
                            <?php while ($fila = $listaGrupos->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_GRUPO"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBRE_G"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["DESCRIPCION_G"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS_G"]); ?></td>
                                    <td>
                                        <a href="?id=<?php echo $fila["ID_GRUPO"]; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay grupos registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Grupos.php">Registrar nuevo grupo</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de grupos - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

</body>

</html>
