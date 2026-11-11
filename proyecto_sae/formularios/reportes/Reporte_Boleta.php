<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/reportes_helper.php";

date_default_timezone_set("America/Mexico_City");

$idAlumno = (int)($_GET["id_alumno"] ?? 0);

$alumnos  = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(APELLIDO_PATERNO, ' ', APELLIDO_MATERNO, ' ', NOMBRE) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, APELLIDO_MATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);
$unidades = $conexion->query("SELECT ID_UNIDAD, DESCRIPCION FROM unidad WHERE ESTATUS = 'ALTA' ORDER BY ID_UNIDAD")->fetch_all(MYSQLI_ASSOC);

$alumno = null;
$materias = [];
$notas = [];
$gruposAlumno = [];

if ($idAlumno > 0) {
    $stmt = $conexion->prepare("SELECT ID_ALUMNOS, MATRICULA, CONCAT(APELLIDO_PATERNO, ' ', APELLIDO_MATERNO, ' ', NOMBRE) AS NOMBRE_COMPLETO FROM alumnos WHERE ID_ALUMNOS = ?");
    $stmt->bind_param("i", $idAlumno);
    $stmt->execute();
    $alumno = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($alumno !== null) {
        // Materias asignadas al alumno
        $stmt = $conexion->prepare(
            "SELECT m.ID_MATERIAS, m.CLAVE_MATERIA, m.NOMBRE_MATERIA,
                    CONCAT(p.NOMBRE, ' ', p.APELLIDO_PATERNO) AS PROFESOR
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

        // Calificaciones: $notas[id_materia][id_unidad]
        $stmt = $conexion->prepare("SELECT ID_MATERIAS, ID_UNIDAD, CALIFICACION FROM calificaciones WHERE ID_ALUMNOS = ?");
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
            $notas[$c["ID_MATERIAS"]][$c["ID_UNIDAD"]] = (float)$c["CALIFICACION"];
        }
        $stmt->close();

        $stmt = $conexion->prepare("SELECT g.NOMBRE_GRUPO FROM grupo_alumno ga INNER JOIN grupos g ON g.ID_GRUPOS = ga.ID_GRUPOS WHERE ga.ID_ALUMNOS = ? AND ga.ESTATUS = 'ALTA' ORDER BY g.NOMBRE_GRUPO");
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $g) { $gruposAlumno[] = $g["NOMBRE_GRUPO"]; }
        $stmt->close();
    }
}

$textoGrupos = count($gruposAlumno) > 0 ? implode(", ", $gruposAlumno) : "Sin grupo";

// ---------- Promedios ----------
$promMateria = [];
foreach ($materias as $m) {
    $valores = [];
    foreach ($unidades as $u) { $valores[] = $notas[$m["ID_MATERIAS"]][$u["ID_UNIDAD"]] ?? null; }
    $promMateria[$m["ID_MATERIAS"]] = promedio($valores);
}
$promGeneral = promedio(array_values($promMateria));

