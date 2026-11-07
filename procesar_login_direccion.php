<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_direccion.php");
    exit;
}

$correo = trim($_POST["correo_electronico"] ?? "");
$password = $_POST["password"] ?? "";

if ($correo === "" || $password === "") {
    $_SESSION["mensaje"] = "Complete todos los campos.";

    header("Location: login_direccion.php");
    exit;
}

$sql = "SELECT id_director, correo_electronico, password_hash
        FROM director
        WHERE correo_electronico = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje"] = "Ocurrió un error al consultar la base de datos.";

    header("Location: login_direccion.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$director = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

if (!$director || !password_verify($password, $director["password_hash"])) {
    $_SESSION["mensaje"] = "Correo electrónico o contraseña incorrectos.";

    header("Location: login_direccion.php");
    exit;
}

session_regenerate_id(true);

$_SESSION["id_director"] = (int) $director["id_director"];
$_SESSION["correo_director"] = $director["correo_electronico"];
$_SESSION["rol"] = "director";

header("Location: dashboard_direccion.php");
exit;
?>
