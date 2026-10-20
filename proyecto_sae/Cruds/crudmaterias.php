<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE materias SET ESTATUS = 'BAJA' WHERE ID_MATERIAS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudmaterias.php");
    exit;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_MATERIAS AS idmateria, CLAVE_MATERIA AS clavemateria, NOMBRE_MATERIA AS nombremateria, CREDITOS AS creditos, DESCRIPCION AS descripcion FROM materias WHERE ESTATUS = 'ALTA'");
$materias = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Materias</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/bootstrap-icons.css">
    <style>
        thead.thead-dark th { background-color: #0d6efd; color: #ffffff; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>

<?php include __DIR__ . "/../NavBar/navbar.php"; ?>

<div class="container">
    <br><br>
    <h1 align="center">Catálogo de Materias</h1>
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Materias.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nueva materia
        </a>
    </div>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Clave Materia</th>
                <th scope="col">Nombre Materia</th>
                <th scope="col">Créditos</th>
                <th scope="col">Descripción</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php if (count($materias) === 0) { ?>
            <tr>
                <td colspan="7" class="text-center text-muted">No hay materias registradas.</td>
            </tr>
<?php } ?>
<?php foreach ($materias as $materia) { ?>
            <tr>
                <th scope="row"><?php echo $materia["idmateria"]; ?></th>
                <td><?php echo htmlspecialchars($materia["clavemateria"]); ?></td>
                <td><?php echo htmlspecialchars($materia["nombremateria"]); ?></td>
                <td><?php echo htmlspecialchars($materia["creditos"]); ?></td>
                <td><?php echo htmlspecialchars($materia["descripcion"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Materias.php?id=<?php echo $materia["idmateria"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudmaterias.php?baja=<?php echo $materia["idmateria"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja esta materia?');">
                        <i class="bi bi-eject-fill"></i>
                    </a>
                </td>
            </tr>
<?php } ?>
        </tbody>
    </table>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
