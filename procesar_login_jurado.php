<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_jurado.php");
    exit;
}

$correo = trim($_POST["correo_electronico"] ?? "");
$password = $_POST["password"] ?? "";

if ($correo === "" || $password === "") {
    $_SESSION["mensaje"] = "Complete todos los campos.";
    header("Location: login_jurado.php");
    exit;
}

$sql = "SELECT id_jurado, correo_electronico, password_hash
        FROM jurado
        WHERE correo_electronico = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje"] = "Ocurrió un error al consultar la base de datos.";
    header("Location: login_jurado.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$jurado = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$jurado || !password_verify($password, $jurado["password_hash"])) {
    $_SESSION["mensaje"] = "Correo electrónico o contraseña incorrectos.";
    header("Location: login_jurado.php");
    exit;
}

session_regenerate_id(true);
$_SESSION["id_jurado"] = $jurado["id_jurado"];
$_SESSION["correo_jurado"] = $jurado["correo_electronico"];
$_SESSION["rol"] = "jurado";

header("Location: dashboard_jurado.php");
exit;
?>