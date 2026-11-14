<?php
require_once __DIR__ . "/../../config/conexion.php";

function h($t) { return htmlspecialchars((string)$t); }

$modo  = (isset($_GET["modo"]) && $_GET["modo"] === "masiva") ? "masiva" : "individual";
$exito = "";
$error = "";

// ---------- Guardado (individual y masivo usan el mismo arreglo cal[ID_ALUMNO]) ----------
if (isset($_POST["guardar"])) {
    $idUnidadPost  = (int)$_POST["id_unidad"];
    $idMateriaPost = (int)$_POST["id_materia"];
    $guardadas = 0;
    $invalidas = 0;

    try {
        // INSERT IGNORE: si el alumno ya tiene calificación en esa materia y unidad, no se modifica.
        // El SELECT ... FROM asignatura solo inserta si el alumno tiene asignada la materia.
        $sql = "INSERT IGNORE INTO calificaciones (ID_ALUMNOS, ID_MATERIAS, CALIFICACION, ID_UNIDAD)
                SELECT ?, ?, ?, ? FROM asignatura WHERE ID_ALUMNOS = ? AND ID_MATERIAS = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);

        foreach ($_POST["cal"] ?? [] as $idAlumnoPost => $valor) {
            $valor = trim($valor);
            if ($valor === "") {
                continue;
            }
            if (!is_numeric($valor) || $valor < 0 || $valor > 10) {
                $invalidas++;
                continue;
            }
            $idAlumnoPost = (int)$idAlumnoPost;
            $calif = (float)$valor;
            $stmt->bind_param("iidiii", $idAlumnoPost, $idMateriaPost, $calif, $idUnidadPost, $idAlumnoPost, $idMateriaPost);
            $stmt->execute();
            $guardadas += $stmt->affected_rows;
        }
        $stmt->close();

        if ($guardadas > 0) {
            $exito = "Calificaciones guardadas: " . $guardadas . ".";
        }
        if ($invalidas > 0) {
            $error = "Hubo " . $invalidas . " calificación(es) fuera del rango 0 a 10 que no se guardaron.";
        }
        if ($guardadas === 0 && $invalidas === 0) {
            $error = "No se capturó ninguna calificación.";
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Ocurrió un error al guardar: " . $e->getMessage();
    }
}

// ---------- Corrección de una calificación ya capturada ----------
$correccionOk = false;
if (isset($_POST["corregir_guardar"])) {
    $idUnidadPost  = (int)$_POST["id_unidad"];
    $idMateriaPost = (int)$_POST["id_materia"];
    $idAlumnoPost  = (int)$_POST["id_alumno"];
    $valor = trim($_POST["nueva_cal"] ?? "");

    if ($valor === "" || !is_numeric($valor) || $valor < 0 || $valor > 10) {
        $error = "La calificación debe ser un número entre 0 y 10.";
    } else {
        try {
            $calif = (float)$valor;
            $stmt = $conexion->prepare("UPDATE calificaciones SET CALIFICACION = ? WHERE ID_ALUMNOS = ? AND ID_MATERIAS = ? AND ID_UNIDAD = ?");
            $stmt->bind_param("diii", $calif, $idAlumnoPost, $idMateriaPost, $idUnidadPost);
            $stmt->execute();
            $stmt->close();
            $exito = "Calificación corregida correctamente.";
            $correccionOk = true;
        } catch (mysqli_sql_exception $e) {
            $error = "Ocurrió un error al corregir: " . $e->getMessage();
        }
    }
}

// ---------- Datos para mostrar ----------
$corregir  = isset($_GET["corregir"]) && !$correccionOk;
$idUnidad  = (int)($_GET["id_unidad"] ?? 0);
$idMateria = (int)($_GET["id_materia"] ?? 0);
$idAlumno  = (int)($_GET["id_alumno"] ?? 0);
$idGrupo   = (int)($_GET["id_grupo"] ?? 0);

