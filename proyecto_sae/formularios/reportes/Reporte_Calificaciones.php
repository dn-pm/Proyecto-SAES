<?php
require_once __DIR__ . "/../../config/conexion.php";

function h($t) { return htmlspecialchars((string)$t); }

// =====================================================================
//  Genera un archivo .xlsx real
//  $datos es un arreglo de filas; la fila 0 son los encabezados.
//  Devuelve la ruta del archivo temporal.
// =====================================================================
function crearXlsx(array $datos) {
    $xml = function ($t) { return htmlspecialchars((string)$t, ENT_XML1 | ENT_QUOTES, "UTF-8"); };

    $filasXml = "";
    foreach ($datos as $r => $fila) {
        $filasXml .= '<row r="' . ($r + 1) . '">';
        foreach ($fila as $c => $valor) {
            $ref = chr(65 + $c) . ($r + 1);
            $estilo = ($r === 0) ? ' s="1"' : '';
            if ($valor === "" || $valor === null) {
                $filasXml .= '<c r="' . $ref . '"' . $estilo . '/>';
            } elseif (is_int($valor)) {
                $filasXml .= '<c r="' . $ref . '"' . $estilo . '><v>' . $valor . '</v></c>';
            } elseif (is_float($valor)) {
                $filasXml .= '<c r="' . $ref . '"' . $estilo . '><v>' . number_format($valor, 1, ".", "") . '</v></c>';
            } else {
                $filasXml .= '<c r="' . $ref . '"' . $estilo . ' t="inlineStr"><is><t>' . $xml($valor) . '</t></is></c>';
            }
        }
        $filasXml .= '</row>';
    }

    $hoja = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
          . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
          . '<cols><col min="1" max="1" width="12" customWidth="1"/><col min="2" max="2" width="15" customWidth="1"/><col min="3" max="3" width="42" customWidth="1"/><col min="4" max="4" width="10" customWidth="1"/><col min="5" max="5" width="28" customWidth="1"/><col min="6" max="6" width="14" customWidth="1"/></cols>'
          . '<sheetData>' . $filasXml . '</sheetData></worksheet>';

    $tmp = tempnam(sys_get_temp_dir(), "xlsx");
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString("[Content_Types].xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
    $zip->addFromString("_rels/.rels", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $zip->addFromString("xl/workbook.xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Calificaciones" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString("xl/_rels/workbook.xml.rels", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
    $zip->addFromString("xl/styles.xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0D6EFD"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
    $zip->addFromString("xl/worksheets/sheet1.xml", $hoja);
    $zip->close();

    return $tmp;
}

// ---------- Filtros (GET: solo consulta, no modifica nada) ----------
$idUnidad = (int)($_GET["id_unidad"] ?? 0);
$idGrupo  = (int)($_GET["id_grupo"] ?? 0);

$unidades = $conexion->query("SELECT ID_UNIDAD, DESCRIPCION FROM unidad WHERE ESTATUS = 'ALTA' ORDER BY ID_UNIDAD")->fetch_all(MYSQLI_ASSOC);
$grupos   = $conexion->query("SELECT ID_GRUPOS, NOMBRE_GRUPO FROM grupos WHERE ESTATUS = 'ALTA' ORDER BY NOMBRE_GRUPO")->fetch_all(MYSQLI_ASSOC);

$filas = [];
$nombreUnidad = "";
$nombreGrupo = "";

if ($idUnidad > 0) {

    foreach ($unidades as $u) {
        if ($u["ID_UNIDAD"] == $idUnidad) { $nombreUnidad = $u["DESCRIPCION"]; }
    }
    foreach ($grupos as $g) {
        if ($g["ID_GRUPOS"] == $idGrupo) { $nombreGrupo = $g["NOMBRE_GRUPO"]; }
    }

    // Todos los alumnos activos (o los de un grupo) con su calificación en la unidad, si ya la tienen
    $joinGrupo = "";
    $tipos = "i";
    $params = [$idUnidad];
    if ($idGrupo > 0) {
        $joinGrupo = "INNER JOIN grupo_alumno ga ON ga.ID_ALUMNOS = a.ID_ALUMNOS AND ga.ID_GRUPOS = ? AND ga.ESTATUS = 'ALTA'";
        $tipos = "ii";
        $params = [$idGrupo, $idUnidad];
    }

    $sql = "SELECT a.ID_ALUMNOS, a.MATRICULA,
                   CONCAT(a.APELLIDO_PATERNO, ' ', a.APELLIDO_MATERNO, ' ', a.NOMBRE) AS NOMBRE_COMPLETO,
                   c.CALIFICACION
            FROM alumnos a
            $joinGrupo
            LEFT JOIN calificaciones c ON c.ID_ALUMNOS = a.ID_ALUMNOS AND c.ID_UNIDAD = ?
            WHERE a.ESTATUS = 'ALTA'
            ORDER BY a.APELLIDO_PATERNO, a.APELLIDO_MATERNO, a.NOMBRE";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ---------- Descarga (se hace antes de imprimir cualquier HTML) ----------
$hayZip = class_exists("ZipArchive");

if (isset($_GET["descargar"]) && count($filas) > 0) {

    $datos = [["ID_ALUMNO", "MATRICULA", "ALUMNO", "ID_UNIDAD", "UNIDAD", "CALIFICACION"]];
    foreach ($filas as $f) {
        $datos[] = [
            (int)$f["ID_ALUMNOS"],
            $f["MATRICULA"],
            $f["NOMBRE_COMPLETO"],
            $idUnidad,
            $nombreUnidad,
            $f["CALIFICACION"] === null ? "" : (float)$f["CALIFICACION"]
        ];
    }

    $nombreArchivo = "calificaciones_unidad_" . $idUnidad . ($idGrupo > 0 ? "_grupo_" . $idGrupo : "");

    if (ob_get_length()) { ob_clean(); }

    if ($hayZip) {
        $archivo = crearXlsx($datos);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xlsx"');
        header("Content-Length: " . filesize($archivo));
        readfile($archivo);
        unlink($archivo);
    } else {
        // Plan B si la extensión zip no está activa: CSV (Excel también lo abre)
        header("Content-Type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
        echo "\xEF\xBB\xBF";
        $salida = fopen("php://output", "w");
        foreach ($datos as $fila) {
            fputcsv($salida, $fila, ",", '"', "");
        }
        fclose($salida);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de calificaciones</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Reporte de calificaciones</h2>
        </div>

        <div class="card-body">

            <?php if (!$hayZip) { ?>
                <div class="alert alert-warning">La extensión zip de PHP no está activa, así que la descarga saldrá en CSV en lugar de .xlsx.</div>
            <?php } ?>

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-5">
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
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary">Ver lista</button>
                    <button type="submit" name="descargar" value="1" class="btn btn-success">Descargar Excel</button>
                </div>
            </form>

            <?php if ($idUnidad > 0) { ?>
                <hr>
                <h5 class="mb-3">
                    <?= h($nombreUnidad) ?>
                    <?php if ($nombreGrupo !== "") { ?> - Grupo <?= h($nombreGrupo) ?><?php } ?>
                </h5>
                <p>
                    <a href="<?= $base ?>/formularios/procesos/Captura_Calificaciones.php?modo=masiva&id_unidad=<?= $idUnidad ?>">&larr; Regresar a la captura masiva</a>
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr><th>Id alumno</th><th>Matrícula</th><th>Alumno</th><th>Calificación</th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($filas) === 0) { ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay alumnos para mostrar.</td></tr>
                            <?php } ?>
                            <?php foreach ($filas as $f) { ?>
                                <tr>
                                    <td><?= h($f["ID_ALUMNOS"]) ?></td>
                                    <td><?= h($f["MATRICULA"]) ?></td>
                                    <td><?= h($f["NOMBRE_COMPLETO"]) ?></td>
                                    <td>
                                        <?php if ($f["CALIFICACION"] === null) { ?>
                                            <span class="text-muted">Pendiente</span>
                                        <?php } else { ?>
                                            <?= h($f["CALIFICACION"]) ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
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
