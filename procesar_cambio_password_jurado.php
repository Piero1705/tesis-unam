<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recuperar_password_jurado.php");
    exit;
}

$correo = trim($_POST["correo_electronico"] ?? "");
$nueva_password = $_POST["nueva_password"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";

if ($correo === "" || $nueva_password === "" || $confirmar_password === "") {
    $_SESSION["mensaje"] = "Complete todos los campos.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["mensaje"] = "Ingrese un correo electrónico válido.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

if ($nueva_password !== $confirmar_password) {
    $_SESSION["mensaje"] = "Las contraseñas no coinciden.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

if (strlen($nueva_password) < 6) {
    $_SESSION["mensaje"] = "La contraseña debe tener al menos 6 caracteres.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

$sql = "SELECT id_jurado FROM jurado
        WHERE correo_electronico = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje"] = "Ocurrió un error al consultar la base de datos.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$jurado = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$jurado) {
    $_SESSION["mensaje"] = "El correo electrónico no pertenece a un jurado registrado.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

$sql = "UPDATE jurado SET password_hash = ? WHERE id_jurado = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje"] = "No se pudo preparar la actualización.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $password_hash, $jurado["id_jurado"]);
$actualizado = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$actualizado) {
    $_SESSION["mensaje"] = "No se pudo actualizar la contraseña.";
    header("Location: recuperar_password_jurado.php");
    exit;
}

$_SESSION["mensaje"] = "Contraseña actualizada correctamente. Ya puede iniciar sesión.";
header("Location: login_jurado.php");
exit;
?>