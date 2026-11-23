<?php
require_once __DIR__ . "/../../config/conexion.php";
requerirRol(['ADMINISTRADOR']);

$mensaje = "";
$error = "";
$usuarioEdit = null;

$puestos = $conexion->query("SELECT ID_PUESTO, DESCRIPCION_PUESTO FROM puesto WHERE STATUS_PUESTO = 'ALTA' ORDER BY ID_PUESTO")->fetch_all(MYSQLI_ASSOC);
$profesores = $conexion->query("SELECT ID_PROFESORES, CLAVE, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM profesores WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);
$alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

if (isset($_POST["actualizar"])) {
    $id            = (int)$_POST["id"];
    $nombreUsuario = trim($_POST["nombre_usuario"] ?? "");
    $nuevaPass     = $_POST["password_usuario"] ?? "";
    $puestoId      = (int)$_POST["puesto"];
    $idProfesor    = !empty($_POST["id_profesor"]) ? (int)$_POST["id_profesor"] : null;
    $idAlumno      = !empty($_POST["id_alumno"]) ? (int)$_POST["id_alumno"] : null;
    $estatus       = $_POST["estatus"];

    if ($puestoId === 1) {
        $idProfesor = null;
        $idAlumno   = null;
    } elseif ($puestoId === 2) {
        $idAlumno = null;
        if ($idProfesor === null) {
            $error = "Debes seleccionar el profesor vinculado.";
        }
    } elseif ($puestoId === 3) {
        $idProfesor = null;
        if ($idAlumno === null) {
            $error = "Debes seleccionar el alumno vinculado.";
        }
    }

    if ($error === "") {
        try {
            if ($nuevaPass !== "") {
                $hash = sha1($nuevaPass);
                $sql = "UPDATE usuarios SET NOMBREUSER = ?, PWUSER = ?, PUESTO = ?, ID_PROFESORES = ?, ID_ALUMNOS = ?, STATUS_USER = ? WHERE ID_USER = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssiiisi", $nombreUsuario, $hash, $puestoId, $idProfesor, $idAlumno, $estatus, $id);
            } else {
                $sql = "UPDATE usuarios SET NOMBREUSER = ?, PUESTO = ?, ID_PROFESORES = ?, ID_ALUMNOS = ?, STATUS_USER = ? WHERE ID_USER = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("siiisi", $nombreUsuario, $puestoId, $idProfesor, $idAlumno, $estatus, $id);
            }
            $stmt->execute();
            $stmt->close();
            $mensaje = "Usuario actualizado correctamente.";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Ese nombre de usuario o persona vinculada ya está registrado en otra cuenta.";
            } else {
                $error = "Ocurrió un error al actualizar: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET["id"])) {
    $id = (int)$_GET["id"];
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE ID_USER = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $usuarioEdit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$listaUsuarios = null;
if ($usuarioEdit === null) {
    $listaUsuarios = $conexion->query(
        "SELECT u.ID_USER, u.NOMBREUSER, p.DESCRIPCION_PUESTO, u.STATUS_USER
         FROM usuarios u
         INNER JOIN puesto p ON p.ID_PUESTO = u.PUESTO
         ORDER BY u.ID_USER DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuarios</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Modificar Usuarios</h2>
        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <?php if ($usuarioEdit !== null) { ?>

                <form method="POST">
                    <input type="hidden" name="id" value="<?= (int)$usuarioEdit["ID_USER"] ?>">

                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" name="nombre_usuario" class="form-control" maxlength="50" required
                               value="<?= htmlspecialchars($usuarioEdit["NOMBREUSER"]) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña (dejar en blanco para conservar la actual)</label>
                        <input type="password" name="password_usuario" class="form-control" minlength="4">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Puesto / Jerarquía</label>
                        <select name="puesto" id="selectPuesto" class="form-select" required onchange="actualizarVinculo()">
                            <?php foreach ($puestos as $p) { ?>
                                <option value="<?= (int)$p["ID_PUESTO"] ?>" <?= ((int)$usuarioEdit["PUESTO"] === (int)$p["ID_PUESTO"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($p["ID_PUESTO"] . " - " . $p["DESCRIPCION_PUESTO"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3 <?= ((int)$usuarioEdit["PUESTO"] === 2) ? "" : "d-none" ?>" id="bloqueProfesor">
                        <label class="form-label">Profesor vinculado</label>
                        <select name="id_profesor" id="selectProfesor" class="form-select">
                            <option value="">Selecciona un profesor...</option>
                            <?php foreach ($profesores as $pr) { ?>
                                <option value="<?= (int)$pr["ID_PROFESORES"] ?>" <?= ((int)($usuarioEdit["ID_PROFESORES"] ?? 0) === (int)$pr["ID_PROFESORES"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($pr["CLAVE"] . " - " . $pr["NOMBRE_COMPLETO"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3 <?= ((int)$usuarioEdit["PUESTO"] === 3) ? "" : "d-none" ?>" id="bloqueAlumno">
                        <label class="form-label">Alumno vinculado</label>
                        <select name="id_alumno" id="selectAlumno" class="form-select">
                            <option value="">Selecciona un alumno...</option>
                            <?php foreach ($alumnos as $al) { ?>
                                <option value="<?= (int)$al["ID_ALUMNOS"] ?>" <?= ((int)($usuarioEdit["ID_ALUMNOS"] ?? 0) === (int)$al["ID_ALUMNOS"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($al["MATRICULA"] . " - " . $al["NOMBRE_COMPLETO"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-select">
                            <option value="ALTA" <?= $usuarioEdit["STATUS_USER"] === "ALTA" ? "selected" : "" ?>>ALTA</option>
                            <option value="BAJA" <?= $usuarioEdit["STATUS_USER"] === "BAJA" ? "selected" : "" ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="<?= $base ?>/Cruds/crudusuarios.php" class="btn btn-secondary">Cancelar</a>
                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Puesto</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaUsuarios && $listaUsuarios->num_rows > 0) { ?>
                            <?php while ($fila = $listaUsuarios->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= (int)$fila["ID_USER"] ?></td>
                                    <td><?= htmlspecialchars($fila["NOMBREUSER"]) ?></td>
                                    <td><?= htmlspecialchars($fila["DESCRIPCION_PUESTO"]) ?></td>
                                    <td><?= htmlspecialchars($fila["STATUS_USER"]) ?></td>
                                    <td><a href="?id=<?= (int)$fila["ID_USER"] ?>" class="btn btn-sm btn-primary">Editar</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay usuarios registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/alta/Formulario_Usuario.php">Registrar nuevo usuario</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudusuarios.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de usuarios - Programación II Patlan Medrano Daniel
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