// ---------- Descarga en Excel ----------
if (isset($_GET["descargar"]) && $alumno !== null && count($materias) > 0) {
    $datos = [];
    $datos[] = ["Boleta de calificaciones"];
    $datos[] = ["Alumno: " . $alumno["MATRICULA"] . " - " . $alumno["NOMBRE_COMPLETO"]];
    $datos[] = ["Grupo: " . $textoGrupos . "   Fecha de emision: " . date("d/m/Y")];
    $datos[] = [];

    $encabezado = ["CLAVE", "MATERIA", "PROFESOR"];
    foreach ($unidades as $u) { $encabezado[] = "UNIDAD " . $u["ID_UNIDAD"]; }
    $encabezado[] = "PROMEDIO";
    $datos[] = $encabezado;

    foreach ($materias as $m) {
        $fila = [$m["CLAVE_MATERIA"], $m["NOMBRE_MATERIA"], $m["PROFESOR"]];
        foreach ($unidades as $u) { $fila[] = $notas[$m["ID_MATERIAS"]][$u["ID_UNIDAD"]] ?? ""; }
        $fila[] = $promMateria[$m["ID_MATERIAS"]] ?? "";
        $datos[] = $fila;
    }

    $pie = ["", "", "PROMEDIO GENERAL"];
    foreach ($unidades as $u) { $pie[] = ""; }
    $pie[] = $promGeneral ?? "";
    $datos[] = $pie;

    enviarExcel($datos, "boleta_" . nombreArchivo($alumno["MATRICULA"]), 4, [0, 1, 2]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de calificaciones</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .firma { border-top: 1px solid #000000; padding-top: 4px; margin-top: 70px; }
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
            <h2>Boleta de calificaciones</h2>
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
                    <button type="submit" class="btn btn-secondary">Generar boleta</button>
                </div>
            </form>

            <?php if ($idAlumno > 0 && $alumno === null) { ?>
                <div class="alert alert-warning">No se encontró el alumno seleccionado.</div>
            <?php } ?>

            <?php if ($alumno !== null) { ?>
                <hr class="no-imprimir">

                <table class="table table-sm table-borderless w-auto mb-3">
                    <tr><th class="pe-3">Alumno</th><td><?= h($alumno["NOMBRE_COMPLETO"]) ?></td></tr>
                    <tr><th class="pe-3">Matrícula</th><td><?= h($alumno["MATRICULA"]) ?></td></tr>
                    <tr><th class="pe-3">Grupo</th><td><?= h($textoGrupos) ?></td></tr>
                    <tr><th class="pe-3">Fecha de emisión</th><td><?= date("d/m/Y") ?></td></tr>
                </table>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Clave</th><th class="text-start">Materia</th><th class="text-start">Profesor</th>
                                <?php foreach ($unidades as $u) { ?>
                                    <th title="<?= h($u["DESCRIPCION"]) ?>">U<?= h($u["ID_UNIDAD"]) ?></th>
                                <?php } ?>
                                <th>Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($materias) === 0) { ?>
                                <tr><td colspan="<?= count($unidades) + 4 ?>" class="text-center text-muted">Este alumno no tiene materias asignadas.</td></tr>
                            <?php } ?>
                            <?php foreach ($materias as $m) { ?>
                                <tr>
                                    <td><?= h($m["CLAVE_MATERIA"]) ?></td>
                                    <td class="text-start"><?= h($m["NOMBRE_MATERIA"]) ?></td>
                                    <td class="text-start"><?= h($m["PROFESOR"]) ?></td>
                                    <?php foreach ($unidades as $u) { ?>
                                        <td><?= fmt($notas[$m["ID_MATERIAS"]][$u["ID_UNIDAD"]] ?? null) ?></td>
                                    <?php } ?>
                                    <th><?= fmt($promMateria[$m["ID_MATERIAS"]]) ?></th>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <?php if (count($materias) > 0) { ?>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="<?= count($unidades) + 3 ?>" class="text-end">Promedio general</th>
                                    <th><?= fmt($promGeneral) ?></th>
                                </tr>
                            </tfoot>
                        <?php } ?>
                    </table>
                </div>

                <?php if (count($unidades) > 0) { ?>
                    <p class="small text-muted mb-0">
                        <?php foreach ($unidades as $u) { ?>
                            <span class="me-3">U<?= h($u["ID_UNIDAD"]) ?> = <?= h($u["DESCRIPCION"]) ?></span>
                        <?php } ?>
                    </p>
                <?php } ?>

                <div class="row text-center mt-4">
                    <div class="col-6"><div class="firma">Control escolar</div></div>
                    <div class="col-6"><div class="firma">Firma del alumno</div></div>
                </div>

                <?php if (count($materias) > 0) { ?>
                    <div class="no-imprimir mt-4">
                        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir boleta</button>
                        <a href="?id_alumno=<?= $idAlumno ?>&descargar=1" class="btn btn-success">Descargar Excel</a>
                    </div>
                <?php } ?>
            <?php } ?>

        </div>

        <div class="card-footer text-center">
            Boleta de calificaciones - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
