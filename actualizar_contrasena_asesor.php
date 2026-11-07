<?php

require_once "conexion_asesor.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: HTML_asesor_1_2.php");
    exit;
}

$correo = strtolower(trim($_POST["correo_electronico"] ?? ""));
$nuevaPassword = $_POST["nueva_password"] ?? "";
$confirmarPassword = $_POST["confirmar_password"] ?? "";

if ($correo === "" || $nuevaPassword === "" || $confirmarPassword === "") {
    die("Complete todos los campos.");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Ingrese un correo electrónico válido.");
}

if ($nuevaPassword !== $confirmarPassword) {
    die("Las contraseñas no coinciden.");
}

$buscar = $conexion->prepare(
    "SELECT id_asesor
     FROM tesis_unam.asesor
     WHERE LOWER(TRIM(correo_electronico)) = ?
     LIMIT 1"
);

if (!$buscar) {
    die("Error al preparar la búsqueda: " . $conexion->error);
}

$buscar->bind_param("s", $correo);
$buscar->execute();

$resultado = $buscar->get_result();

if ($resultado->num_rows !== 1) {
    $buscar->close();
    $conexion->close();
    die("El correo no se encuentra registrado en la tabla asesor.");
}

$nuevoHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

$actualizar = $conexion->prepare(
    "UPDATE tesis_unam.asesor
     SET password_hash = ?
     WHERE LOWER(TRIM(correo_electronico)) = ?"
);

if (!$actualizar) {
    die("Error al preparar la actualización: " . $conexion->error);
}

$actualizar->bind_param("ss", $nuevoHash, $correo);

if (!$actualizar->execute()) {
    die("No se pudo actualizar la contraseña.");
}

$actualizar->close();
$buscar->close();
$conexion->close();

header("Location: HTML_asesor_1_1.php");
exit;

?>
