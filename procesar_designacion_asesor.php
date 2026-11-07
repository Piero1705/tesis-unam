<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_director"])) {
    header("Location: login_direccion.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard_direccion.php");
    exit;
}

$idUsuario = preg_replace("/[^0-9]/", "", $_POST["id_usuario"] ?? "");
$idAsesor = (int) ($_POST["id_asesor"] ?? 0);

if ($idUsuario === "" || $idAsesor <= 0) {
    $_SESSION["mensaje_designacion"] = "Seleccione un asesor válido.";
    header("Location: designar_asesor.php?id=" . rawurlencode($idUsuario));
    exit;
}

$sql = "SELECT id_asesor, correo_electronico, codigo_universitario
        FROM asesor
        WHERE id_asesor = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje_designacion"] = "No se pudo consultar la información del asesor.";
    header("Location: designar_asesor.php?id=" . rawurlencode($idUsuario));
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $idAsesor);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$asesor = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

if (!$asesor) {
    $_SESSION["mensaje_designacion"] = "El asesor seleccionado no existe.";
    header("Location: designar_asesor.php?id=" . rawurlencode($idUsuario));
    exit;
}

$carpetaEstudiante = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaSolicitud = $carpetaEstudiante . DIRECTORY_SEPARATOR . "solicitud_enviada.json";
$rutaAsignacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "asesor_asignado.json";

if (!is_file($rutaSolicitud)) {
    $_SESSION["mensaje_direccion"] = "La solicitud del estudiante ya no está disponible.";
    $_SESSION["tipo_mensaje_direccion"] = "error";

    header("Location: dashboard_direccion.php");
    exit;
}

$solicitud = json_decode(file_get_contents($rutaSolicitud), true);

if (!is_array($solicitud)) {
    $solicitud = [];
}

$asignacion = [
    "id_usuario" => (int) $idUsuario,
    "codigo_universitario_estudiante" => $solicitud["codigo_universitario"] ?? "",
    "correo_estudiante" => $solicitud["correo_estudiante"] ?? "",
    "id_asesor" => (int) $asesor["id_asesor"],
    "correo_asesor" => $asesor["correo_electronico"],
    "codigo_universitario_asesor" => $asesor["codigo_universitario"] ?? "",
    "id_director" => (int) $_SESSION["id_director"],
    "fecha_designacion" => date("Y-m-d H:i:s"),
    "estado" => "Solicitud aprobada y asesor designado"
];

$guardado = file_put_contents(
    $rutaAsignacion,
    json_encode($asignacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($guardado === false) {
    $_SESSION["mensaje_designacion"] = "No se pudo guardar la designación del asesor.";
    header("Location: designar_asesor.php?id=" . rawurlencode($idUsuario));
    exit;
}

$solicitud["estado"] = "Aprobada por Dirección";
$solicitud["fecha_aprobacion"] = date("Y-m-d H:i:s");

file_put_contents(
    $rutaSolicitud,
    json_encode($solicitud, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$_SESSION["mensaje_direccion"] = "La solicitud fue aprobada y el asesor fue designado correctamente.";
$_SESSION["tipo_mensaje_direccion"] = "exito";

header("Location: dashboard_direccion.php");
exit;
?>
