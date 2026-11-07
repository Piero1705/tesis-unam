<?php
declare(strict_types=1);
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_secretaria.php");
    exit;
}

require_once "conexion_secretaria.php";

$correo = trim($_POST["correo_electronico"] ?? "");
$password = $_POST["password"] ?? "";

if ($correo === "" || $password === "") {
    $_SESSION["mensaje_login_secretaria"] = "Por favor, completa todos los campos.";
    $_SESSION["tipo_mensaje_login_secretaria"] = "error";
    header("Location: login_secretaria.php");
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["mensaje_login_secretaria"] = "Ingresa un correo electrónico válido.";
    $_SESSION["tipo_mensaje_login_secretaria"] = "error";
    header("Location: login_secretaria.php");
    exit;
}

$consulta = $conexion->prepare(
    "SELECT id_secretaria, correo_electronico, password_hash
     FROM secretaria
     WHERE correo_electronico = ?
     LIMIT 1"
);

if (!$consulta) {
    $_SESSION["mensaje_login_secretaria"] = "No se pudo procesar el inicio de sesión.";
    $_SESSION["tipo_mensaje_login_secretaria"] = "error";
    header("Location: login_secretaria.php");
    exit;
}

$consulta->bind_param("s", $correo);
$consulta->execute();
$resultado = $consulta->get_result();
$secretaria = $resultado->fetch_assoc();

$credenciales_validas = false;

if ($secretaria) {
    $password_guardado = (string) $secretaria["password_hash"];

    // Permite iniciar sesión con contraseñas antiguas guardadas como texto plano.
    if (password_verify($password, $password_guardado)) {
        $credenciales_validas = true;
    } elseif (hash_equals($password_guardado, $password)) {
        $credenciales_validas = true;

        // Migra automáticamente la contraseña antigua a un hash seguro.
        $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare(
            "UPDATE secretaria SET password_hash = ? WHERE id_secretaria = ?"
        );

        if ($actualizar) {
            $id_secretaria = (int) $secretaria["id_secretaria"];
            $actualizar->bind_param("si", $nuevo_hash, $id_secretaria);
            $actualizar->execute();
            $actualizar->close();
        }
    }
}

$consulta->close();
$conexion->close();

if (!$credenciales_validas) {
    $_SESSION["mensaje_login_secretaria"] =
        "El correo electrónico o la contraseña son incorrectos.";
    $_SESSION["tipo_mensaje_login_secretaria"] = "error";
    header("Location: login_secretaria.php");
    exit;
}

session_regenerate_id(true);

$_SESSION["id_secretaria"] = (int) $secretaria["id_secretaria"];
$_SESSION["correo_secretaria"] = (string) $secretaria["correo_electronico"];

header("Location: dashboard_secretaria.php");
exit;
