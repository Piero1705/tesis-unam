<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: HTML_1_1.php");
    exit;
}

$correo = trim($_POST["correo_electronico"] ?? "");
$codigo = trim($_POST["codigo_universitario"] ?? "");
$password = $_POST["password"] ?? "";

$sql = "SELECT id_usuario, correo_electronico, codigo_universitario, password_hash
        FROM usuarios
        WHERE correo_electronico=? AND codigo_universitario=? LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss",$correo,$codigo);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows != 1){
    die("Correo o código universitario incorrectos.");
}

$usuario = $resultado->fetch_assoc();

if(!password_verify($password,$usuario["password_hash"])){
    die("Contraseña incorrecta.");
}

$_SESSION["id_usuario"]=$usuario["id_usuario"];
$_SESSION["correo_electronico"]=$usuario["correo_electronico"];
$_SESSION["codigo_universitario"]=$usuario["codigo_universitario"];

header("Location: dashboard_estudiante.php");
exit;
?>