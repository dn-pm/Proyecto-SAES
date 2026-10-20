<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE profesores SET ESTATUS = 'BAJA' WHERE ID_PROFESORES = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudprofesores.php");
    exit;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_PROFESORES AS idprof, CLAVE AS clave, NOMBRE AS nombre, APELLIDO_PATERNO AS apaterno, APELLIDO_MATERNO AS amaterno, CORREO AS correo, TELEFONO AS telefono FROM profesores WHERE ESTATUS = 'ALTA'");
$profesores = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Profesores</title>
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
    <h1 align="center">Catálogo de Profesores</h1>
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Profesores.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nuevo profesor
        </a>
    </div>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Clave</th>
                <th scope="col">Nombre</th>
                <th scope="col">Apellido Paterno</th>
                <th scope="col">Apellido Materno</th>
                <th scope="col">Email</th>
                <th scope="col">Teléfono</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php if (count($profesores) === 0) { ?>
            <tr>
                <td colspan="9" class="text-center text-muted">No hay profesores registrados.</td>
            </tr>
<?php } ?>
<?php foreach ($profesores as $profesor) { ?>
            <tr>
                <th scope="row"><?php echo $profesor["idprof"]; ?></th>
                <td><?php echo htmlspecialchars($profesor["clave"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["nombre"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["apaterno"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["amaterno"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["correo"]); ?></td>
                <td><?php echo htmlspecialchars($profesor["telefono"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Profesores.php?id=<?php echo $profesor["idprof"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudprofesores.php?baja=<?php echo $profesor["idprof"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este profesor?');">
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
