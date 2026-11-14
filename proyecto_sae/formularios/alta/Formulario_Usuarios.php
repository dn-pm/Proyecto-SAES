<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../config/seguridad.php";

$mostrarDatos = false;
$error = "";
$idNuevo = 0;
$nombreUser = "";
$idPuesto = 0;
$hash = "";

$puestos = $conexion->query("SELECT ID_PUESTO, DESCRIPCION_PUESTO FROM puesto WHERE STATUS_PUESTO = 'ALTA' ORDER BY DESCRIPCION_PUESTO")->fetch_all(MYSQLI_ASSOC);

if (isset($_POST["guardar"])) {
    $nombreUser = trim($_POST["nombreuser"]);
    $pw         = $_POST["pwuser"];
    $pw2        = $_POST["pwuser2"];
    $idPuesto   = (int)$_POST["puesto"];

    $nombrePuesto = "";
    foreach ($puestos as $p) {
        if ($p["ID_PUESTO"] == $idPuesto) { $nombrePuesto = $p["DESCRIPCION_PUESTO"]; }
    }

    if ($nombreUser === "") {
        $error = "Escribe el nombre de usuario.";
    } elseif ($nombrePuesto === "") {
        $error = "Selecciona un puesto válido.";
    } elseif (strlen($pw) < 4) {
        $error = "La contraseña debe tener al menos 4 caracteres.";
    } elseif ($pw !== $pw2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // La contraseña nunca se guarda tal cual: se guarda su hash
        $hash = encriptarPw($pw);

        try {
            $stmt = $conexion->prepare("INSERT INTO usuarios (NOMBREUSER, PWUSER, PUESTO) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nombreUser, $hash, $idPuesto);
            $stmt->execute();
            $idNuevo = $stmt->insert_id;
            $stmt->close();
            $mostrarDatos = true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Ya existe un usuario con ese nombre.";
            } else {
                $error = "Ocurrió un error al guardar: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . "/../../NavBar/navbar.php"; ?>

<div class="container mt-5">
    <div class="card shadow">

        <div class="card-header bg-primary text-white text-center">
            <h2>Registro de Usuarios</h2>
        </div>

        <div class="card-body">

            <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if (count($puestos) === 0) { ?>
                <div class="alert alert-warning">
                    Primero registra al menos un puesto en
                    <a href="<?= $base ?>/formularios/alta/Formulario_Puestos.php">Registro de Puestos</a>.
                </div>
            <?php } ?>

            <form method="POST" autocomplete="off">

                <div class="mb-3">
                    <label class="form-label">Nombre de usuario</label>
                    <input type="text" name="nombreuser" class="form-control" maxlength="50" value="<?php echo $mostrarDatos ? "" : htmlspecialchars($nombreUser); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="pwuser" class="form-control" minlength="4" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="pwuser2" class="form-control" minlength="4" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Puesto</label>
                    <select name="puesto" class="form-select" required>
                        <option value="">Selecciona un puesto...</option>
                        <?php foreach ($puestos as $p) { ?>
                            <option value="<?= $p["ID_PUESTO"] ?>" <?= (!$mostrarDatos && $idPuesto == $p["ID_PUESTO"]) ? "selected" : "" ?>>
                                <?= htmlspecialchars($p["DESCRIPCION_PUESTO"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" name="guardar" class="btn btn-primary">Registrar usuario</button>

            </form>

            <?php if ($mostrarDatos) { ?>

                <hr>

                <div class="alert alert-success mt-3">
                    Usuario guardado correctamente en la base de datos.
                </div>

                <h4 class="mb-3">Información del usuario</h4>

                <table class="table table-bordered">
                    <tr><th>Id de usuario</th><td><?php echo $idNuevo; ?></td></tr>
                    <tr><th>Usuario</th><td><?php echo htmlspecialchars($nombreUser); ?></td></tr>
                    <tr><th>Puesto</th><td><?php echo htmlspecialchars($nombrePuesto); ?></td></tr>
                    <tr><th>Estatus</th><td>ALTA</td></tr>
                    <tr>
                        <th>Contraseña encriptada (<?php echo nombreAlgoritmoPw(); ?>)</th>
                        <td><code><?php echo htmlspecialchars($hash); ?></code></td>
                    </tr>
                </table>
                <p class="text-muted small">La contraseña original no se guarda; en la base de datos solo queda este valor.</p>

            <?php } ?>

            <div class="text-center mt-3">
                <a href="<?= $base ?>/formularios/modificar/Modificar_Usuarios.php">Modificar un usuario existente</a>
                &nbsp;|&nbsp;
                <a href="<?= $base ?>/Cruds/crudusuarios.php">Ir al catálogo</a>
            </div>

        </div>

        <div class="card-footer text-center">
            Registro de usuarios - Programación II Patlan Medrano Daniel
        </div>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
