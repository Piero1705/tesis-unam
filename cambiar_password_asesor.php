<?php
declare(strict_types=1);
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recuperar_password_asesor.php");
    exit;
}

require_once "conexion_asesor.php";

$correo = trim($_POST["correo_electronico"] ?? "");
$password_nueva = $_POST["password_nueva"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";

function volverRecuperacion(string $mensaje, string $tipo = "error"): never
{
    $_SESSION["mensaje_password_asesor"] = $mensaje;
    $_SESSION["tipo_mensaje_password_asesor"] = $tipo;
    header("Location: recuperar_password_asesor.php");
    exit;
}

if ($correo === "" || $password_nueva === "" || $confirmar_password === "") {
    volverRecuperacion("Por favor, completa todos los campos.");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    volverRecuperacion("Ingresa un correo electrónico válido.");
}

if (strlen($password_nueva) < 8) {
    volverRecuperacion("La contraseña debe tener como mínimo 8 caracteres.");
}

if ($password_nueva !== $confirmar_password) {
    volverRecuperacion("Las contraseñas ingresadas no coinciden.");
}

$buscar = $conexion->prepare(
    "SELECT id_asesor
     FROM asesor
     WHERE correo_electronico = ?
     LIMIT 1"
);

if (!$buscar) {
    volverRecuperacion("No se pudo verificar el correo electrónico.");
}

$buscar->bind_param("s", $correo);
$buscar->execute();
$resultado = $buscar->get_result();
$asesor = $resultado->fetch_assoc();
$buscar->close();

if (!$asesor) {
    $conexion->close();
    volverRecuperacion("El correo electrónico no pertenece a un asesor registrado.");
}

$nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
$id_asesor = (int) $asesor["id_asesor"];

$actualizar = $conexion->prepare(
    "UPDATE asesor
     SET password_hash = ?
     WHERE id_asesor = ?"
);

if (!$actualizar) {
    $conexion->close();
    volverRecuperacion("No se pudo preparar el cambio de contraseña.");
}

$actualizar->bind_param("si", $nuevo_hash, $id_asesor);
$actualizar->execute();

$correcto = $actualizar->affected_rows >= 0;

$actualizar->close();
$conexion->close();

if (!$correcto) {
    volverRecuperacion("No se pudo cambiar la contraseña.");
}

$_SESSION["mensaje_login_asesor"] =
    "La contraseña fue actualizada correctamente. Ya puedes iniciar sesión.";
$_SESSION["tipo_mensaje_login_asesor"] = "correcto";

header("Location: login_asesor.php");
exit;
