<?php
$mysqli = include "conexion.php";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    $stmt = $mysqli->prepare("UPDATE usuarios SET STATUS_USER = 'BAJA' WHERE ID_USER = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: crudusuarios.php");
    exit;
}

// ---------- LISTADO ----------
$resultado = $mysqli->query("SELECT u.ID_USER AS iduser, u.NOMBREUSER AS nombreuser, p.DESCRIPCION_PUESTO AS puesto FROM usuarios u INNER JOIN puesto p ON p.ID_PUESTO = u.PUESTO WHERE u.STATUS_USER = 'ALTA' ORDER BY u.NOMBREUSER");
$usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Usuarios</title>
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
    <h1 align="center">Catálogo de Usuarios</h1>
    <br>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Usuarios.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nuevo usuario
        </a>
    </div>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Usuario</th>
                <th scope="col">Puesto</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php if (count($usuarios) === 0) { ?>
            <tr>
                <td colspan="5" class="text-center text-muted">No hay usuarios registrados.</td>
            </tr>
<?php } ?>
<?php foreach ($usuarios as $usuario) { ?>
            <tr>
                <th scope="row"><?php echo $usuario["iduser"]; ?></th>
                <td><?php echo htmlspecialchars($usuario["nombreuser"]); ?></td>
                <td><?php echo htmlspecialchars($usuario["puesto"]); ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Usuarios.php?id=<?php echo $usuario["iduser"]; ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <a href="crudusuarios.php?baja=<?php echo $usuario["iduser"]; ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este usuario?');">
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
