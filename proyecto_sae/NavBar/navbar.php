<?php
require_once __DIR__ . "/../config/auth.php";

$navbarSolo = isset($_SERVER["SCRIPT_FILENAME"]) && realpath($_SERVER["SCRIPT_FILENAME"]) === realpath(__FILE__);

if ($navbarSolo) {
    requerirLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar - Proyecto SAE</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/bootstrap-icons.css">
</head>
<body>
<?php } ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">

    <a class="navbar-brand" href="<?= $base ?>/principal.php">Proyecto SAE</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <?php if (esAdmin()) { ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Registros</a>
            <ul class="dropdown-menu bg-dark">
              <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudalumnos.php">Alumnos</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudprofesores.php">Profesores</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudgrupos.php">Grupos</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudmaterias.php">Materias</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudunidades.php">Unidades</a></li>
              <li><hr class="dropdown-divider border-secondary"></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudusuarios.php">Usuarios</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Procesos</a>
            <ul class="dropdown-menu bg-dark">
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/procesos/Inscripcion_Grupos.php">Inscripción a grupos</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/procesos/Asignacion_Materias.php">Asignación de materias</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/procesos/Captura_Calificaciones.php">Captura de calificaciones</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Reportes</a>
            <ul class="dropdown-menu bg-dark">
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/reportes/Reporte_Alumnos_Grupo.php">Alumnos por grupo</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/reportes/Reporte_Materias_Alumno.php">Materias por alumno</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/reportes/Reporte_Calificaciones.php">Calificaciones por materia y grupo</a></li>
              <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/reportes/Reporte_Boleta.php">Boleta de calificaciones</a></li>
            </ul>
          </li>
        <?php } elseif (esMaestro()) { ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/formularios/reportes/Reporte_Alumnos_Grupo.php">Mis grupos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/formularios/procesos/Captura_Calificaciones.php">Capturar calificaciones</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/formularios/reportes/Reporte_Calificaciones.php">Reporte de calificaciones</a>
          </li>
        <?php } elseif (esAlumno()) { ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/formularios/procesos/Asignacion_Materias.php">Meter materias</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/formularios/reportes/Reporte_Boleta.php">Mis calificaciones</a>
          </li>
        <?php } ?>

      </ul>

      <?php if (isset($_SESSION['id_user'])) {
          $badgeClass = esAdmin() ? 'bg-danger' : (esMaestro() ? 'bg-info text-dark' : 'bg-success');
      ?>
        <div class="d-flex align-items-center gap-2 text-light">
          <span class="small">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['nombre_mostrar'] ?? $_SESSION['nombre_user']) ?>
          </span>
          <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($_SESSION['puesto_desc'] ?? $_SESSION['rol']) ?></span>
          <a href="<?= $base ?>/logout.php" class="btn btn-outline-light btn-sm ms-2">
            <i class="bi bi-box-arrow-right"></i> Salir
          </a>
        </div>
      <?php } ?>
    </div>

  </div>
</nav>
<?php if ($navbarSolo) { ?>
<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
