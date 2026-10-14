<?php
require_once __DIR__ . "/../../config/conexion.php";

function h($t) { return htmlspecialchars((string)$t); }

$exito = "";
$error = "";

if (isset($_POST["guardar"])) {
    $idGrupo  = (int)$_POST["id_grupo"];
    $idAlumno = (int)$_POST["id_alumno"];
    $estatus  = isset($_POST["activo"]) ? "ALTA" : "BAJA";

    try {
        $sql = "INSERT INTO grupo_alumno (ID_GRUPOS, ID_ALUMNOS, ESTATUS) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iis", $idGrupo, $idAlumno, $estatus);
        $stmt->execute();
        $stmt->close();
        $exito = "Alumno inscrito al grupo correctamente.";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Ese alumno ya está inscrito en ese grupo.";
        } else {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

$grupos = $conexion->query("SELECT ID_GRUPOS, NOMBRE_GRUPO FROM grupos WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_GRUPO")->fetch_all(MYSQLI_ASSOC);
$alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

$inscritos = $conexion->query(
    "SELECT ga.ID_GRUPOS, g.NOMBRE_GRUPO, ga.ID_ALUMNOS, a.MATRICULA,
            CONCAT(a.NOMBRE, ' ', a.APELLIDO_PATERNO, ' ', a.APELLIDO_MATERNO) AS NOMBRE_COMPLETO,
            ga.ESTATUS
     FROM grupo_alumno ga
     INNER JOIN grupos g ON g.ID_GRUPOS = ga.ID_GRUPOS
     INNER JOIN alumnos a ON a.ID_ALUMNOS = ga.ID_ALUMNOS
     ORDER BY g.NOMBRE_GRUPO, a.APELLIDO_PATERNO"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción de alumnos a grupos</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Inscripción de alumnos a grupos</h2>
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
                    <label class="form-label">Id grupo</label>
                    <select name="id_grupo" class="form-select" required>
                        <option value="">Selecciona un grupo...</option>
                        <?php foreach ($grupos as $g) { ?>
                            <option value="<?= $g["ID_GRUPOS"] ?>"><?= h($g["ID_GRUPOS"] . " - " . $g["NOMBRE_GRUPO"]) ?></option>
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

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                    <label class="form-check-label" for="activo">Estatus activo</label>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Inscribir alumno</button>

            </form>

            <hr>

            <h4 class="mb-3">Alumnos inscritos</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>Id grupo</th><th>Grupo</th><th>Id alumno</th><th>Matrícula</th><th>Alumno</th><th>Estatus</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($inscritos) === 0) { ?>
                            <tr><td colspan="6" class="text-center text-muted">Aún no hay inscripciones.</td></tr>
                        <?php } ?>
                        <?php foreach ($inscritos as $i) { ?>
                            <tr>
                                <td><?= h($i["ID_GRUPOS"]) ?></td>
                                <td><?= h($i["NOMBRE_GRUPO"]) ?></td>
                                <td><?= h($i["ID_ALUMNOS"]) ?></td>
                                <td><?= h($i["MATRICULA"]) ?></td>
                                <td><?= h($i["NOMBRE_COMPLETO"]) ?></td>
                                <td><?= h($i["ESTATUS"]) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="card-footer text-center">
            Inscripción a grupos - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
