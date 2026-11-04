<?php
// Esta página está en la raíz, por eso $base se calcula aquí y no con conexion.php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
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

    <div class="text-center mb-5">
        <h1>Sistema de Administración Escolar</h1>
        <p class="text-muted">Selecciona un módulo para comenzar</p>
    </div>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-plus"></i> Registros</h5>
                    <p class="card-text">Catálogos de alumnos, profesores, grupos, materias y unidades.</p>
                    <a href="<?= $base ?>/Cruds/crudalumnos.php" class="btn btn-primary btn-sm">Alumnos</a>
                    <a href="<?= $base ?>/Cruds/crudprofesores.php" class="btn btn-primary btn-sm">Profesores</a>
                    <a href="<?= $base ?>/Cruds/crudgrupos.php" class="btn btn-primary btn-sm">Grupos</a>
                    <a href="<?= $base ?>/Cruds/crudmaterias.php" class="btn btn-primary btn-sm">Materias</a>
                    <a href="<?= $base ?>/Cruds/crudunidades.php" class="btn btn-primary btn-sm">Unidades</a>
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

    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
