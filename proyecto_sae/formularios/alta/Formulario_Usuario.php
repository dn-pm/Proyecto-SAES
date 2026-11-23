<?php
require_once __DIR__ . "/../../config/conexion.php";
requerirRol(['ADMINISTRADOR']);

$mostrarDatos = false;
$error = "";

$puestos = $conexion->query("SELECT ID_PUESTO, DESCRIPCION_PUESTO FROM puesto WHERE STATUS_PUESTO = 'ALTA' ORDER BY ID_PUESTO")->fetch_all(MYSQLI_ASSOC);
$profesores = $conexion->query("SELECT ID_PROFESORES, CLAVE, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM profesores WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);
$alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

$nombreUsuario = "";
$puestoId = 0;
$puestoNombre = "";

if (isset($_POST["guardar"])) {
    $nombreUsuario   = trim($_POST["nombre_usuario"] ?? "");
    $passwordUsuario = $_POST["password_usuario"] ?? "";
    $puestoId        = (int)($_POST["puesto"] ?? 0);
    $idProfesor      = !empty($_POST["id_profesor"]) ? (int)$_POST["id_profesor"] : null;
    $idAlumno        = !empty($_POST["id_alumno"]) ? (int)$_POST["id_alumno"] : null;

    if ($puestoId === 1) {
        $idProfesor = null;
        $idAlumno   = null;
    } elseif ($puestoId === 2) {
        $idAlumno = null;
        if ($idProfesor === null) {
            $error = "Debes seleccionar el profesor al que pertenece este usuario.";
        }
    } elseif ($puestoId === 3) {
        $idProfesor = null;
        if ($idAlumno === null) {
            $error = "Debes seleccionar el alumno al que pertenece este usuario.";
        }
    }

    if ($error === "") {
        $hash = sha1($passwordUsuario);
        try {
            $sql = "INSERT INTO usuarios (NOMBREUSER, PWUSER, PUESTO, ID_PROFESORES, ID_ALUMNOS) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssiii", $nombreUsuario, $hash, $puestoId, $idProfesor, $idAlumno);
            $stmt->execute();
            $stmt->close();

            foreach ($puestos as $p) {
                if ((int)$p["ID_PUESTO"] === $puestoId) {
                    $puestoNombre = $p["DESCRIPCION_PUESTO"];
                    break;
                }
            }
            $mostrarDatos = true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Ese nombre de usuario o esa persona ya tiene una cuenta asignada.";
            } else {
                $error = "Ocurrió un error al guardar: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Usuarios</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Nombre de usuario (login)</label>
                    <input type="text" name="nombre_usuario" class="form-control" maxlength="50" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password_usuario" class="form-control" minlength="4" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Puesto / Jerarquía</label>
                    <select name="puesto" id="selectPuesto" class="form-select" required onchange="actualizarVinculo()">
                        <option value="">Selecciona un puesto...</option>
                        <?php foreach ($puestos as $p) { ?>
                            <option value="<?= (int)$p["ID_PUESTO"] ?>">
                                <?= htmlspecialchars($p["ID_PUESTO"] . " - " . $p["DESCRIPCION_PUESTO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3 d-none" id="bloqueProfesor">
                    <label class="form-label">Profesor vinculado</label>
                    <select name="id_profesor" id="selectProfesor" class="form-select">
                        <option value="">Selecciona un profesor...</option>
                        <?php foreach ($profesores as $pr) { ?>
                            <option value="<?= (int)$pr["ID_PROFESORES"] ?>">
                                <?= htmlspecialchars($pr["CLAVE"] . " - " . $pr["NOMBRE_COMPLETO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3 d-none" id="bloqueAlumno">
                    <label class="form-label">Alumno vinculado</label>
                    <select name="id_alumno" id="selectAlumno" class="form-select">
                        <option value="">Selecciona un alumno...</option>
                        <?php foreach ($alumnos as $al) { ?>
                            <option value="<?= (int)$al["ID_ALUMNOS"] ?>">
                                <?= htmlspecialchars($al["MATRICULA"] . " - " . $al["NOMBRE_COMPLETO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar usuario</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Usuario guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información del usuario</h4>

                <table class="table table-bordered">
                    <tr><th>Nombre de usuario</th><td><?= htmlspecialchars($nombreUsuario) ?></td></tr>
                    <tr><th>Puesto</th><td><?= htmlspecialchars($puestoNombre) ?></td></tr>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Usuario.php">Modificar un usuario existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudusuarios.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de usuarios - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script>
function actualizarVinculo() {
    const puesto = document.getElementById('selectPuesto').value;
    const bloqueProf = document.getElementById('bloqueProfesor');
    const bloqueAlum = document.getElementById('bloqueAlumno');
    const selProf = document.getElementById('selectProfesor');
    const selAlum = document.getElementById('selectAlumno');

    bloqueProf.classList.add('d-none');
    bloqueAlum.classList.add('d-none');
    selProf.required = false;
    selAlum.required = false;

    if (puesto === '2') {
        bloqueProf.classList.remove('d-none');
        selProf.required = true;
    } else if (puesto === '3') {
        bloqueAlum.classList.remove('d-none');
        selAlum.required = true;
    }
}
</script>
<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
