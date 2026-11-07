<?php
declare(strict_types=1);
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_asesor.php");
    exit;
}

require_once "conexion_asesor.php";

$correo = trim($_POST["correo_electronico"] ?? "");
$codigo = trim($_POST["codigo_estudiante"] ?? "");
$password = $_POST["password"] ?? "";

function volverLogin(string $mensaje): never
{
    $_SESSION["mensaje_login_asesor"] = $mensaje;
    $_SESSION["tipo_mensaje_login_asesor"] = "error";
    header("Location: login_asesor.php");
    exit;
}

if ($correo === "" || $codigo === "" || $password === "") {
    volverLogin("Por favor, completa todos los campos.");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    volverLogin("Ingresa un correo electrónico válido.");
}

$consulta = $conexion->prepare(
    "SELECT id_asesor, correo_electronico, codigo_universitario, password_hash
     FROM asesor
     WHERE correo_electronico = ?
       AND codigo_universitario = ?
     LIMIT 1"
);

if (!$consulta) {
    volverLogin("No se pudo procesar el inicio de sesión.");
}

$consulta->bind_param("ss", $correo, $codigo);
$consulta->execute();
$resultado = $consulta->get_result();
$asesor = $resultado->fetch_assoc();

$credenciales_validas = false;

if ($asesor) {
    $password_guardado = (string) $asesor["password_hash"];

    if (password_verify($password, $password_guardado)) {
        $credenciales_validas = true;
    } elseif (hash_equals($password_guardado, $password)) {
        // Compatibilidad temporal con contraseñas guardadas como texto plano.
        $credenciales_validas = true;

        // Convierte automáticamente la contraseña a un hash seguro.
        $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare(
            "UPDATE asesor SET password_hash = ? WHERE id_asesor = ?"
        );

        if ($actualizar) {
            $id_asesor = (int) $asesor["id_asesor"];
            $actualizar->bind_param("si", $nuevo_hash, $id_asesor);
            $actualizar->execute();
            $actualizar->close();
        }
    }
}

$consulta->close();
$conexion->close();

if (!$credenciales_validas) {
    volverLogin("El correo, código del estudiante o contraseña son incorrectos.");
}

session_regenerate_id(true);

$_SESSION["id_asesor"] = (int) $asesor["id_asesor"];
$_SESSION["correo_asesor"] = (string) $asesor["correo_electronico"];
$_SESSION["codigo_estudiante_asesorado"] =
    (string) $asesor["codigo_universitario"];

header("Location: dashboard_asesor.php");
exit;
