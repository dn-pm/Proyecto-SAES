<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/reportes_helper.php";

$idMateria = (int)($_GET["id_materia"] ?? 0);
$idGrupo   = (int)($_GET["id_grupo"] ?? 0);

$materias = $conexion->query("SELECT ID_MATERIAS, CLAVE_MATERIA, NOMBRE_MATERIA FROM materias WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_MATERIA")->fetch_all(MYSQLI_ASSOC);
$grupos   = $conexion->query("SELECT ID_GRUPOS, NOMBRE_GRUPO FROM grupos WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_GRUPO")->fetch_all(MYSQLI_ASSOC);
$unidades = $conexion->query("SELECT ID_UNIDAD, DESCRIPCION FROM unidad WHERE ESTATUS = 'ALTA' ORDER BY ID_UNIDAD")->fetch_all(MYSQLI_ASSOC);

$materia = null;
$grupo = null;
$filas = [];
$notas = [];

if ($idMateria > 0 && $idGrupo > 0) {

    $stmt = $conexion->prepare("SELECT ID_MATERIAS, CLAVE_MATERIA, NOMBRE_MATERIA FROM materias WHERE ID_MATERIAS = ?");
    $stmt->bind_param("i", $idMateria);
    $stmt->execute();
    $materia = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    $stmt = $conexion->prepare("SELECT ID_GRUPOS, NOMBRE_GRUPO FROM grupos WHERE ID_GRUPOS = ?");
    $stmt->bind_param("i", $idGrupo);
    $stmt->execute();
    $grupo = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($materia !== null && $grupo !== null) {

        // Alumnos activos del grupo que tienen asignada la materia
        $stmt = $conexion->prepare(
            "SELECT a.ID_ALUMNOS, a.MATRICULA,
                    CONCAT(a.APELLIDO_PATERNO, ' ', a.APELLIDO_MATERNO, ' ', a.NOMBRE) AS NOMBRE_COMPLETO
             FROM grupo_alumno ga
             INNER JOIN alumnos a ON a.ID_ALUMNOS = ga.ID_ALUMNOS AND a.ESTATUS = 'ALTA'
             INNER JOIN asignatura s ON s.ID_ALUMNOS = a.ID_ALUMNOS AND s.ID_MATERIAS = ?
             WHERE ga.ID_GRUPOS = ? AND ga.ESTATUS = 'ALTA'
             ORDER BY a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE"
        );
        $stmt->bind_param("ii", $idMateria, $idGrupo);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Calificaciones de esa materia: $notas[id_alumno][id_unidad]
        $stmt = $conexion->prepare("SELECT ID_ALUMNOS, ID_UNIDAD, CALIFICACION FROM calificaciones WHERE ID_MATERIAS = ?");
        $stmt->bind_param("i", $idMateria);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
            $notas[$c["ID_ALUMNOS"]][$c["ID_UNIDAD"]] = (float)$c["CALIFICACION"];
        }
        $stmt->close();
    }
}

// ---------- Promedios ----------
$promAlumno = [];
$promUnidad = [];
foreach ($filas as $f) {
    $valores = [];
    foreach ($unidades as $u) { $valores[] = $notas[$f["ID_ALUMNOS"]][$u["ID_UNIDAD"]] ?? null; }
    $promAlumno[$f["ID_ALUMNOS"]] = promedio($valores);
}
foreach ($unidades as $u) {
    $valores = [];
    foreach ($filas as $f) { $valores[] = $notas[$f["ID_ALUMNOS"]][$u["ID_UNIDAD"]] ?? null; }
    $promUnidad[$u["ID_UNIDAD"]] = promedio($valores);
}
$promGrupo = promedio(array_values($promAlumno));