$unidades = $conexion->query("SELECT ID_UNIDAD, DESCRIPCION FROM unidad WHERE ESTATUS = 'ALTA' ORDER BY ID_UNIDAD")->fetch_all(MYSQLI_ASSOC);
$materias = $conexion->query("SELECT ID_MATERIAS, CLAVE_MATERIA, NOMBRE_MATERIA FROM materias WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_MATERIA")->fetch_all(MYSQLI_ASSOC);

$alumnos = [];
$grupos = [];
$asignada = false;
$calActual = null;
$filas = [];

if ($modo === "individual") {
    $alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

    if ($idAlumno > 0 && $idMateria > 0 && $idUnidad > 0) {
        // ¿El alumno tiene asignada esa materia?
        $stmt = $conexion->prepare("SELECT ID_ASIGNATURA FROM asignatura WHERE ID_ALUMNOS = ? AND ID_MATERIAS = ?");
        $stmt->bind_param("ii", $idAlumno, $idMateria);
        $stmt->execute();
        $asignada = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        if ($asignada) {
            $stmt = $conexion->prepare("SELECT CALIFICACION FROM calificaciones WHERE ID_ALUMNOS = ? AND ID_MATERIAS = ? AND ID_UNIDAD = ?");
            $stmt->bind_param("iii", $idAlumno, $idMateria, $idUnidad);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $calActual = $r ? $r["CALIFICACION"] : null;
        }
    }
} else {
    $grupos = $conexion->query("SELECT ID_GRUPOS, NOMBRE_GRUPO FROM grupos WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_GRUPO")->fetch_all(MYSQLI_ASSOC);

    if ($idMateria > 0 && $idUnidad > 0) {
        // Alumnos activos con esa materia asignada (y, si se eligió, de ese grupo)
        $joinGrupo = "";
        $tipos = "";
        $params = [];
        if ($idGrupo > 0) {
            $joinGrupo = "INNER JOIN grupo_alumno ga ON ga.ID_ALUMNOS = a.ID_ALUMNOS AND ga.ID_GRUPOS = ? AND ga.ESTATUS = 'ALTA'";
            $tipos .= "i";
            $params[] = $idGrupo;
        }
        $tipos .= "ii";
        $params[] = $idUnidad;
        $params[] = $idMateria;

        $sql = "SELECT a.ID_ALUMNOS, a.MATRICULA,
                       CONCAT(a.NOMBRE, ' ', a.APELLIDO_PATERNO, ' ', a.APELLIDO_MATERNO) AS NOMBRE_COMPLETO,
                       c.CALIFICACION
                FROM asignatura s
                INNER JOIN alumnos a ON a.ID_ALUMNOS = s.ID_ALUMNOS AND a.ESTATUS = 'ALTA'
                $joinGrupo
                LEFT JOIN calificaciones c ON c.ID_ALUMNOS = a.ID_ALUMNOS AND c.ID_MATERIAS = s.ID_MATERIAS AND c.ID_UNIDAD = ?
                WHERE s.ID_MATERIAS = ?
                ORDER BY a.APELLIDO_PATERNO, a.NOMBRE";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captura de calificaciones</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Captura de calificaciones</h2>
        </div>

        <div class="card-body">

            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $modo === 'individual' ? 'active' : '' ?>" href="?modo=individual">Individual</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $modo === 'masiva' ? 'active' : '' ?>" href="?modo=masiva">Masiva</a>
                </li>
            </ul>

            <?php if ($exito !== "") { ?>
                <div class="alert alert-success"><?php echo h($exito); ?></div>
            <?php } ?>
            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo h($error); ?></div>
            <?php } ?>

            <?php if (count($unidades) === 0) { ?>
                <div class="alert alert-warning">Primero registra al menos una unidad en Registros &gt; Unidades.</div>
            <?php } ?>

            <?php if ($modo === "individual") { ?>

                <!-- Selección de alumno, materia y unidad -->
                <form method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="modo" value="individual">
                    <div class="col-md-4">
                        <label class="form-label">Alumno</label>
                        <select name="id_alumno" class="form-select" required>
                            <option value="">Selecciona un alumno...</option>
                            <?php foreach ($alumnos as $a) { ?>
                                <option value="<?= $a["ID_ALUMNOS"] ?>" <?= $idAlumno == $a["ID_ALUMNOS"] ? "selected" : "" ?>>
                                    <?= h($a["ID_ALUMNOS"] . " - " . $a["MATRICULA"] . " - " . $a["NOMBRE_COMPLETO"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label class="form-label">Unidad</label>
                        <select name="id_unidad" class="form-select" required>
                            <option value="">Selecciona una unidad...</option>
                            <?php foreach ($unidades as $u) { ?>
                                <option value="<?= $u["ID_UNIDAD"] ?>" <?= $idUnidad == $u["ID_UNIDAD"] ? "selected" : "" ?>>
                                    <?= h($u["ID_UNIDAD"] . " - " . $u["DESCRIPCION"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary w-100">Seleccionar</button>
                    </div>
                </form>

                <?php if ($idAlumno > 0 && $idMateria > 0 && $idUnidad > 0 && !$asignada) { ?>
                    <div class="alert alert-warning">
                        Ese alumno no tiene asignada esa materia. Asígnala primero en Procesos &gt; Asignación de materias.
                    </div>
                <?php } ?>

                <?php if ($asignada) { ?>
                    <hr>
                    <form method="POST">
                        <input type="hidden" name="id_unidad" value="<?= $idUnidad ?>">
                        <input type="hidden" name="id_materia" value="<?= $idMateria ?>">
                        <input type="hidden" name="id_alumno" value="<?= $idAlumno ?>">

                        <div class="row g-3">
                            <?php if ($calActual === null) { ?>
                                <!-- Campo editable: solo aparece mientras no hay calificación -->
                                <div class="col-md-4">
                                    <label class="form-label">Calificación (capturar)</label>
                                    <input type="number" name="cal[<?= $idAlumno ?>]" class="form-control" min="0" max="10" step="0.1" required>
                                </div>
                            <?php } ?>

                            <!-- Campo no editable: queda con la calificación una vez guardada -->
                            <div class="col-md-4">
                                <label class="form-label">Calificación registrada</label>
                                <input type="text" class="form-control" value="<?= h($calActual) ?>" readonly>
                            </div>

                            <?php if ($calActual !== null && $corregir) { ?>
                                <!-- Campo de corrección: solo aparece al presionar "Corregir calificación" -->
                                <div class="col-md-4">
                                    <label class="form-label">Nueva calificación</label>
                                    <input type="number" name="nueva_cal" class="form-control" min="0" max="10" step="0.1" value="<?= h($calActual) ?>" required>
                                </div>
                            <?php } ?>
                        </div>

                        <?php $urlBase = "?modo=individual&id_alumno=" . $idAlumno . "&id_materia=" . $idMateria . "&id_unidad=" . $idUnidad; ?>
                        <?php if ($calActual === null) { ?>
                            <button type="submit" name="guardar" class="btn btn-primary mt-3">Guardar calificación</button>
                        <?php } elseif ($corregir) { ?>
                            <button type="submit" name="corregir_guardar" class="btn btn-warning mt-3">Guardar corrección</button>
                            <a href="<?= $urlBase ?>" class="btn btn-secondary mt-3">Cancelar</a>
                        <?php } else { ?>
                            <a href="<?= $urlBase ?>&corregir=1" class="btn btn-outline-warning mt-3">Corregir calificación</a>
                        <?php } ?>
                    </form>
                <?php } ?>

            <?php } else { ?>

                <!-- Selección de materia, unidad y grupo -->
                <form method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="modo" value="masiva">
                    <div class="col-md-4">
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
                    <div class="col-md-3">
                        <label class="form-label">Unidad</label>
                        <select name="id_unidad" class="form-select" required>
                            <option value="">Selecciona una unidad...</option>
                            <?php foreach ($unidades as $u) { ?>
                                <option value="<?= $u["ID_UNIDAD"] ?>" <?= $idUnidad == $u["ID_UNIDAD"] ? "selected" : "" ?>>
                                    <?= h($u["ID_UNIDAD"] . " - " . $u["DESCRIPCION"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grupo (opcional)</label>
                        <select name="id_grupo" class="form-select">
                            <option value="0">Todos los grupos</option>
                            <?php foreach ($grupos as $g) { ?>
                                <option value="<?= $g["ID_GRUPOS"] ?>" <?= $idGrupo == $g["ID_GRUPOS"] ? "selected" : "" ?>>
                                    <?= h($g["NOMBRE_GRUPO"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary w-100">Cargar alumnos</button>
                    </div>
                </form>

                <?php if ($idMateria > 0 && $idUnidad > 0) { ?>
                    <hr>
                    <form method="POST">
                        <input type="hidden" name="id_unidad" value="<?= $idUnidad ?>">
                        <input type="hidden" name="id_materia" value="<?= $idMateria ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Id alumno</th>
                                        <th>Matrícula</th>
                                        <th>Alumno</th>
                                        <th style="width:180px">Calificación (capturar)</th>
                                        <th style="width:180px">Calificación registrada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($filas) === 0) { ?>
                                        <tr><td colspan="5" class="text-center text-muted">No hay alumnos activos con esa materia asignada<?= $idGrupo > 0 ? " en ese grupo" : "" ?>.</td></tr>
                                    <?php } ?>
                                    <?php foreach ($filas as $f) { ?>
                                        <tr>
                                            <td><?= h($f["ID_ALUMNOS"]) ?></td>
                                            <td><?= h($f["MATRICULA"]) ?></td>
                                            <td><?= h($f["NOMBRE_COMPLETO"]) ?></td>
                                            <td>
                                                <?php if ($f["CALIFICACION"] === null) { ?>
                                                    <input type="number" name="cal[<?= $f["ID_ALUMNOS"] ?>]" class="form-control" min="0" max="10" step="0.1">
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" value="<?= h($f["CALIFICACION"]) ?>" readonly>
                                                <?php if ($f["CALIFICACION"] !== null) { ?>
                                                    <a class="small" href="?modo=individual&id_alumno=<?= $f["ID_ALUMNOS"] ?>&id_materia=<?= $idMateria ?>&id_unidad=<?= $idUnidad ?>&corregir=1">Corregir</a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" name="guardar" class="btn btn-primary">Guardar calificaciones</button>
                        <a href="<?= $base ?>/formularios/reportes/Reporte_Calificaciones.php?id_materia=<?= $idMateria ?>&id_grupo=<?= $idGrupo ?>" class="btn btn-outline-success">Enviar a Reportes</a>
                        <?php if ($idGrupo > 0) { ?>
                            <a href="<?= $base ?>/formularios/reportes/Reporte_Calificaciones.php?id_materia=<?= $idMateria ?>&id_grupo=<?= $idGrupo ?>&descargar=1" class="btn btn-success">Descargar Excel</a>
                        <?php } ?>
                        <div class="text-muted small mt-2">
                            Solo se guardan las filas que tengan calificación capturada. Guarda antes de enviar al reporte: el Excel incluye las calificaciones ya guardadas.
                            <?php if ($idGrupo === 0) { ?>Para descargar el Excel elige un grupo; el reporte es por materia y grupo.<?php } ?>
                        </div>
                    </form>
                <?php } ?>

            <?php } ?>

        </div>

        <div class="card-footer text-center">
            Captura de calificaciones - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
