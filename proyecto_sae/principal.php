<?php
require_once __DIR__ . "/config/auth.php";
requerirLogin();

$nombreMostrar = htmlspecialchars($_SESSION['nombre_mostrar'] ?? $_SESSION['nombre_user']);
$puestoDesc    = htmlspecialchars($_SESSION['puesto_desc'] ?? $_SESSION['rol']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto SAE</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include __DIR__ . "/NavBar/navbar.php"; ?>

<div class="container mt-5">

    <?php if (isset($_GET['denegado'])) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-shield-lock-fill"></i>
            <strong>Acceso denegado:</strong> tu perfil (<?= $puestoDesc ?>) no tiene permisos para entrar a ese módulo.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="text-center mb-5">
        <h1>Sistema de Administración Escolar</h1>
        <p class="text-muted mb-1">Bienvenido, <strong><?= $nombreMostrar ?></strong></p>
        <span class="badge bg-secondary">Jerarquía: <?= $puestoDesc ?></span>
    </div>

    <div class="row g-4 justify-content-center">

        <?php if (esAdmin()) { ?>
            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-plus"></i> Registros</h5>
                        <p class="card-text">Catálogos de alumnos, profesores, grupos, materias, unidades y usuarios.</p>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="<?= $base ?>/Cruds/crudalumnos.php" class="btn btn-primary btn-sm">Alumnos</a>
                            <a href="<?= $base ?>/Cruds/crudprofesores.php" class="btn btn-primary btn-sm">Profesores</a>
                            <a href="<?= $base ?>/Cruds/crudgrupos.php" class="btn btn-primary btn-sm">Grupos</a>
                            <a href="<?= $base ?>/Cruds/crudmaterias.php" class="btn btn-primary btn-sm">Materias</a>
                            <a href="<?= $base ?>/Cruds/crudunidades.php" class="btn btn-primary btn-sm">Unidades</a>
                            <a href="<?= $base ?>/Cruds/crudusuarios.php" class="btn btn-dark btn-sm">Usuarios</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-diagram-3"></i> Procesos</h5>
                        <p class="card-text">Inscripción de alumnos a grupos y asignación de materias.</p>
                        <a href="<?= $base ?>/formularios/procesos/Inscripcion_Grupos.php" class="btn btn-primary">Inscripción</a>
                        <a href="<?= $base ?>/formularios/procesos/Asignacion_Materias.php" class="btn btn-outline-primary">Asignación</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-journal-check"></i> Calificaciones</h5>
                        <p class="card-text">Captura individual y masiva por unidad, con corrección.</p>
                        <a href="<?= $base ?>/formularios/procesos/Captura_Calificaciones.php" class="btn btn-primary">Capturar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-file-earmark-spreadsheet"></i> Reportes</h5>
                        <p class="card-text">Alumnos por grupo, materias por alumno, calificaciones por materia y grupo, y boleta.</p>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="<?= $base ?>/formularios/reportes/Reporte_Alumnos_Grupo.php" class="btn btn-primary btn-sm">Alumnos por grupo</a>
                            <a href="<?= $base ?>/formularios/reportes/Reporte_Materias_Alumno.php" class="btn btn-primary btn-sm">Materias por alumno</a>
                            <a href="<?= $base ?>/formularios/reportes/Reporte_Calificaciones.php" class="btn btn-primary btn-sm">Calificaciones</a>
                            <a href="<?= $base ?>/formularios/reportes/Reporte_Boleta.php" class="btn btn-primary btn-sm">Boleta</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php } elseif (esMaestro()) { ?>
            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people-fill"></i> Mis Grupos</h5>
                        <p class="card-text">Consulta la lista de alumnos inscritos en los grupos donde impartes clase.</p>
                        <a href="<?= $base ?>/formularios/reportes/Reporte_Alumnos_Grupo.php" class="btn btn-primary">Ver mis grupos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-journal-check"></i> Calificaciones</h5>
                        <p class="card-text">Captura individual y masiva por unidad para tus materias y alumnos asignados.</p>
                        <a href="<?= $base ?>/formularios/procesos/Captura_Calificaciones.php" class="btn btn-primary">Capturar calificaciones</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-file-earmark-spreadsheet"></i> Reporte por Grupo</h5>
                        <p class="card-text">Consulta y descarga en Excel las calificaciones de tus materias y grupos.</p>
                        <a href="<?= $base ?>/formularios/reportes/Reporte_Calificaciones.php" class="btn btn-primary">Ver calificaciones</a>
                    </div>
                </div>
            </div>

        <?php } elseif (esAlumno()) { ?>
            <div class="col-md-5">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-journal-plus"></i> Meter Materias</h5>
                        <p class="card-text">Selecciona tus materias y profesores para inscribirlas en tu carga académica.</p>
                        <a href="<?= $base ?>/formularios/procesos/Asignacion_Materias.php" class="btn btn-primary">Inscribir materias</a>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-award"></i> Mis Calificaciones</h5>
                        <p class="card-text">Consulta tus calificaciones por unidad, promedios por materia y boleta oficial.</p>
                        <a href="<?= $base ?>/formularios/reportes/Reporte_Boleta.php" class="btn btn-primary">Ver mi boleta</a>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
