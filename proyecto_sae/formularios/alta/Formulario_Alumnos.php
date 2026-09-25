<?php

require_once "../../config/conexion.php";

// Variable para saber si se guardaron los datos
$mostrarDatos = false;
$error = "";

// Comprobar si se presionó el botón
if (isset($_POST["guardar"])) {

    // Obtener los datos del formulario
    $matricula       = $_POST["matricula"];
    $nombre          = $_POST["nombre"];
    $apellidoPaterno = $_POST["apellidoPaterno"];
    $apellidoMaterno = $_POST["apellidoMaterno"];
    $domicilio       = $_POST["domicilio"];
    $correo          = $_POST["correo"];
    $telefono        = $_POST["telefono"];

    // Insertar en la base de datos usando sentencia preparada
    $sql = "INSERT INTO alumnos (MATRICULA, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, DOMICILIO, CORREO, TELEFONO)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssss", $matricula, $nombre, $apellidoPaterno, $apellidoMaterno, $domicilio, $correo, $telefono);

    if ($stmt->execute()) {
        $mostrarDatos = true;
    } else {
        $error = "Ocurrió un error al guardar: " . $conexion->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registro de Alumnos</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Registro de Alumnos</h2>

        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">

                <!-- Matrícula -->

                <div class="mb-3">

                    <label class="form-label">
                        Matrícula
                    </label>

                    <input
                        type="text"
                        name="matricula"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Nombre -->

                <div class="mb-3">

                    <label class="form-label">
                        Nombre
                    </label>

                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Apellido paterno -->

                <div class="mb-3">

                    <label class="form-label">
                        Apellido paterno
                    </label>

                    <input
                        type="text"
                        name="apellidoPaterno"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Apellido materno -->

                <div class="mb-3">

                    <label class="form-label">
                        Apellido materno
                    </label>

                    <input
                        type="text"
                        name="apellidoMaterno"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Domicilio -->

                <div class="mb-3">

                    <label class="form-label">
                        Domicilio
                    </label>

                    <input
                        type="text"
                        name="domicilio"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Correo -->

                <div class="mb-3">

                    <label class="form-label">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        name="correo"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Teléfono -->

                <div class="mb-3">

                    <label class="form-label">
                        Teléfono
                    </label>

                    <input
                        type="tel"
                        name="telefono"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Botón -->

                <button
                    type="submit"
                    name="guardar"
                    class="btn btn-primary"
                >
                    Registrar alumno
                </button>

            </form>


            <?php

            // Mostrar los datos después de guardarlos

            if ($mostrarDatos == true) {

            ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Alumno guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">
                    Datos del alumno
                </h4>

                <table class="table table-bordered">

                    <tr>
                        <th>Matrícula</th>
                        <td><?php echo $matricula; ?></td>
                    </tr>

                    <tr>
                        <th>Nombre</th>
                        <td><?php echo $nombre; ?></td>
                    </tr>

                    <tr>
                        <th>Apellido paterno</th>
                        <td><?php echo $apellidoPaterno; ?></td>
                    </tr>

                    <tr>
                        <th>Apellido materno</th>
                        <td><?php echo $apellidoMaterno; ?></td>
                    </tr>

                    <tr>
                        <th>Domicilio</th>
                        <td><?php echo $domicilio; ?></td>
                    </tr>

                    <tr>
                        <th>Correo electrónico</th>
                        <td><?php echo $correo; ?></td>
                    </tr>

                    <tr>
                        <th>Teléfono</th>
                        <td><?php echo $telefono; ?></td>
                    </tr>

                </table>

            <?php

            }

            ?>

            <div class="text-center mt-3">
                <a href="../modificar/Modificar_Alumnos.php">Modificar un alumno existente</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">

            Registro de alumnos - Programación II Patlan Medrano Daniel

        </div>

    </div>

</div>

</body>

</html>
