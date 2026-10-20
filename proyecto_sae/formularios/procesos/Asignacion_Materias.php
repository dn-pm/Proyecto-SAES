<?php
require_once __DIR__ . "/../../config/conexion.php";

function h($t) { return htmlspecialchars((string)$t); }

$exito = "";
$error = "";
$actualizado = false;

// ---------- ASIGNAR ----------
if (isset($_POST["guardar"])) {
    $idMateria  = (int)$_POST["id_materia"];
    $idAlumno   = (int)$_POST["id_alumno"];
    $idProfesor = (int)$_POST["id_profesor"];

    try {
        $sql = "INSERT INTO asignatura (ID_MATERIAS, ID_ALUMNOS, ID_PROFESORES) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iii", $idMateria, $idAlumno, $idProfesor);
        $stmt->execute();
        $stmt->close();
        $exito = "Asignatura guardada correctamente.";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Ese alumno ya tiene asignada esa materia.";
        } else {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

// ---------- MODIFICAR ----------
if (isset($_POST["actualizar"])) {
    $idAsignatura = (int)$_POST["id_asignatura"];
    $idMateria    = (int)$_POST["id_materia"];
    $idAlumno     = (int)$_POST["id_alumno"];
    $idProfesor   = (int)$_POST["id_profesor"];

    try {
        $sql = "UPDATE asignatura SET ID_MATERIAS = ?, ID_ALUMNOS = ?, ID_PROFESORES = ? WHERE ID_ASIGNATURA = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiii", $idMateria, $idAlumno, $idProfesor, $idAsignatura);
        $stmt->execute();
        $stmt->close();
        $exito = "Asignatura actualizada correctamente.";
        $actualizado = true;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Ese alumno ya tiene asignada esa materia.";
        } else {
            $error = "Ocurrió un error al actualizar: " . $e->getMessage();
        }
    }
}

// ---------- ELIMINAR ----------
if (isset($_POST["eliminar"])) {
    $idAsignatura = (int)$_POST["id_asignatura"];

    try {
        $stmt = $conexion->prepare("DELETE FROM asignatura WHERE ID_ASIGNATURA = ?");
        $stmt->bind_param("i", $idAsignatura);
        $stmt->execute();
        $stmt->close();
        $exito = "Asignatura eliminada.";
    } catch (mysqli_sql_exception $e) {
        $error = "Ocurrió un error al eliminar: " . $e->getMessage();
    }
}

// ---------- Si viene ?editar=ID, se carga la fila para llenar el formulario ----------
$editando = null;
if (isset($_GET["editar"]) && !$actualizado) {
    $idEdit = (int)$_GET["editar"];
    $stmt = $conexion->prepare("SELECT * FROM asignatura WHERE ID_ASIGNATURA = ?");
    $stmt->bind_param("i", $idEdit);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $editando = $fila ?: null;
}

$materias = $conexion->query("SELECT ID_MATERIAS, CLAVE_MATERIA, NOMBRE_MATERIA FROM materias WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_MATERIA")->fetch_all(MYSQLI_ASSOC);
$alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);
$profesores = $conexion->query("SELECT ID_PROFESORES, CLAVE, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM profesores WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

$asignadas = $conexion->query(
    "SELECT s.ID_ASIGNATURA, m.NOMBRE_MATERIA,
            CONCAT(a.NOMBRE, ' ', a.APELLIDO_PATERNO) AS ALUMNO,
            CONCAT(p.NOMBRE, ' ', p.APELLIDO_PATERNO) AS PROFESOR
     FROM asignatura s
     INNER JOIN materias m ON m.ID_MATERIAS = s.ID_MATERIAS
     INNER JOIN alumnos a ON a.ID_ALUMNOS = s.ID_ALUMNOS
     INNER JOIN profesores p ON p.ID_PROFESORES = s.ID_PROFESORES
     ORDER BY s.ID_ASIGNATURA DESC"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de materias</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Asignación de materias</h2>
        </div>

        <div class="card-body">

            <?php if ($exito !== "") { ?>
                <div class="alert alert-success"><?php echo h($exito); ?></div>
            <?php } ?>
            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo h($error); ?></div>
            <?php } ?>

            <?php if ($editando !== null) { ?>
                <div class="alert alert-warning">Modificando la asignatura #<?= h($editando["ID_ASIGNATURA"]) ?></div>
            <?php } ?>

            <form method="POST">

                <?php if ($editando !== null) { ?>
                    <input type="hidden" name="id_asignatura" value="<?= $editando["ID_ASIGNATURA"] ?>">
                <?php } ?>

                <div class="mb-3">
                    <label class="form-label">Id materia</label>
                    <select name="id_materia" class="form-select" required>
                        <option value="">Selecciona una materia...</option>
                        <?php foreach ($materias as $m) { ?>
                            <option value="<?= $m["ID_MATERIAS"] ?>" <?= ($editando !== null && $editando["ID_MATERIAS"] == $m["ID_MATERIAS"]) ? "selected" : "" ?>>
                                <?= h($m["ID_MATERIAS"] . " - " . $m["CLAVE_MATERIA"] . " - " . $m["NOMBRE_MATERIA"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Id alumno</label>
                    <select name="id_alumno" class="form-select" required>
                        <option value="">Selecciona un alumno...</option>
                        <?php foreach ($alumnos as $a) { ?>
                            <option value="<?= $a["ID_ALUMNOS"] ?>" <?= ($editando !== null && $editando["ID_ALUMNOS"] == $a["ID_ALUMNOS"]) ? "selected" : "" ?>>
                                <?= h($a["ID_ALUMNOS"] . " - " . $a["MATRICULA"] . " - " . $a["NOMBRE_COMPLETO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Id profesor</label>
                    <select name="id_profesor" class="form-select" required>
                        <option value="">Selecciona un profesor...</option>
                        <?php foreach ($profesores as $p) { ?>
                            <option value="<?= $p["ID_PROFESORES"] ?>" <?= ($editando !== null && $editando["ID_PROFESORES"] == $p["ID_PROFESORES"]) ? "selected" : "" ?>>
                                <?= h($p["ID_PROFESORES"] . " - " . $p["CLAVE"] . " - " . $p["NOMBRE_COMPLETO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <?php if ($editando !== null) { ?>
                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="Asignacion_Materias.php" class="btn btn-secondary">Cancelar</a>
                <?php } else { ?>
                    <button type="submit" name="guardar" class="btn btn-primary">Asignar materia</button>
                <?php } ?>

            </form>

            <hr>

            <h4 class="mb-3">Asignaturas registradas</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr><th>Id asignatura</th><th>Materia</th><th>Alumno</th><th>Profesor</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($asignadas) === 0) { ?>
                            <tr><td colspan="5" class="text-center text-muted">Aún no hay asignaciones.</td></tr>
                        <?php } ?>
                        <?php foreach ($asignadas as $s) { ?>
                            <tr>
                                <td><?= h($s["ID_ASIGNATURA"]) ?></td>
                                <td><?= h($s["NOMBRE_MATERIA"]) ?></td>
                                <td><?= h($s["ALUMNO"]) ?></td>
                                <td><?= h($s["PROFESOR"]) ?></td>
                                <td class="text-nowrap">
                                    <a href="Asignacion_Materias.php?editar=<?= $s["ID_ASIGNATURA"] ?>" class="btn btn-sm btn-outline-primary">Modificar</a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta asignatura?');">
                                        <input type="hidden" name="id_asignatura" value="<?= $s["ID_ASIGNATURA"] ?>">
                                        <button type="submit" name="eliminar" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="card-footer text-center">
            Asignación de materias - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
