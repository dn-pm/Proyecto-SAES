<?php
// =========================================================
// Archivo de conexión a la base de datos
// Todos los formularios (de alta y de modificación) incluyen
// este archivo con: require_once "../../config/conexion.php";
// =========================================================

$servidor = "localhost";
$usuario  = "root";
$password = "";        // Cambia esto si tu MySQL tiene contraseña
$base_datos = "proyecto_sae";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
