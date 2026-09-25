<?php

require_once "../../config/conexion.php";

$mostrarDatos = false;
$error = "";

if (isset($_POST["guardar"])) {

    $grupo       = $_POST["grupo"];
    $descripcion = $_POST["descripcion"];

    $sql = "INSERT INTO grupo (NOMBRE_G, DESCRIPCION_G) VALUES (?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $grupo, $descripcion);

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

    <title>Registro de Grupos</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">

            <h2>Registro de Grupos</h2>

        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">

                <!-- Nombre del grupo -->

                <div class="mb-3">

                    <label class="form-label">
                        Nombre del grupo
                    </label>

                    <input
                        type="text"
                        name="grupo"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Descripción -->

                <div class="mb-3">

                    <label class="form-label">
                        Descripción del grupo
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
                    Registrar grupo
                </button>

            </form>


            <?php

            if ($mostrarDatos == true) {

            ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Grupo guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">
                    Información del grupo
                </h4>

                <table class="table table-bordered">

                    <tr>

                        <th>Nombre del grupo</th>

                        <td>
                            <?php echo $grupo; ?>
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
                <a href="../modificar/Modificar_Grupos.php">Modificar un grupo existente</a>
                &nbsp;|&nbsp;
                <a href="../../index.php">Regresar al menú</a>
            </div>

        </div>

        <div class="card-footer text-center">

            Registro de grupos - Programación II Patlan Medrano Daniel

        </div>

    </div>

</div>

</body>

</html>
