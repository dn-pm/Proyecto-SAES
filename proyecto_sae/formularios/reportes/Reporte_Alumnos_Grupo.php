<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/reportes_helper.php";
requerirRol(['ADMINISTRADOR', 'MAESTRO']);

$idGrupo = (int)($_GET["id_grupo"] ?? 0);
$soloMaestro = esMaestro();
$idProfesorSesion = $soloMaestro ? (int)$_SESSION["id_profesor"] : 0;

if ($soloMaestro) {
    $stmt = $conexion->prepare(
        "SELECT DISTINCT g.ID_GRUPOS, g.NOMBRE_GRUPO
         FROM grupos g
         INNER JOIN grupo_alumno ga ON ga.ID_GRUPOS = g.ID_GRUPOS AND ga.ESTATUS = 'ALTA'
         INNER JOIN asignatura s ON s.ID_ALUMNOS = ga.ID_ALUMNOS
         WHERE g.ESTATUS = 'ALTA' AND s.ID_PROFESORES = ?
         ORDER BY g.NOMBRE_GRUPO"
    );
    $stmt->bind_param("i", $idProfesorSesion);
    $stmt->execute();
    $grupos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $grupos = $conexion->query("SELECT ID_GRUPOS, NOMBRE_GRUPO FROM grupos WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_GRUPO")->fetch_all(MYSQLI_ASSOC);
}

$grupo = null;
$filas = [];

