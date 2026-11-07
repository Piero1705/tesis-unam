<?php
declare(strict_types=1);
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recuperar_password_secretaria.php");
    exit;
}

require_once "conexion_secretaria.php";

$correo = trim($_POST["correo_electronico"] ?? "");
$password_nueva = $_POST["password_nueva"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";

function volverConMensaje(string $mensaje, string $tipo = "error"): never
{
    $_SESSION["mensaje_password_secretaria"] = $mensaje;
    $_SESSION["tipo_mensaje_password_secretaria"] = $tipo;
    header("Location: recuperar_password_secretaria.php");
    exit;
}

if ($correo === "" || $password_nueva === "" || $confirmar_password === "") {
    volverConMensaje("Por favor, completa todos los campos.");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    volverConMensaje("Ingresa un correo electrónico válido.");
}

if (strlen($password_nueva) < 8) {
    volverConMensaje("La contraseña debe tener como mínimo 8 caracteres.");
}

if ($password_nueva !== $confirmar_password) {
    volverConMensaje("Las contraseñas ingresadas no coinciden.");
}

$buscar = $conexion->prepare(
    "SELECT id_secretaria
     FROM secretaria
     WHERE correo_electronico = ?
     LIMIT 1"
);

if (!$buscar) {
    volverConMensaje("No se pudo verificar el correo electrónico.");
}

$buscar->bind_param("s", $correo);
$buscar->execute();
$resultado = $buscar->get_result();
$secretaria = $resultado->fetch_assoc();
$buscar->close();

if (!$secretaria) {
    $conexion->close();
    volverConMensaje("El correo electrónico no pertenece a una secretaria registrada.");
}

$nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
$id_secretaria = (int) $secretaria["id_secretaria"];

$actualizar = $conexion->prepare(
    "UPDATE secretaria
     SET password_hash = ?
     WHERE id_secretaria = ?"
);

if (!$actualizar) {
    $conexion->close();
    volverConMensaje("No se pudo preparar el cambio de contraseña.");
}

$actualizar->bind_param("si", $nuevo_hash, $id_secretaria);
$actualizar->execute();

$filas_actualizadas = $actualizar->affected_rows;

$actualizar->close();
$conexion->close();

if ($filas_actualizadas < 0) {
    volverConMensaje("No se pudo cambiar la contraseña.");
}

$_SESSION["mensaje_login_secretaria"] =
    "La contraseña fue actualizada correctamente. Ya puedes iniciar sesión.";
$_SESSION["tipo_mensaje_login_secretaria"] = "correcto";

header("Location: login_secretaria.php");
exit;
