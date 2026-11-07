<?php
declare(strict_types=1);

$host = getenv("DB_HOST") ?: "localhost";
$usuario = getenv("DB_USER") ?: "root";
$contrasena = getenv("DB_PASSWORD") ?: "";
$base_datos = getenv("DB_NAME") ?: "tesis_unam";

$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión con la base de datos.");
}

$conexion->set_charset("utf8mb4");