if ($idGrupo > 0) {
    $permitido = true;
    if ($soloMaestro) {
        $permitido = false;
        foreach ($grupos as $gItem) {
            if ((int)$gItem["ID_GRUPOS"] === $idGrupo) {
                $permitido = true;
                break;
            }
        }
    }

    if ($permitido) {
        $stmt = $conexion->prepare("SELECT ID_GRUPOS, NOMBRE_GRUPO, TURNO, SEMESTRE, DESCRIPCION FROM grupos WHERE ID_GRUPOS = ?");
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $grupo = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
    }

    if ($grupo !== null) {
        if ($soloMaestro) {
            $stmt = $conexion->prepare(
                "SELECT a.MATRICULA, a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE, a.CORREO, a.TELEFONO,
                        GROUP_CONCAT(DISTINCT m.NOMBRE_MATERIA ORDER BY m.NOMBRE_MATERIA SEPARATOR ', ') AS MATERIAS
                 FROM grupo_alumno ga
                 INNER JOIN alumnos a ON a.ID_ALUMNOS = ga.ID_ALUMNOS
                 INNER JOIN asignatura s ON s.ID_ALUMNOS = a.ID_ALUMNOS AND s.ID_PROFESORES = ?
                 INNER JOIN materias m ON m.ID_MATERIAS = s.ID_MATERIAS
                 WHERE ga.ID_GRUPOS = ? AND ga.ESTATUS = 'ALTA' AND a.ESTATUS = 'ALTA'
                 GROUP BY a.ID_ALUMNOS, a.MATRICULA, a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE, a.CORREO, a.TELEFONO
                 ORDER BY a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE"
            );
            $stmt->bind_param("ii", $idProfesorSesion, $idGrupo);
        } else {
            $stmt = $conexion->prepare(
                "SELECT a.MATRICULA, a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE, a.CORREO, a.TELEFONO
                 FROM grupo_alumno ga
                 INNER JOIN alumnos a ON a.ID_ALUMNOS = ga.ID_ALUMNOS
                 WHERE ga.ID_GRUPOS = ? AND ga.ESTATUS = 'ALTA' AND a.ESTATUS = 'ALTA'
                 ORDER BY a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE"
            );
            $stmt->bind_param("i", $idGrupo);
        }
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// ---------- Descarga en Excel ----------
if (isset($_GET["descargar"]) && $grupo !== null && count($filas) > 0) {
    $datos = [];
    $datos[] = ["Lista de alumnos por grupo"];
    $datos[] = ["Grupo: " . $grupo["NOMBRE_GRUPO"] . "   Turno: " . $grupo["TURNO"] . "   Semestre: " . $grupo["SEMESTRE"]];
    $datos[] = [];
    if ($soloMaestro) {
        $datos[] = ["NO.", "MATRICULA", "APELLIDO PATERNO", "APELLIDO MATERNO", "NOMBRE", "CORREO", "TELEFONO", "MATERIAS"];
        foreach ($filas as $n => $f) {
            $datos[] = [$n + 1, $f["MATRICULA"], $f["APELLIDO_PATERNO"], $f["APELLIDO_MATERNO"], $f["NOMBRE"], $f["CORREO"], $f["TELEFONO"], $f["MATERIAS"]];
        }
    } else {
        $datos[] = ["NO.", "MATRICULA", "APELLIDO PATERNO", "APELLIDO MATERNO", "NOMBRE", "CORREO", "TELEFONO"];
        foreach ($filas as $n => $f) {
            $datos[] = [$n + 1, $f["MATRICULA"], $f["APELLIDO_PATERNO"], $f["APELLIDO_MATERNO"], $f["NOMBRE"], $f["CORREO"], $f["TELEFONO"]];
        }
    }
    enviarExcel($datos, "alumnos_grupo_" . nombreArchivo($grupo["NOMBRE_GRUPO"]), 3, [0, 1]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $soloMaestro ? "Mis grupos" : "Reporte: alumnos por grupo" ?></title>
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
            <h2><?= $soloMaestro ? "Mis grupos y alumnos" : "Lista de alumnos por grupo" ?></h2>
        </div>

        <div class="card-body">

            <?php if ($soloMaestro && count($grupos) === 0) { ?>
                <div class="alert alert-info">Aún no tienes grupos con alumnos asignados.</div>
            <?php } ?>

            <form method="GET" class="row g-3 mb-4 no-imprimir">
                <div class="col-md-8">
                    <label class="form-label"><?= $soloMaestro ? "Mis grupos" : "Grupo" ?></label>
                    <select name="id_grupo" class="form-select" required>
                        <option value="">Selecciona un grupo...</option>
                        <?php foreach ($grupos as $g) { ?>
                            <option value="<?= $g["ID_GRUPOS"] ?>" <?= $idGrupo == $g["ID_GRUPOS"] ? "selected" : "" ?>>
                                <?= h($g["NOMBRE_GRUPO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary">Ver lista</button>
                </div>
            </form>

            <?php if ($idGrupo > 0 && $grupo === null) { ?>
                <div class="alert alert-warning">No tienes acceso a ese grupo o no se encontró.</div>
            <?php } ?>

            <?php if ($grupo !== null) { ?>
                <hr class="no-imprimir">

                <div class="mb-3">
                    <h5 class="mb-1">Grupo <?= h($grupo["NOMBRE_GRUPO"]) ?></h5>
                    <div class="text-muted">
                        Turno: <?= h($grupo["TURNO"]) ?> &nbsp;|&nbsp;
                        Semestre: <?= h($grupo["SEMESTRE"]) ?> &nbsp;|&nbsp;
                        Total de alumnos: <strong><?= count($filas) ?></strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th><th>Matrícula</th><th>Apellido paterno</th><th>Apellido materno</th>
                                <th>Nombre</th><th>Correo</th><th>Teléfono</th>
                                <?php if ($soloMaestro) { ?><th>Materias contigo</th><?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($filas) === 0) { ?>
                                <tr><td colspan="<?= $soloMaestro ? 8 : 7 ?>" class="text-center text-muted">Este grupo no tiene alumnos inscritos.</td></tr>
                            <?php } ?>
                            <?php foreach ($filas as $n => $f) { ?>
                                <tr>
                                    <td><?= $n + 1 ?></td>
                                    <td><?= h($f["MATRICULA"]) ?></td>
                                    <td><?= h($f["APELLIDO_PATERNO"]) ?></td>
                                    <td><?= h($f["APELLIDO_MATERNO"]) ?></td>
                                    <td><?= h($f["NOMBRE"]) ?></td>
                                    <td><?= h($f["CORREO"]) ?></td>
                                    <td><?= h($f["TELEFONO"]) ?></td>
                                    <?php if ($soloMaestro) { ?><td><?= h($f["MATERIAS"]) ?></td><?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <?php if (count($filas) > 0) { ?>
                    <div class="no-imprimir">
                        <a href="?id_grupo=<?= $idGrupo ?>&descargar=1" class="btn btn-success">Descargar Excel</a>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Imprimir</button>
                    </div>
                <?php } ?>
            <?php } ?>

        </div>

        <div class="card-footer text-center">
            Reporte de alumnos por grupo - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
