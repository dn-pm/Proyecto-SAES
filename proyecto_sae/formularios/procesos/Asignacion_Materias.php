<?php
require_once __DIR__ . "/../../config/conexion.php";

function h($t) { return htmlspecialchars((string)$t); }

$exito = "";
$error = "";

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

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Id materia</label>
                    <select name="id_materia" class="form-select" required>
                        <option value="">Selecciona una materia...</option>
                        <?php foreach ($materias as $m) { ?>
                            <option value="<?= $m["ID_MATERIAS"] ?>"><?= h($m["ID_MATERIAS"] . " - " . $m["CLAVE_MATERIA"] . " - " . $m["NOMBRE_MATERIA"]) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Id alumno</label>
                    <select name="id_alumno" class="form-select" required>
                        <option value="">Selecciona un alumno...</option>
                        <?php foreach ($alumnos as $a) { ?>
                            <option value="<?= $a["ID_ALUMNOS"] ?>"><?= h($a["ID_ALUMNOS"] . " - " . $a["MATRICULA"] . " - " . $a["NOMBRE_COMPLETO"]) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Id profesor</label>
                    <select name="id_profesor" class="form-select" required>
                        <option value="">Selecciona un profesor...</option>
                        <?php foreach ($profesores as $p) { ?>
                            <option value="<?= $p["ID_PROFESORES"] ?>"><?= h($p["ID_PROFESORES"] . " - " . $p["CLAVE"] . " - " . $p["NOMBRE_COMPLETO"]) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Asignar materia</button>

            </form>

            <hr>

            <h4 class="mb-3">Asignaturas registradas</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>Id asignatura</th><th>Materia</th><th>Alumno</th><th>Profesor</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($asignadas) === 0) { ?>
                            <tr><td colspan="4" class="text-center text-muted">Aún no hay asignaciones.</td></tr>
                        <?php } ?>
                        <?php foreach ($asignadas as $s) { ?>
                            <tr>
                                <td><?= h($s["ID_ASIGNATURA"]) ?></td>
                                <td><?= h($s["NOMBRE_MATERIA"]) ?></td>
                                <td><?= h($s["ALUMNO"]) ?></td>
                                <td><?= h($s["PROFESOR"]) ?></td>
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
