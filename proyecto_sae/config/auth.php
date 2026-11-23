<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base = preg_replace('#/(Cruds|formularios|NavBar|config)(/.*)?$#i', '', $scriptDir);
$base = rtrim($base, '/');

function esAdmin(): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMINISTRADOR';
}

function esMaestro(): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'MAESTRO';
}

function esAlumno(): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'ALUMNO';
}

function requerirLogin(): void {
    global $base;
    if (!isset($_SESSION['id_user'])) {
        header("Location: " . $base . "/login.php");
        exit;
    }
}

function requerirRol(array $rolesPermitidos): void {
    global $base;
    requerirLogin();
    if (!in_array($_SESSION['rol'] ?? '', $rolesPermitidos, true)) {
        header("Location: " . $base . "/principal.php?denegado=1");
        exit;
    }
}
