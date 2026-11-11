<?php
function h($t) { return htmlspecialchars((string)$t); }

// Promedio de los valores capturados (ignora los vacíos). Devuelve null si no hay ninguno.
function promedio(array $valores) {
    $v = [];
    foreach ($valores as $x) {
        if ($x !== null && $x !== "") { $v[] = (float)$x; }
    }
    if (count($v) === 0) { return null; }
    return round(array_sum($v) / count($v), 1);
}

// Número con un decimal, o una raya si no hay valor
function fmt($n) {
    return ($n === null || $n === "") ? "—" : number_format((float)$n, 1, ".", "");
}

// Texto seguro para usarlo como nombre de archivo
function nombreArchivo($t) {
    $t = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)$t);
    return trim($t, "_");
}


function letraColumna($i) {
    $letra = "";
    $i++;
    while ($i > 0) {
        $resto = ($i - 1) % 26;
        $letra = chr(65 + $resto) . $letra;
        $i = intdiv($i - 1, 26);
    }
    return $letra;
}

function crearXlsx(array $datos, $filaEncabezado = 0, array $filasTitulo = []) {
    $xml = function ($t) { return htmlspecialchars((string)$t, ENT_XML1 | ENT_QUOTES, "UTF-8"); };

    // Ancho de cada columna según su contenido (sin contar las filas de título)
    $anchos = [];
    foreach ($datos as $r => $fila) {
        if (in_array($r, $filasTitulo, true)) { continue; }
        foreach ($fila as $c => $valor) {
            $largo = preg_match_all('/./us', (string)$valor);
            if (!isset($anchos[$c]) || $largo > $anchos[$c]) { $anchos[$c] = $largo; }
        }
    }
    $colsXml = "";
    foreach ($anchos as $c => $largo) {
        $ancho = min(max($largo + 3, 9), 45);
        $colsXml .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . $ancho . '" customWidth="1"/>';
    }
    if ($colsXml !== "") { $colsXml = '<cols>' . $colsXml . '</cols>'; }

    $filasXml = "";
    foreach ($datos as $r => $fila) {
        $filasXml .= '<row r="' . ($r + 1) . '">';
        foreach ($fila as $c => $valor) {
            $ref = letraColumna($c) . ($r + 1);
            $estilo = "";
            if ($r === $filaEncabezado) {
                $estilo = ' s="1"';
            } elseif (in_array($r, $filasTitulo, true)) {
                $estilo = ' s="2"';
            }
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
          . $colsXml
          . '<sheetData>' . $filasXml . '</sheetData></worksheet>';

    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';

    $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';

    $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets></workbook>';

    $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>';

    // Estilos: 0 normal, 1 encabezado (azul con letras blancas), 2 título (negritas)
    $estilos = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="12"/><name val="Calibri"/></font></fonts>'
        . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF0D6EFD"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
        . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>';

    $tmp = tempnam(sys_get_temp_dir(), "xlsx");
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString("[Content_Types].xml", $contentTypes);
    $zip->addFromString("_rels/.rels", $rootRels);
    $zip->addFromString("xl/workbook.xml", $workbook);
    $zip->addFromString("xl/_rels/workbook.xml.rels", $workbookRels);
    $zip->addFromString("xl/styles.xml", $estilos);
    $zip->addFromString("xl/worksheets/sheet1.xml", $hoja);
    $zip->close();

    return $tmp;
}

function enviarExcel(array $datos, $nombre, $filaEncabezado = 0, array $filasTitulo = []) {
    if (ob_get_length()) { ob_clean(); }

    if (class_exists("ZipArchive")) {
        $archivo = crearXlsx($datos, $filaEncabezado, $filasTitulo);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment; filename="' . $nombre . '.xlsx"');
        header("Content-Length: " . filesize($archivo));
        readfile($archivo);
        unlink($archivo);
    } else {
        header("Content-Type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename="' . $nombre . '.csv"');
        echo "\xEF\xBB\xBF";
        $salida = fopen("php://output", "w");
        foreach ($datos as $fila) {
            fputcsv($salida, $fila, ",", '"', "");
        }
        fclose($salida);
    }
    exit;
}
