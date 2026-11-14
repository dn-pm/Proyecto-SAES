<?php

require_once "../../config/conexion.php";
require_once "../../config/seguridad.php";

$mensaje = "";
$error = "";
$usuario = null;

$puestos = $conexion->query("SELECT ID_PUESTO, DESCRIPCION_PUESTO, STATUS_PUESTO FROM puesto ORDER BY DESCRIPCION_PUESTO")->fetch_all(MYSQLI_ASSOC);

if (isset($_POST["actualizar"])) {

    $id         = (int) $_POST["id"];
    $nombreUser = trim($_POST["nombreuser"]);
    $idPuesto   = (int) $_POST["puesto"];
    $estatus    = $_POST["estatus"];
    $pw         = $_POST["pwuser"];
    $pw2        = $_POST["pwuser2"];

    if ($nombreUser === "") {
        $error = "Escribe el nombre de usuario.";
    } elseif ($pw !== "" && strlen($pw) < 4) {
        $error = "La nueva contraseña debe tener al menos 4 caracteres.";
    } elseif ($pw !== $pw2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            if ($pw !== "") {
                // Contraseña nueva: se guarda su hash
                $hash = encriptarPw($pw);
                $sql = "UPDATE usuarios SET NOMBREUSER = ?, PWUSER = ?, PUESTO = ?, STATUS_USER = ? WHERE ID_USER = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssisi", $nombreUser, $hash, $idPuesto, $estatus, $id);
            } else {
                // Sin contraseña nueva: se conserva la que ya tenía
                $sql = "UPDATE usuarios SET NOMBREUSER = ?, PUESTO = ?, STATUS_USER = ? WHERE ID_USER = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("sisi", $nombreUser, $idPuesto, $estatus, $id);
            }
            $stmt->execute();
            $stmt->close();
            $mensaje = $pw !== "" ? "Usuario actualizado y contraseña cambiada correctamente." : "Usuario actualizado correctamente.";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Ya existe otro usuario con ese nombre.";
            } else {
                $error = "Ocurrió un error al actualizar: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE ID_USER = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$listaUsuarios = null;

if ($usuario === null) {
    $listaUsuarios = $conexion->query("SELECT u.ID_USER, u.NOMBREUSER, u.STATUS_USER, p.DESCRIPCION_PUESTO FROM usuarios u INNER JOIN puesto p ON p.ID_PUESTO = u.PUESTO ORDER BY u.ID_USER");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuarios</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Modificar Usuarios</h2>
        </div>

        <div class="card-body">

            <?php if ($mensaje !== "") { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if ($usuario !== null) { ?>

                <form method="POST" autocomplete="off">

                    <input type="hidden" name="id" value="<?php echo $usuario["ID_USER"]; ?>">

                    <div class="mb-3">
                        <label class="form-label">Id de usuario</label>
                        <input type="text" class="form-control" value="<?php echo $usuario["ID_USER"]; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" name="nombreuser" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($usuario["NOMBREUSER"]); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="pwuser" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="pwuser2" class="form-control">
                        </div>
                        <div class="col-12 mb-3 text-muted small">Déjalas vacías si no quieres cambiar la contraseña.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Puesto</label>
                        <select name="puesto" class="form-control" required>
                            <?php foreach ($puestos as $p) { ?>
                                <option value="<?= $p["ID_PUESTO"] ?>" <?= $usuario["PUESTO"] == $p["ID_PUESTO"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($p["DESCRIPCION_PUESTO"]) ?><?= $p["STATUS_PUESTO"] !== "ALTA" ? " (BAJA)" : "" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <option value="ALTA" <?php if ($usuario["STATUS_USER"] == "ALTA") echo "selected"; ?>>ALTA</option>
                            <option value="BAJA" <?php if ($usuario["STATUS_USER"] == "BAJA") echo "selected"; ?>>BAJA</option>
                        </select>
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary">Guardar cambios</button>
                    <a href="<?= $base ?>/Cruds/crudusuarios.php" class="btn btn-secondary">Cancelar</a>

                </form>

            <?php } else { ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Puesto</th>
                            <th>Estatus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaUsuarios && $listaUsuarios->num_rows > 0) { ?>
                            <?php while ($fila = $listaUsuarios->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila["ID_USER"]; ?></td>
                                    <td><?php echo htmlspecialchars($fila["NOMBREUSER"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["DESCRIPCION_PUESTO"]); ?></td>
                                    <td><?php echo htmlspecialchars($fila["STATUS_USER"]); ?></td>
                                    <td><a href="?id=<?php echo $fila["ID_USER"]; ?>" class="btn btn-sm btn-primary">Editar</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay usuarios registrados todavía.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="../alta/Formulario_Usuarios.php">Registrar nuevo usuario</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudusuarios.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Modificación de usuarios - Programación II Patlan Medrano Daniel
        </div>

    </div>

</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>
