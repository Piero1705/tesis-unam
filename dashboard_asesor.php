<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["id_asesor"])) {
    header("Location: login_asesor.php");
    exit;
}

$idAsesor = (int) $_SESSION["id_asesor"];
$correoAsesor = $_SESSION["correo_asesor"] ?? "Asesor de tesis";
$carpetaBase = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante";
$tesistas = [];
$mensaje = "";
$tipoMensaje = "";

function leerJsonAsesor(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
}

function guardarJsonAsesor(string $ruta, array $datos): bool
{
    return file_put_contents(
        $ruta,
        json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";
    $idUsuario = preg_replace("/[^0-9]/", "", $_POST["id_usuario"] ?? "");

    if ($idUsuario === "") {
        $mensaje = "No se pudo identificar al estudiante.";
        $tipoMensaje = "error";
    } else {
        $carpetaEstudiante = $carpetaBase . DIRECTORY_SEPARATOR . $idUsuario;
        $rutaAsignacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "asesor_asignado.json";
        $asignacion = leerJsonAsesor($rutaAsignacion);

        if ((int) ($asignacion["id_asesor"] ?? 0) !== $idAsesor) {
            $mensaje = "No tienes autorización para modificar este expediente.";
            $tipoMensaje = "error";
        } elseif ($accion === "subir_reporte_plagio") {
            $rutaReporteInfo = $carpetaEstudiante . DIRECTORY_SEPARATOR . "reporte_plagio.json";

            if (!isset($_FILES["reporte_plagio"])) {
                $mensaje = "Selecciona un reporte de plagio en formato PDF.";
                $tipoMensaje = "error";
            } else {
                $archivo = $_FILES["reporte_plagio"];

                if ($archivo["error"] !== UPLOAD_ERR_OK) {
                    $mensaje = "No se pudo recibir el reporte de plagio.";
                    $tipoMensaje = "error";
                } else {
                    $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
                    $tipoMime = mime_content_type($archivo["tmp_name"]) ?: "";

                    if ($extension !== "pdf" || $tipoMime !== "application/pdf") {
                        $mensaje = "El reporte de plagio debe ser obligatoriamente un archivo PDF.";
                        $tipoMensaje = "error";
                    } else {
                        $reporteAnterior = leerJsonAsesor($rutaReporteInfo);

                        if (!empty($reporteAnterior["archivo"])) {
                            $rutaAnterior = $carpetaEstudiante
                                . DIRECTORY_SEPARATOR
                                . basename($reporteAnterior["archivo"]);

                            if (is_file($rutaAnterior)) {
                                unlink($rutaAnterior);
                            }
                        }

                        $nombreFinal = "reporte_plagio_" . date("Ymd_His") . ".pdf";
                        $destino = $carpetaEstudiante . DIRECTORY_SEPARATOR . $nombreFinal;

                        if (move_uploaded_file($archivo["tmp_name"], $destino)) {
                            $datosReporte = [
                                "id_usuario" => (int) $idUsuario,
                                "id_asesor" => $idAsesor,
                                "correo_asesor" => $correoAsesor,
                                "archivo" => $nombreFinal,
                                "nombre_original" => basename($archivo["name"]),
                                "fecha_subida" => date("Y-m-d H:i:s"),
                                "estado" => "Reporte de plagio presentado"
                            ];

                            guardarJsonAsesor($rutaReporteInfo, $datosReporte);

                            $mensaje = "El reporte de plagio fue subido correctamente.";
                            $tipoMensaje = "exito";
                        } else {
                            $mensaje = "No se pudo guardar el reporte de plagio.";
                            $tipoMensaje = "error";
                        }
                    }
                }
            }
        } elseif ($accion === "guardar_observacion") {
            $rutaReporteInfo = $carpetaEstudiante . DIRECTORY_SEPARATOR . "reporte_plagio.json";
            $reportePlagio = leerJsonAsesor($rutaReporteInfo);
            $observacionTexto = trim($_POST["observacion"] ?? "");

            if (empty($reportePlagio["archivo"])) {
                $mensaje = "Antes de enviar o actualizar la observación debes subir obligatoriamente el reporte de plagio en PDF.";
                $tipoMensaje = "error";
            } elseif ($observacionTexto === "") {
                $mensaje = "La observación no puede estar vacía.";
                $tipoMensaje = "error";
            } else {
                $rutaObservacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "observacion_asesor.json";

                $datos = [
                    "id_usuario" => (int) $idUsuario,
                    "id_asesor" => $idAsesor,
                    "correo_asesor" => $correoAsesor,
                    "observacion" => $observacionTexto,
                    "fecha_observacion" => date("Y-m-d H:i:s"),
                    "reporte_plagio" => $reportePlagio["archivo"],
                    "estado" => "Observación enviada al estudiante"
                ];

                guardarJsonAsesor($rutaObservacion, $datos);

                $mensaje = "La observación fue enviada al estudiante.";
                $tipoMensaje = "exito";
            }
        }
    }
}

if (is_dir($carpetaBase)) {
    foreach (scandir($carpetaBase) as $idUsuario) {
        if ($idUsuario === "." || $idUsuario === "..") {
            continue;
        }

        $idSeguro = preg_replace("/[^0-9]/", "", $idUsuario);

        if ($idSeguro === "") {
            continue;
        }

        $carpetaEstudiante = $carpetaBase . DIRECTORY_SEPARATOR . $idSeguro;
        $asignacion = leerJsonAsesor($carpetaEstudiante . DIRECTORY_SEPARATOR . "asesor_asignado.json");

        if ((int) ($asignacion["id_asesor"] ?? 0) !== $idAsesor) {
            continue;
        }

        $tesis = leerJsonAsesor($carpetaEstudiante . DIRECTORY_SEPARATOR . "tesis_completa.json");
        $observacion = leerJsonAsesor($carpetaEstudiante . DIRECTORY_SEPARATOR . "observacion_asesor.json");
        $reportePlagio = leerJsonAsesor($carpetaEstudiante . DIRECTORY_SEPARATOR . "reporte_plagio.json");

        $tesistas[] = [
            "id_usuario" => $idSeguro,
            "codigo" => $asignacion["codigo_universitario_estudiante"] ?? "Sin código",
            "correo" => $asignacion["correo_estudiante"] ?? "Sin correo",
            "tesis" => $tesis,
            "observacion" => $observacion,
            "reporte_plagio" => $reportePlagio
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Asesor</title>
    <link rel="stylesheet" href="dashboard_asesor_simple.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="marca">
            <img src="logo.png" alt="Logo UNAM">
            <div>
                <h1>UNAM</h1>
                <p>Módulo del Asesor</p>
            </div>
        </div>

        <nav class="menu">
            <a href="dashboard_asesor.php" class="activo">📄 Tesis asignadas</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <span class="subtitulo">Asesoría de tesis</span>
            <h1>Mis tesistas asignados</h1>
            <p><?php echo htmlspecialchars((string) $correoAsesor); ?></p>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if (count($tesistas) === 0): ?>
            <section class="panel estado-vacio">
                La Dirección de la EPISI todavía no te ha asignado un tesista.
            </section>
        <?php endif; ?>

        <?php foreach ($tesistas as $tesista): ?>
            <section class="panel">

                <div class="panel-titulo">
                    <div>
                        <span class="subtitulo">Tesista asignado</span>
                        <h2><?php echo htmlspecialchars($tesista["codigo"]); ?></h2>
                        <p><?php echo htmlspecialchars($tesista["correo"]); ?></p>
                    </div>

                    <?php if (!empty($tesista["tesis"]["archivo"])): ?>
                        <span class="estado recibido">Tesis recibida</span>
                    <?php else: ?>
                        <span class="estado pendiente">Esperando tesis</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tesista["tesis"]["archivo"])): ?>

                    <div class="archivo-tesis">
                        <div>
                            <strong>
                                <?php echo htmlspecialchars(
                                    $tesista["tesis"]["nombre_original"]
                                    ?? $tesista["tesis"]["archivo"]
                                ); ?>
                            </strong>

                            <small>
                                Fecha:
                                <?php echo htmlspecialchars($tesista["tesis"]["fecha_subida"] ?? ""); ?>
                            </small>
                        </div>

                        <a
                            href="ver_tesis.php?id=<?php echo rawurlencode($tesista["id_usuario"]); ?>"
                            target="_blank"
                        >
                            Visualizar tesis
                        </a>
                    </div>

                    <section class="bloque-plagio">
                        <div class="cabecera-plagio">
                            <div>
                                <span class="subtitulo">Control obligatorio</span>
                                <h3>Reporte de plagio</h3>
                                <p>
                                    Antes de enviar o actualizar la observación debes adjuntar un reporte en PDF.
                                </p>
                            </div>

                            <?php if (!empty($tesista["reporte_plagio"]["archivo"])): ?>
                                <span class="estado recibido">Reporte cargado</span>
                            <?php else: ?>
                                <span class="estado pendiente">Reporte pendiente</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($tesista["reporte_plagio"]["archivo"])): ?>
                            <div class="reporte-actual">
                                <div>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            $tesista["reporte_plagio"]["nombre_original"]
                                            ?? $tesista["reporte_plagio"]["archivo"]
                                        ); ?>
                                    </strong>

                                    <small>
                                        Subido:
                                        <?php echo htmlspecialchars(
                                            $tesista["reporte_plagio"]["fecha_subida"] ?? ""
                                        ); ?>
                                    </small>
                                </div>

                                <a
                                    href="ver_reporte_plagio.php?id=<?php echo rawurlencode($tesista["id_usuario"]); ?>"
                                    target="_blank"
                                >
                                    Visualizar reporte
                                </a>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="formulario-plagio">
                            <input type="hidden" name="accion" value="subir_reporte_plagio">
                            <input
                                type="hidden"
                                name="id_usuario"
                                value="<?php echo htmlspecialchars($tesista["id_usuario"]); ?>"
                            >

                            <label for="reporte-<?php echo htmlspecialchars($tesista["id_usuario"]); ?>">
                                Seleccionar reporte de plagio PDF
                            </label>

                            <input
                                type="file"
                                id="reporte-<?php echo htmlspecialchars($tesista["id_usuario"]); ?>"
                                name="reporte_plagio"
                                accept="application/pdf,.pdf"
                                required
                            >

                            <button type="submit">
                                <?php echo !empty($tesista["reporte_plagio"]["archivo"])
                                    ? "Reemplazar reporte"
                                    : "Subir reporte de plagio"; ?>
                            </button>
                        </form>
                    </section>

                    <form method="POST" class="formulario-observacion">
                        <input type="hidden" name="accion" value="guardar_observacion">
                        <input
                            type="hidden"
                            name="id_usuario"
                            value="<?php echo htmlspecialchars($tesista["id_usuario"]); ?>"
                        >

                        <label for="observacion-<?php echo htmlspecialchars($tesista["id_usuario"]); ?>">
                            Observación para el estudiante
                        </label>

                        <textarea
                            id="observacion-<?php echo htmlspecialchars($tesista["id_usuario"]); ?>"
                            name="observacion"
                            rows="7"
                            placeholder="Escribe las correcciones o recomendaciones..."
                            required
                        ><?php echo htmlspecialchars($tesista["observacion"]["observacion"] ?? ""); ?></textarea>

                        <button
                            type="submit"
                            <?php echo empty($tesista["reporte_plagio"]["archivo"]) ? "disabled" : ""; ?>
                        >
                            <?php echo !empty($tesista["observacion"]["observacion"])
                                ? "Actualizar observación"
                                : "Enviar observación"; ?>
                        </button>

                        <?php if (empty($tesista["reporte_plagio"]["archivo"])): ?>
                            <p class="aviso-obligatorio">
                                Debes subir primero el reporte de plagio para habilitar este botón.
                            </p>
                        <?php endif; ?>
                    </form>

                <?php else: ?>
                    <div class="estado-vacio">
                        El estudiante aún no ha subido la tesis completa.
                    </div>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>

    </main>

</div>

</body>
</html>
