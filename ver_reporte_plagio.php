<?php
session_start();

$sesionValida =
    isset($_SESSION["id_asesor"]) ||
    isset($_SESSION["id_director"]) ||
    isset($_SESSION["id_secretaria"]);

if (!$sesionValida) {
    http_response_code(403);
    exit("Acceso no autorizado.");
}

$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? "");

if ($idUsuario === "") {
    http_response_code(400);
    exit("Identificador inválido.");
}

$carpeta = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaInfo = $carpeta . DIRECTORY_SEPARATOR . "reporte_plagio.json";

if (!is_file($rutaInfo)) {
    http_response_code(404);
    exit("No se encontró el reporte de plagio.");
}

$datos = json_decode(file_get_contents($rutaInfo), true);

if (!is_array($datos) || empty($datos["archivo"])) {
    http_response_code(404);
    exit("No existe un reporte de plagio disponible.");
}

$rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . basename($datos["archivo"]);

if (!is_file($rutaArchivo)) {
    http_response_code(404);
    exit("El archivo del reporte no existe.");
}

$nombre = basename($datos["nombre_original"] ?? $datos["archivo"]);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header("Content-Type: application/pdf");
header("Content-Length: " . filesize($rutaArchivo));
header("Content-Disposition: inline; filename=\"" . str_replace('"', '', $nombre) . "\"");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, max-age=0, must-revalidate");

readfile($rutaArchivo);
exit;
?>
