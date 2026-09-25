<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$profesor = null;

if (isset($_POST["actualizar"])) {

    $id              = $_POST["id"];
    $numeroEmpleado  = $_POST["numeroEmpleado"];
    $nombre          = $_POST["nombre"];
    $apellidoPaterno = $_POST["apellidoPaterno"];
    $apellidoMaterno = $_POST["apellidoMaterno"];
    $domicilio       = $_POST["domicilio"];
    $correo          = $_POST["correo"];
    $telefono        = $_POST["telefono"];
    $estatus         = $_POST["estatus"];

    $sql = "UPDATE profesores SET NUMERO_EMPLEADO = ?, NOMBRE = ?, APELLIDO_PATERNO = ?, APELLIDO_MATERNO = ?,
                DOMICILIO = ?, CORREO = ?, TELEFONO = ?, ESTATUS = ? WHERE ID_PROFESORES = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssssi", $numeroEmpleado, $nombre, $apellidoPaterno, $apellidoMaterno, $domicilio, $correo, $telefono, $estatus, $id);

    if ($stmt->execute()) {
        $mensaje = "Profesor actualizado correctamente.";
    } else {
        $error = "Ocurrió un error al actualizar: " . $conexion->error;
    }

    $stmt->close();
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "SELECT * FROM profesores WHERE ID_PROFESORES = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $profesor = $resultado->fetch_assoc();
    $stmt->close();
}

$listaProfesores = null;

if ($profesor === null) {
    $listaProfesores = $conexion->query("SELECT * FROM profesores ORDER BY ID_PROFESORES DESC");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modificar Profesores</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Modificar Profesores</h2>

        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($profesor !== null) { ?>

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $profesor["ID_PROFESORES"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Número de empleado</label>
                        <input type="text" name="numeroEmpleado" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["NUMERO_EMPLEADO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["NOMBRE"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido paterno</label>
                        <input type="text" name="apellidoPaterno" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["APELLIDO_PATERNO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido materno</label>
                        <input type="text" name="apellidoMaterno" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["APELLIDO_MATERNO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <input type="text" name="domicilio" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["DOMICILIO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="correo" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["CORREO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" required
                               value="<?php echo htmlspecialchars($profesor["TELEFONO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($profesor["ESTATUS"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($profesor["ESTATUS"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">
                        Guardar cambios
                    </button>

                    <a href="Modificar_Profesores.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>No. empleado</th>
                            <th>Nombre completo</th>
                            <th>Correo</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaProfesores && $listaProfesores->num_rows > 0) { ?>
                            <?php while ($fila = $listaProfesores->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_PROFESORES"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["NUMERO_EMPLEADO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBRE"] . " " . $fila["APELLIDO_PATERNO"] . " " . $fila["APELLIDO_MATERNO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["CORREO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS"]); ?></td>
                                    <td>
                                        <a href="?id=<?php echo $fila["ID_PROFESORES"]; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay profesores registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Profesores.php">Registrar nuevo profesor</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de profesores - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

</body>

</html>
