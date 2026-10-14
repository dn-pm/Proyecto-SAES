<?php
require_once __DIR__ . "/../../config/conexion.php";

function h($t) { return htmlspecialchars((string)$t); }

$modo  = (isset($_GET["modo"]) && $_GET["modo"] === "masiva") ? "masiva" : "individual";
$exito = "";
$error = "";

// ---------- Guardado (individual y masivo usan el mismo arreglo cal[ID_ALUMNO]) ----------
if (isset($_POST["guardar"])) {
    $idUnidadPost = (int)$_POST["id_unidad"];
    $guardadas = 0;
    $invalidas = 0;

    try {
        // INSERT IGNORE: si el alumno ya tiene calificación en esa unidad, no se modifica
        $sql = "INSERT IGNORE INTO calificaciones (ID_ALUMNOS, CALIFICACION, ID_UNIDAD) VALUES (?, ?, ?)";
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
            $stmt->bind_param("idi", $idAlumnoPost, $calif, $idUnidadPost);
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

// ---------- Datos para mostrar ----------
$idUnidad = (int)($_GET["id_unidad"] ?? 0);
$idAlumno = (int)($_GET["id_alumno"] ?? 0);

$unidades = $conexion->query("SELECT ID_UNIDAD, DESCRIPCION FROM unidad ORDER BY ID_UNIDAD")->fetch_all(MYSQLI_ASSOC);

$alumnos = [];
$calActual = null;
$filas = [];

if ($modo === "individual") {
    $alumnos = $conexion->query("SELECT ID_ALUMNOS, MATRICULA, CONCAT(NOMBRE, ' ', APELLIDO_PATERNO, ' ', APELLIDO_MATERNO) AS NOMBRE_COMPLETO FROM alumnos WHERE ESTATUS = 'ALTA' ORDER BY APELLIDO_PATERNO, NOMBRE")->fetch_all(MYSQLI_ASSOC);

    if ($idAlumno > 0 && $idUnidad > 0) {
        $stmt = $conexion->prepare("SELECT CALIFICACION FROM calificaciones WHERE ID_ALUMNOS = ? AND ID_UNIDAD = ?");
        $stmt->bind_param("ii", $idAlumno, $idUnidad);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $calActual = $r ? $r["CALIFICACION"] : null;
    }
} elseif ($idUnidad > 0) {
    $stmt = $conexion->prepare(
        "SELECT a.ID_ALUMNOS, a.MATRICULA,
                CONCAT(a.NOMBRE, ' ', a.APELLIDO_PATERNO, ' ', a.APELLIDO_MATERNO) AS NOMBRE_COMPLETO,
                c.CALIFICACION
         FROM alumnos a
         LEFT JOIN calificaciones c ON c.ID_ALUMNOS = a.ID_ALUMNOS AND c.ID_UNIDAD = ?
         WHERE a.ESTATUS = 'ALTA'
         ORDER BY a.APELLIDO_PATERNO, a.NOMBRE"
    );
    $stmt->bind_param("i", $idUnidad);
    $stmt->execute();
    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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

                <!-- Selección de alumno y unidad -->
                <form method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="modo" value="individual">
                    <div class="col-md-6">
                        <label class="form-label">Id alumno</label>
                        <select name="id_alumno" class="form-select" required>
                            <option value="">Selecciona un alumno...</option>
                            <?php foreach ($alumnos as $a) { ?>
                                <option value="<?= $a["ID_ALUMNOS"] ?>" <?= $idAlumno == $a["ID_ALUMNOS"] ? "selected" : "" ?>>
                                    <?= h($a["ID_ALUMNOS"] . " - " . $a["MATRICULA"] . " - " . $a["NOMBRE_COMPLETO"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Id unidad</label>
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

                <?php if ($idAlumno > 0 && $idUnidad > 0) { ?>
                    <hr>
                    <form method="POST">
                        <input type="hidden" name="id_unidad" value="<?= $idUnidad ?>">

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
                        </div>

                        <?php if ($calActual === null) { ?>
                            <button type="submit" name="guardar" class="btn btn-primary mt-3">Guardar calificación</button>
                        <?php } else { ?>
                            <p class="text-muted small mt-3 mb-0">Esta calificación ya fue registrada y no se puede editar.</p>
                        <?php } ?>
                    </form>
                <?php } ?>

            <?php } else { ?>

                <!-- Selección de unidad -->
                <form method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="modo" value="masiva">
                    <div class="col-md-8">
                        <label class="form-label">Id unidad</label>
                        <select name="id_unidad" class="form-select" required>
                            <option value="">Selecciona una unidad...</option>
                            <?php foreach ($unidades as $u) { ?>
                                <option value="<?= $u["ID_UNIDAD"] ?>" <?= $idUnidad == $u["ID_UNIDAD"] ? "selected" : "" ?>>
                                    <?= h($u["ID_UNIDAD"] . " - " . $u["DESCRIPCION"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary w-100">Cargar alumnos</button>
                    </div>
                </form>

                <?php if ($idUnidad > 0) { ?>
                    <hr>
                    <form method="POST">
                        <input type="hidden" name="id_unidad" value="<?= $idUnidad ?>">

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
                                        <tr><td colspan="5" class="text-center text-muted">No hay alumnos activos.</td></tr>
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
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" name="guardar" class="btn btn-primary">Guardar calificaciones</button>
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
