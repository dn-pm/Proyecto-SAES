<!-- PRUEBA NAVBAR NUEVO -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">

    <a class="navbar-brand" href="<?= $base ?>/principal.php">Proyecto SAE</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Catálogos</a>
          <ul class="dropdown-menu bg-dark">
            <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudalumnos.php">Alumnos</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudprofesores.php">Profesores</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudgrupos.php">Grupos</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/Cruds/crudmaterias.php">Materias</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Registrar</a>
          <ul class="dropdown-menu bg-dark">
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/alta/Formulario_Alumnos.php">Alumnos</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/alta/Formulario_Profesores.php">Profesores</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/alta/Formulario_Grupos.php">Grupos</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/alta/Formulario_Materias.php">Materias</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Modificar</a>
          <ul class="dropdown-menu bg-dark">
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/modificar/Modificar_Alumnos.php">Alumnos</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/modificar/Modificar_Profesores.php">Profesores</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/modificar/Modificar_Grupos.php">Grupos</a></li>
            <li><a class="dropdown-item text-light" href="<?= $base ?>/formularios/modificar/Modificar_Materias.php">Materias</a></li>
          </ul>
        </li>

      </ul>
    </div>

  </div>
</nav>
