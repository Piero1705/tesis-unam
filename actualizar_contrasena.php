<?php

session_start();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: HTML_1_2.php");
    exit;
}

$correo = trim($_POST["correo_electronico"] ?? "");
$nuevaPassword = $_POST["nueva_password"] ?? "";
$confirmarPassword = $_POST["confirmar_password"] ?? "";

if ($correo === "") {
    die("Ingrese un correo electrónico.");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Ingrese un correo electrónico válido.");
}

if ($nuevaPassword === "") {
    die("Ingrese una nueva contraseña.");
}

if ($confirmarPassword === "") {
    die("Confirme la nueva contraseña.");
}

if ($nuevaPassword !== $confirmarPassword) {
    die("Las contraseñas no coinciden.");
}

$consulta = $conexion->prepare(
    "SELECT id_usuario
     FROM usuarios
     WHERE correo_electronico = ?
     LIMIT 1"
);

$consulta->bind_param("s", $correo);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows !== 1) {
    $consulta->close();
    $conexion->close();
    die("El correo electrónico no se encuentra registrado.");
}

$hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

$actualizar = $conexion->prepare(
    "UPDATE usuarios
     SET password_hash = ?
     WHERE correo_electronico = ?"
);

$actualizar->bind_param("ss", $hash, $correo);

if ($actualizar->execute()) {

    $actualizar->close();
    $consulta->close();
    $conexion->close();

    header("Location: HTML_1_1.php");
    exit;

} else {

    $actualizar->close();
    $consulta->close();
    $conexion->close();

    die("No fue posible actualizar la contraseña.");

}

?>