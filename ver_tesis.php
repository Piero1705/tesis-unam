<?php
session_start();

$sesionValida =
    isset($_SESSION["id_usuario"]) ||
    isset($_SESSION["id_asesor"]) ||
    isset($_SESSION["id_director"]) ||
    isset($_SESSION["id_secretaria"]) ||
    isset($_SESSION["id_jurado"]);

if (!$sesionValida) {
    http_response_code(403);
    exit("Acceso no autorizado.");
}

$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? "");

if ($idUsuario === "") {
    http_response_code(400);
    exit("Identificador de estudiante inválido.");
}

$carpetaEstudiante = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaInformacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "tesis_completa.json";

if (!is_file($rutaInformacion)) {
    http_response_code(404);
    exit("No se encontró la información de la tesis.");
}

$informacion = json_decode(file_get_contents($rutaInformacion), true);

if (!is_array($informacion) || empty($informacion["archivo"])) {
    http_response_code(404);
    exit("No existe una tesis disponible.");
}

$nombreArchivo = basename($informacion["archivo"]);
$rutaArchivo = $carpetaEstudiante . DIRECTORY_SEPARATOR . $nombreArchivo;

if (!is_file($rutaArchivo)) {
    http_response_code(404);
    exit("El archivo físico de la tesis no existe.");
}

$extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));

$tiposMime = [
    "pdf" => "application/pdf",
    "doc" => "application/msword",
    "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
];

$tipoMime = $tiposMime[$extension] ?? "application/octet-stream";
$nombreOriginal = basename($informacion["nombre_original"] ?? $nombreArchivo);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header("Content-Type: " . $tipoMime);
header("Content-Length: " . filesize($rutaArchivo));
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

if ($extension === "pdf") {
    header('Content-Disposition: inline; filename="' . str_replace('"', '', $nombreOriginal) . '"');
} else {
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $nombreOriginal) . '"');
}

readfile($rutaArchivo);
exit;
?>
