<?php

require_once "../../config/conexion.php";

$mostrarDatos = false;
$error = "";

if (isset($_POST["guardar"])) {

    $materia     = $_POST["materia"];
    $descripcion = $_POST["descripcion"];

    $sql = "INSERT INTO materias (NOMBRE_M, DESCRIPCION_M) VALUES (?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $materia, $descripcion);

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

    <title>Registro de Materias</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Registro de Materias</h2>

        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">

                <!-- Nombre de la materia -->

                <div class="mb-3">

                    <label class="form-label">
                        Nombre de la materia
                    </label>

                    <input
                        type="text"
                        name="materia"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Descripción -->

                <div class="mb-3">

                    <label class="form-label">
                        Descripción de la materia
                    </label>

                    <textarea
                        name="descripcion"
                        class="form-control"
                        rows="4"
                        required
                    ></textarea>

                </div>


                <!-- Botón -->

                <button
                    type="submit"
                    name="guardar"
                    class="btn btn-primary"
                >
                    Registrar materia
                </button>

            </form>


            <?php

            if ($mostrarDatos == true) {

            ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Materia guardada correctamente en la base de datos.
                </div>

                <h4 class="mb-3">
                    Información de la materia
                </h4>

                <table class="table table-bordered">

                    <tr>

                        <th>Nombre de la materia</th>

                        <td>
                            <?php echo $materia; ?>
                        </td>

                    </tr>

                    <tr>

                        <th>Descripción</th>

                        <td>
                            <?php echo $descripcion; ?>
                        </td>

                    </tr>

                </table>

            <?php

            }

            ?>

            <div class="text-center mt-3">
                <a href="../modificar/Modificar_Materias.php">Modificar una materia existente</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">

            Registro de materias - Programación II Patlan Medrano Daniel

        </div>

    </div>

</div>

</body>

</html>