// ---------- Descarga en Excel ----------
if (isset($_GET["descargar"]) && $materia !== null && $grupo !== null && count($filas) > 0) {
    $datos = [];
    $datos[] = ["Lista de calificaciones por materia y grupo"];
    $datos[] = ["Materia: " . $materia["CLAVE_MATERIA"] . " - " . $materia["NOMBRE_MATERIA"]];
    $datos[] = ["Grupo: " . $grupo["NOMBRE_GRUPO"]];
    $datos[] = [];

    $encabezado = ["NO.", "MATRICULA", "ALUMNO"];
    foreach ($unidades as $u) { $encabezado[] = "UNIDAD " . $u["ID_UNIDAD"]; }
    $encabezado[] = "PROMEDIO";
    $datos[] = $encabezado;

    foreach ($filas as $n => $f) {
        $fila = [$n + 1, $f["MATRICULA"], $f["NOMBRE_COMPLETO"]];
        foreach ($unidades as $u) { $fila[] = $notas[$f["ID_ALUMNOS"]][$u["ID_UNIDAD"]] ?? ""; }
        $fila[] = $promAlumno[$f["ID_ALUMNOS"]] ?? "";
        $datos[] = $fila;
    }

    $pie = ["", "", "PROMEDIO DEL GRUPO"];
    foreach ($unidades as $u) { $pie[] = $promUnidad[$u["ID_UNIDAD"]] ?? ""; }
    $pie[] = $promGrupo ?? "";
    $datos[] = $pie;

    enviarExcel($datos, "calificaciones_" . nombreArchivo($materia["CLAVE_MATERIA"]) . "_" . nombreArchivo($grupo["NOMBRE_GRUPO"]), 4, [0, 1, 2]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte: calificaciones por materia y grupo</title>
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
            <h2>Lista de calificaciones por materia y grupo</h2>
        </div>

        <div class="card-body">

            <form method="GET" class="row g-3 mb-4 no-imprimir">
                <div class="col-md-5">
                    <label class="form-label">Materia</label>
                    <select name="id_materia" class="form-select" required>
                        <option value="">Selecciona una materia...</option>
                        <?php foreach ($materias as $m) { ?>
                            <option value="<?= $m["ID_MATERIAS"] ?>" <?= $idMateria == $m["ID_MATERIAS"] ? "selected" : "" ?>>
                                <?= h($m["CLAVE_MATERIA"] . " - " . $m["NOMBRE_MATERIA"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Grupo</label>
                    <select name="id_grupo" class="form-select" required>
                        <option value="">Selecciona un grupo...</option>
                        <?php foreach ($grupos as $g) { ?>
                            <option value="<?= $g["ID_GRUPOS"] ?>" <?= $idGrupo == $g["ID_GRUPOS"] ? "selected" : "" ?>>
                                <?= h($g["NOMBRE_GRUPO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary">Ver lista</button>
                </div>
            </form>

            <?php if ($idMateria > 0 && $idGrupo === 0) { ?>
                <div class="alert alert-info no-imprimir">Elige un grupo para ver la lista de esa materia.</div>
            <?php } elseif ($idGrupo > 0 && $idMateria === 0) { ?>
                <div class="alert alert-info no-imprimir">Elige una materia para ver la lista de ese grupo.</div>
            <?php } ?>

            <?php if ($idMateria > 0 && $idGrupo > 0 && ($materia === null || $grupo === null)) { ?>
                <div class="alert alert-warning">No se encontró la materia o el grupo seleccionado.</div>
            <?php } ?>

            <?php if ($materia !== null && $grupo !== null) { ?>
                <hr class="no-imprimir">

                <div class="mb-3">
                    <h5 class="mb-1"><?= h($materia["CLAVE_MATERIA"] . " - " . $materia["NOMBRE_MATERIA"]) ?></h5>
                    <div class="text-muted">
                        Grupo: <?= h($grupo["NOMBRE_GRUPO"]) ?> &nbsp;|&nbsp;
                        Alumnos: <strong><?= count($filas) ?></strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th><th>Matrícula</th><th class="text-start">Alumno</th>
                                <?php foreach ($unidades as $u) { ?>
                                    <th title="<?= h($u["DESCRIPCION"]) ?>">Unidad <?= h($u["ID_UNIDAD"]) ?></th>
                                <?php } ?>
                                <th>Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($filas) === 0) { ?>
                                <tr><td colspan="<?= count($unidades) + 4 ?>" class="text-center text-muted">No hay alumnos de este grupo con esa materia asignada.</td></tr>
                            <?php } ?>
                            <?php foreach ($filas as $n => $f) { ?>
                                <tr>
                                    <td><?= $n + 1 ?></td>
                                    <td><?= h($f["MATRICULA"]) ?></td>
                                    <td class="text-start"><?= h($f["NOMBRE_COMPLETO"]) ?></td>
                                    <?php foreach ($unidades as $u) { ?>
                                        <td><?= fmt($notas[$f["ID_ALUMNOS"]][$u["ID_UNIDAD"]] ?? null) ?></td>
                                    <?php } ?>
                                    <th><?= fmt($promAlumno[$f["ID_ALUMNOS"]]) ?></th>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <?php if (count($filas) > 0) { ?>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="3" class="text-end">Promedio del grupo</th>
                                    <?php foreach ($unidades as $u) { ?>
                                        <th><?= fmt($promUnidad[$u["ID_UNIDAD"]]) ?></th>
                                    <?php } ?>
                                    <th><?= fmt($promGrupo) ?></th>
                                </tr>
                            </tfoot>
                        <?php } ?>
                    </table>
                </div>

                <?php if (count($filas) > 0) { ?>
                    <div class="no-imprimir">
                        <a href="?id_materia=<?= $idMateria ?>&id_grupo=<?= $idGrupo ?>&descargar=1" class="btn btn-success">Descargar Excel</a>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Imprimir</button>
                        <a href="<?= $base ?>/formularios/procesos/Captura_Calificaciones.php?modo=masiva&id_materia=<?= $idMateria ?>&id_grupo=<?= $idGrupo ?>" class="btn btn-outline-primary">Ir a captura de calificaciones</a>
                    </div>
                <?php } ?>
            <?php } ?>

        </div>

        <div class="card-footer text-center">
            Reporte de calificaciones - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
