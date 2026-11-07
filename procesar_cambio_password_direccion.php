<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recuperar_password_direccion.php");
    exit;
}

$correo = trim($_POST["correo_electronico"] ?? "");
$nueva_password = $_POST["nueva_password"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";

if ($correo === "" || $nueva_password === "" || $confirmar_password === "") {
    $_SESSION["mensaje"] = "Complete todos los campos.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["mensaje"] = "Ingrese un correo electrónico válido.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

if ($nueva_password !== $confirmar_password) {
    $_SESSION["mensaje"] = "Las contraseñas no coinciden.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

if (strlen($nueva_password) < 6) {
    $_SESSION["mensaje"] = "La contraseña debe tener al menos 6 caracteres.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

$sql = "SELECT id_director
        FROM director
        WHERE correo_electronico = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje"] = "Ocurrió un error al consultar la base de datos.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$director = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

if (!$director) {
    $_SESSION["mensaje"] = "El correo electrónico no pertenece a un Director registrado.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

$sql_update = "UPDATE director
               SET password_hash = ?
               WHERE id_director = ?";

$stmt_update = mysqli_prepare($conexion, $sql_update);

if (!$stmt_update) {
    $_SESSION["mensaje"] = "No se pudo preparar la actualización.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

$id_director = (int) $director["id_director"];

mysqli_stmt_bind_param(
    $stmt_update,
    "si",
    $password_hash,
    $id_director
);

$actualizado = mysqli_stmt_execute($stmt_update);

mysqli_stmt_close($stmt_update);

if (!$actualizado) {
    $_SESSION["mensaje"] = "No se pudo actualizar la contraseña.";
    $_SESSION["tipo_mensaje"] = "error";

    header("Location: recuperar_password_direccion.php");
    exit;
}

$_SESSION["mensaje"] = "Contraseña actualizada correctamente. Ya puede iniciar sesión.";
$_SESSION["tipo_mensaje"] = "exito";

header("Location: login_direccion.php");
exit;
?>
