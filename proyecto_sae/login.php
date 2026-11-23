<?php
require_once __DIR__ . "/config/conexion.php";

if (isset($_SESSION['id_user'])) {
    header("Location: " . $base . "/principal.php");
    exit;
}

$error = "";
$aviso = isset($_GET['cerrada']) ? "Sesión cerrada correctamente." : "";
$usuarioIngresado = "";

if (isset($_POST['ingresar'])) {
    $usuarioIngresado = trim($_POST['nombreuser'] ?? '');
    $passwordIngresado = $_POST['pwuser'] ?? '';

    if ($usuarioIngresado === '' || $passwordIngresado === '') {
        $error = "Ingresa tu usuario y contraseña.";
    } else {
        $sql = "SELECT u.ID_USER, u.NOMBREUSER, u.PWUSER, u.PUESTO, u.ID_PROFESORES, u.ID_ALUMNOS, u.STATUS_USER,
                       p.DESCRIPCION_PUESTO, p.STATUS_PUESTO,
                       CONCAT(pr.NOMBRE, ' ', pr.APELLIDO_PATERNO, ' ', pr.APELLIDO_MATERNO) AS NOMBRE_PROFESOR,
                       pr.ESTATUS AS PROF_ESTATUS,
                       CONCAT(al.NOMBRE, ' ', al.APELLIDO_PATERNO, ' ', al.APELLIDO_MATERNO) AS NOMBRE_ALUMNO,
                       al.MATRICULA AS ALUM_MATRICULA,
                       al.ESTATUS AS ALUM_ESTATUS
                FROM usuarios u
                INNER JOIN puesto p ON p.ID_PUESTO = u.PUESTO
                LEFT JOIN profesores pr ON pr.ID_PROFESORES = u.ID_PROFESORES
                LEFT JOIN alumnos al ON al.ID_ALUMNOS = u.ID_ALUMNOS
                WHERE u.NOMBREUSER = ?
                LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $usuarioIngresado);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $hash = $row['PWUSER'] ?? '';
        $passValida = false;
        if ($row !== null) {
            if (strlen($hash) === 40 && hash_equals(strtolower($hash), sha1($passwordIngresado))) {
                $passValida = true;
            } elseif (strlen($hash) === 32 && hash_equals(strtolower($hash), md5($passwordIngresado))) {
                $passValida = true;
            }
        }

        if ($row === null || !$passValida) {
            $error = "Usuario o contraseña incorrectos.";
        } elseif ($row['STATUS_USER'] !== 'ALTA' || $row['STATUS_PUESTO'] !== 'ALTA') {
            $error = "Tu usuario o perfil se encuentra dado de baja. Contacta al administrador.";
        } else {
            $descPuesto = mb_strtoupper(trim($row['DESCRIPCION_PUESTO']), 'UTF-8');
            $rol = '';
            if ((int)$row['PUESTO'] === 1 || str_contains($descPuesto, 'ADMIN')) {
                $rol = 'ADMINISTRADOR';
            } elseif ((int)$row['PUESTO'] === 2 || str_contains($descPuesto, 'MAESTRO') || str_contains($descPuesto, 'PROFESOR')) {
                $rol = 'MAESTRO';
            } elseif ((int)$row['PUESTO'] === 3 || str_contains($descPuesto, 'ALUMNO')) {
                $rol = 'ALUMNO';
            }

            if ($rol === '') {
                $error = "El puesto asignado a este usuario no tiene un rol válido.";
            } elseif ($rol === 'MAESTRO' && (empty($row['ID_PROFESORES']) || $row['PROF_ESTATUS'] !== 'ALTA')) {
                $error = "Esta cuenta de maestro no está vinculada a un profesor activo.";
            } elseif ($rol === 'ALUMNO' && (empty($row['ID_ALUMNOS']) || $row['ALUM_ESTATUS'] !== 'ALTA')) {
                $error = "Esta cuenta de alumno no está vinculada a un alumno activo.";
            } else {
                session_regenerate_id(true);
                $_SESSION['id_user']     = (int)$row['ID_USER'];
                $_SESSION['nombre_user'] = $row['NOMBREUSER'];
                $_SESSION['id_puesto']   = (int)$row['PUESTO'];
                $_SESSION['puesto_desc'] = $row['DESCRIPCION_PUESTO'];
                $_SESSION['rol']         = $rol;
                $_SESSION['id_profesor'] = $row['ID_PROFESORES'] ? (int)$row['ID_PROFESORES'] : null;
                $_SESSION['id_alumno']   = $row['ID_ALUMNOS'] ? (int)$row['ID_ALUMNOS'] : null;

                if ($rol === 'MAESTRO') {
                    $_SESSION['nombre_mostrar'] = trim($row['NOMBRE_PROFESOR']);
                } elseif ($rol === 'ALUMNO') {
                    $_SESSION['nombre_mostrar'] = trim($row['NOMBRE_ALUMNO']) . " (" . $row['ALUM_MATRICULA'] . ")";
                } else {
                    $_SESSION['nombre_mostrar'] = $row['NOMBREUSER'];
                }

                header("Location: " . $base . "/principal.php");
                exit;
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
    <title>Iniciar Sesión - Proyecto SAE</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-mortarboard-fill"></i> Proyecto SAE</h4>
                    <small>Sistema de Administración Escolar</small>
                </div>

                <div class="card-body p-4">
                    <h5 class="text-center mb-3">Inicio de sesión</h5>

                    <?php if ($aviso !== "") { ?>
                        <div class="alert alert-info py-2"><?= htmlspecialchars($aviso) ?></div>
                    <?php } ?>

                    <?php if ($error !== "") { ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                    <?php } ?>

                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" name="nombreuser" class="form-control" maxlength="50"
                                       value="<?= htmlspecialchars($usuarioIngresado) ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="pwuser" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" name="ingresar" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </button>
                    </form>
                </div>

                <div class="card-footer text-center text-muted small">
                    Programación II - Patlan Medrano Daniel
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
