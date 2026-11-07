<?php
declare(strict_types=1);

$host = getenv("DB_HOST") ?: "localhost";
$usuario = getenv("DB_USER") ?: "root";
$password_bd = getenv("DB_PASSWORD") ?: "";
$base_datos = getenv("DB_NAME") ?: "tesis_unam";

$conexion = new mysqli($host, $usuario, $password_bd, $base_datos);

if ($conexion->connect_error) {
    die("No se pudo conectar con la base de datos.");
}

$conexion->set_charset("utf8mb4");
