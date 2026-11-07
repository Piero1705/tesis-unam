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
$idsJurados = $_POST["jurados"] ?? [];

$idsJurados = array_values(
    array_unique(
        array_filter(
            array_map("intval", $idsJurados),
            fn(int $id): bool => $id > 0
        )
    )
);

if ($idUsuario === "" || count($idsJurados) !== 3) {
    $_SESSION["mensaje_jurados"] = "Debes seleccionar exactamente tres jurados.";
    header("Location: designar_jurados.php?id=" . rawurlencode($idUsuario));
    exit;
}

$sql = "SELECT id_jurado, correo_electronico
        FROM jurado
        WHERE id_jurado IN (?, ?, ?)";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    $_SESSION["mensaje_jurados"] = "No se pudo consultar la lista de jurados.";
    header("Location: designar_jurados.php?id=" . rawurlencode($idUsuario));
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $idsJurados[0],
    $idsJurados[1],
    $idsJurados[2]
);

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$jurados = [];

while ($fila = mysqli_fetch_assoc($resultado)) {
    $jurados[] = [
        "id_jurado" => (int) $fila["id_jurado"],
        "correo_electronico" => $fila["correo_electronico"]
    ];
}

mysqli_stmt_close($stmt);

if (count($jurados) !== 3) {
    $_SESSION["mensaje_jurados"] = "Uno o más jurados seleccionados no existen.";
    header("Location: designar_jurados.php?id=" . rawurlencode($idUsuario));
    exit;
}

$carpeta = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaSolicitud = $carpeta . DIRECTORY_SEPARATOR . "solicitud_enviada.json";
$rutaTesis = $carpeta . DIRECTORY_SEPARATOR . "tesis_completa.json";
$rutaJurados = $carpeta . DIRECTORY_SEPARATOR . "jurados_asignados.json";

if (!is_file($rutaSolicitud) || !is_file($rutaTesis)) {
    $_SESSION["mensaje_direccion"] = "No se encontró la tesis completa del estudiante.";
    $_SESSION["tipo_mensaje_direccion"] = "error";
    header("Location: dashboard_direccion.php");
    exit;
}

$solicitud = json_decode(file_get_contents($rutaSolicitud), true);

$datos = [
    "id_usuario" => (int) $idUsuario,
    "codigo_universitario" => $solicitud["codigo_universitario"] ?? "",
    "correo_estudiante" => $solicitud["correo_estudiante"] ?? "",
    "id_director" => (int) $_SESSION["id_director"],
    "fecha_designacion" => date("Y-m-d H:i:s"),
    "estado" => "Jurados dictaminadores designados",
    "jurados" => $jurados
];

$guardado = file_put_contents(
    $rutaJurados,
    json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($guardado === false) {
    $_SESSION["mensaje_jurados"] = "No se pudo guardar la designación de jurados.";
    header("Location: designar_jurados.php?id=" . rawurlencode($idUsuario));
    exit;
}

$_SESSION["mensaje_direccion"] = "Los tres jurados fueron designados correctamente.";
$_SESSION["tipo_mensaje_direccion"] = "exito";

header("Location: dashboard_direccion.php");
exit;
?>
