<?php
$mysqli = include "conexion.php";
requerirRol(['ADMINISTRADOR']);

$aviso = "";

// ---------- BAJA ----------
if (isset($_GET["baja"])) {
    $id = (int) $_GET["baja"];
    if ($id === (int) $_SESSION["id_user"]) {
        $aviso = "No puedes dar de baja tu propio usuario mientras tienes la sesión iniciada.";
    } else {
        $stmt = $mysqli->prepare("UPDATE usuarios SET STATUS_USER = 'BAJA' WHERE ID_USER = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: crudusuarios.php");
        exit;
    }
}

// ---------- LISTADO ----------
$sql = "SELECT u.ID_USER, u.NOMBREUSER, p.DESCRIPCION_PUESTO, u.STATUS_USER,
               CONCAT(pr.CLAVE, ' - ', pr.NOMBRE, ' ', pr.APELLIDO_PATERNO) AS PROFESOR_VINCULADO,
               CONCAT(al.MATRICULA, ' - ', al.NOMBRE, ' ', al.APELLIDO_PATERNO) AS ALUMNO_VINCULADO
        FROM usuarios u
        INNER JOIN puesto p ON p.ID_PUESTO = u.PUESTO
        LEFT JOIN profesores pr ON pr.ID_PROFESORES = u.ID_PROFESORES
        LEFT JOIN alumnos al ON al.ID_ALUMNOS = u.ID_ALUMNOS
        WHERE u.STATUS_USER = 'ALTA'
        ORDER BY u.ID_USER";
$usuarios = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
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

    <?php if ($aviso !== "") { ?>
        <div class="alert alert-warning"><?= htmlspecialchars($aviso) ?></div>
    <?php } ?>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= $base ?>/formularios/alta/Formulario_Usuario.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Registrar nuevo usuario
        </a>
    </div>

    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Usuario</th>
                <th scope="col">Puesto / Jerarquía</th>
                <th scope="col">Persona Vinculada</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
<?php if (count($usuarios) === 0) { ?>
            <tr>
                <td colspan="6" class="text-center text-muted">No hay usuarios registrados.</td>
            </tr>
<?php } ?>
<?php foreach ($usuarios as $u) {
    $vinculo = "Acceso general administrativo";
    if (!empty($u["PROFESOR_VINCULADO"])) {
        $vinculo = "Profesor: " . $u["PROFESOR_VINCULADO"];
    } elseif (!empty($u["ALUMNO_VINCULADO"])) {
        $vinculo = "Alumno: " . $u["ALUMNO_VINCULADO"];
    }
?>
            <tr>
                <th scope="row"><?= (int)$u["ID_USER"] ?></th>
                <td><?= htmlspecialchars($u["NOMBREUSER"]) ?></td>
                <td><?= htmlspecialchars($u["DESCRIPCION_PUESTO"]) ?></td>
                <td><?= htmlspecialchars($vinculo) ?></td>
                <td>
                    <a href="<?= $base ?>/formularios/modificar/Modificar_Usuario.php?id=<?= (int)$u["ID_USER"] ?>" class="btn btn-sm btn-primary" title="Modificar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                </td>
                <td>
                    <?php if ((int)$u["ID_USER"] !== (int)$_SESSION["id_user"]) { ?>
                        <a href="crudusuarios.php?baja=<?= (int)$u["ID_USER"] ?>" class="btn btn-sm btn-secondary" title="Baja" onclick="return confirm('¿Dar de baja este usuario?');">
                            <i class="bi bi-eject-fill"></i>
                        </a>
                    <?php } ?>
                </td>
            </tr>
<?php } ?>
        </tbody>
    </table>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
