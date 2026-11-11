<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/reportes_helper.php";

$idAlumno = (int)($_GET["id_alumno"] ?? 0);

$alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(APELLIDO_PATERNO, ' ', APELLIDO_MATERNO, ' ', NOMBRE) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, APELLIDO_MATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

$alumno = null;
$materias = [];
$gruposAlumno = [];
$totalCreditos = 0;

if ($idAlumno > 0) {
    $stmt = $conexion->prepare("SELECT ID_ALUMNOS, MATRICULA, CONCAT(APELLIDO_PATERNO, ' ', APELLIDO_MATERNO, ' ', NOMBRE) AS NOMBRE_COMPLETO FROM alumnos WHERE ID_ALUMNOS = ?");
    $stmt->bind_param("i", $idAlumno);
    $stmt->execute();
    $alumno = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($alumno !== null) {
        $stmt = $conexion->prepare(
            "SELECT m.CLAVE_MATERIA, m.NOMBRE_MATERIA, m.CREDITOS,
                    CONCAT(p.NOMBRE, ' ', p.APELLIDO_PATERNO, ' ', p.APELLIDO_MATERNO) AS PROFESOR
             FROM asignatura s
             INNER JOIN materias m ON m.ID_MATERIAS = s.ID_MATERIAS
             INNER JOIN profesores p ON p.ID_PROFESORES = s.ID_PROFESORES
             WHERE s.ID_ALUMNOS = ?
             ORDER BY m.NOMBRE_MATERIA"
        );
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        $materias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($materias as $m) { $totalCreditos += (int)$m["CREDITOS"]; }

        $stmt = $conexion->prepare("SELECT g.NOMBRE_GRUPO FROM grupo_alumno ga INNER JOIN grupos g ON g.ID_GRUPOS = ga.ID_GRUPOS WHERE ga.ID_ALUMNOS = ? AND ga.ESTATUS = 'ALTA' ORDER BY g.NOMBRE_GRUPO");
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $g) { $gruposAlumno[] = $g["NOMBRE_GRUPO"]; }
        $stmt->close();
    }
}

$textoGrupos = count($gruposAlumno) > 0 ? implode(", ", $gruposAlumno) : "Sin grupo";

// ---------- Descarga en Excel ----------
if (isset($_GET["descargar"]) && $alumno !== null && count($materias) > 0) {
    $datos = [];
    $datos[] = ["Lista de materias por alumno"];
    $datos[] = ["Alumno: " . $alumno["MATRICULA"] . " - " . $alumno["NOMBRE_COMPLETO"]];
    $datos[] = ["Grupo: " . $textoGrupos];
    $datos[] = [];
    $datos[] = ["NO.", "CLAVE", "MATERIA", "CREDITOS", "PROFESOR"];
    foreach ($materias as $n => $m) {
        $datos[] = [$n + 1, $m["CLAVE_MATERIA"], $m["NOMBRE_MATERIA"], (int)$m["CREDITOS"], $m["PROFESOR"]];
    }
    $datos[] = ["", "", "TOTAL DE CREDITOS", $totalCreditos, ""];
    enviarExcel($datos, "materias_alumno_" . nombreArchivo($alumno["MATRICULA"]), 4, [0, 1, 2]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte: materias por alumno</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            nav, .no-imprimir { display: none !important; }
            body { background: #ffffff !important; }
            .card { border: none !important; box-shadow: none !important; }
            .card-header { background: #ffffff !important; color: #000000 !important; border-bottom: 2px solid #000000; }
        }
    </style>
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Lista de materias por alumno</h2>
        </div>

        <div class="card-body">

            <form method="GET" class="row g-3 mb-4 no-imprimir">
                <div class="col-md-8">
                    <label class="form-label">Alumno</label>
                    <select name="id_alumno" class="form-select" required>
                        <option value="">Selecciona un alumno...</option>
                        <?php foreach ($alumnos as $a) { ?>
                            <option value="<?= $a["ID_ALUMNOS"] ?>" <?= $idAlumno == $a["ID_ALUMNOS"] ? "selected" : "" ?>>
                                <?= h($a["MATRICULA"] . " - " . $a["NOMBRE_COMPLETO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary">Ver lista</button>
                </div>
            </form>

            <?php if ($idAlumno > 0 && $alumno === null) { ?>
                <div class="alert alert-warning">No se encontró el alumno seleccionado.</div>
            <?php } ?>

            <?php if ($alumno !== null) { ?>
                <hr class="no-imprimir">

                <div class="mb-3">
                    <h5 class="mb-1"><?= h($alumno["NOMBRE_COMPLETO"]) ?></h5>
                    <div class="text-muted">
                        Matrícula: <?= h($alumno["MATRICULA"]) ?> &nbsp;|&nbsp;
                        Grupo: <?= h($textoGrupos) ?> &nbsp;|&nbsp;
                        Materias: <strong><?= count($materias) ?></strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr><th>No.</th><th>Clave</th><th>Materia</th><th>Créditos</th><th>Profesor</th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($materias) === 0) { ?>
                                <tr><td colspan="5" class="text-center text-muted">Este alumno no tiene materias asignadas.</td></tr>
                            <?php } ?>
                            <?php foreach ($materias as $n => $m) { ?>
                                <tr>
                                    <td><?= $n + 1 ?></td>
                                    <td><?= h($m["CLAVE_MATERIA"]) ?></td>
                                    <td><?= h($m["NOMBRE_MATERIA"]) ?></td>
                                    <td><?= h($m["CREDITOS"]) ?></td>
                                    <td><?= h($m["PROFESOR"]) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <?php if (count($materias) > 0) { ?>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="3" class="text-end">Total de créditos</th>
                                    <th colspan="2"><?= $totalCreditos ?></th>
                                </tr>
                            </tfoot>
                        <?php } ?>
                    </table>
                </div>

                <?php if (count($materias) > 0) { ?>
                    <div class="no-imprimir">
                        <a href="?id_alumno=<?= $idAlumno ?>&descargar=1" class="btn btn-success">Descargar Excel</a>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Imprimir</button>
                    </div>
                <?php } ?>
            <?php } ?>

        </div>

        <div class="card-footer text-center">
            Reporte de materias por alumno - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
