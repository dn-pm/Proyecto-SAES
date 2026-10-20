<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE alumnos SET ESTATUS = 'BAJA' WHERE ID_ALUMNOS = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudalumnos.php");
    exit;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT ID_ALUMNOS AS idalum, MATRICULA AS matricula, NOMBRE AS nombre, APELLIDO_PATERNO AS apaterno, APELLIDO_MATERNO AS amaterno, DOMICILIO AS domicilio, CORREO AS correo, TELEFONO AS telefono FROM alumnos WHERE ESTATUS = 'ALTA'");
$alumnos = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Alumnos</title>
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
    <h1 align="center">Catálogo de Alumnos</h1>
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Alumnos.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nuevo alumno
        </a>
    </div>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Matrícula</th>
                <th scope="col">Nombre</th>
                <th scope="col">Apellido Paterno</th>
                <th scope="col">Apellido Materno</th>
                <th scope="col">Domicilio</th>
                <th scope="col">Email</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php if (count($alumnos) === 0) { ?>
            <tr>
                <td colspan="9" class="text-center text-muted">No hay alumnos registrados.</td>
            </tr>
<?php } ?>
<?php foreach ($alumnos as $alumno) { ?>
            <tr>
                <th scope="row"><?php echo $alumno["idalum"]; ?></th>
                <td><?php echo htmlspecialchars($alumno["matricula"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["nombre"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["apaterno"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["amaterno"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["domicilio"]); ?></td>
                <td><?php echo htmlspecialchars($alumno["correo"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Alumnos.php?id=<?php echo $alumno["idalum"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudalumnos.php?baja=<?php echo $alumno["idalum"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este alumno?');">
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
