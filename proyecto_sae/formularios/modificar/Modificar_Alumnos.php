<?php

require_once "../../config/conexion.php";

$mensaje = "";
$error = "";
$alumno = null;

// 1) Si se envió el formulario de edición, actualizar el registro
if (isset($_POST["actualizar"])) {

    $id              = $_POST["id"];
    $matricula       = $_POST["matricula"];
    $nombre          = $_POST["nombre"];
    $apellidoPaterno = $_POST["apellidoPaterno"];
    $apellidoMaterno = $_POST["apellidoMaterno"];
    $domicilio       = $_POST["domicilio"];
    $correo          = $_POST["correo"];
    $telefono        = $_POST["telefono"];
    $estatus         = $_POST["estatus"];

    $sql = "UPDATE alumnos SET MATRICULA = ?, NOMBRE = ?, APELLIDO_PATERNO = ?, APELLIDO_MATERNO = ?,
                DOMICILIO = ?, CORREO = ?, TELEFONO = ?, ESTATUS = ? WHERE ID_ALUMNOS = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssssi", $matricula, $nombre, $apellidoPaterno, $apellidoMaterno, $domicilio, $correo, $telefono, $estatus, $id);

    if ($stmt->execute()) {
        $mensaje = "Alumno actualizado correctamente.";
    } else {
        $error = "Ocurrió un error al actualizar: " . $conexion->error;
    }

    $stmt->close();
}

// 2) Si se pidió editar un registro específico (?id=), cargar sus datos
if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "SELECT * FROM alumnos WHERE ID_ALUMNOS = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $alumno = $resultado->fetch_assoc();
    $stmt->close();
}

// 3) Si no se pidió editar nada en particular, listar todos los alumnos
$listaAlumnos = null;

if ($alumno === null) {
    $listaAlumnos = $conexion->query("SELECT * FROM alumnos ORDER BY ID_ALUMNOS DESC");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modificar Alumnos</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Modificar Alumnos</h2>

        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($alumno !== null) { ?>

                <!-- Formulario de edición, precargado con los datos del alumno -->

                <form method="POST">

                    <input type="hidden" name="id" value="<?php echo $alumno["ID_ALUMNOS"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Matrícula</label>
                        <input type="text" name="matricula" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["MATRICULA"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["NOMBRE"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido paterno</label>
                        <input type="text" name="apellidoPaterno" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["APELLIDO_PATERNO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido materno</label>
                        <input type="text" name="apellidoMaterno" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["APELLIDO_MATERNO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <input type="text" name="domicilio" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["DOMICILIO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="correo" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["CORREO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" required
                               value="<?php echo htmlspecialchars($alumno["TELEFONO"]); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($alumno["ESTATUS"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($alumno["ESTATUS"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">
                        Guardar cambios
                    </button>

                    <a href="Modificar_Alumnos.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <!-- Lista de alumnos para elegir cuál editar -->

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Matrícula</th>
                            <th>Nombre completo</th>
                            <th>Correo</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaAlumnos && $listaAlumnos->num_rows > 0) { ?>
                            <?php while ($fila = $listaAlumnos->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_ALUMNOS"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["MATRICULA"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBRE"] . " " . $fila["APELLIDO_PATERNO"] . " " . $fila["APELLIDO_MATERNO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["CORREO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["ESTATUS"]); ?></td>
                                    <td>
                                        <a href="?id=<?php echo $fila["ID_ALUMNOS"]; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay alumnos registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Alumnos.php">Registrar nuevo alumno</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de alumnos - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

</body>

</html>
