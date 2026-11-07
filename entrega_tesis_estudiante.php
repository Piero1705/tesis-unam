<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: HTML_1_1.php");
    exit;
}

$idUsuario = (int) $_SESSION["id_usuario"];
$correoEstudiante = $_SESSION["correo_electronico"]
    ?? $_SESSION["correo_usuario"]
    ?? "estudiante@unam.edu.pe";
$codigoUniversitario = $_SESSION["codigo_universitario"] ?? "SIN-CODIGO";

$carpetaEstudiante = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

if (!is_dir($carpetaEstudiante)) {
    mkdir($carpetaEstudiante, 0777, true);
}

$rutaTesisInfo = $carpetaEstudiante . DIRECTORY_SEPARATOR . "tesis_completa.json";
$rutaObservacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "observacion_asesor.json";
$rutaAsignacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "asesor_asignado.json";

$mensaje = "";

function leerJson(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["tesis_completa"])) {
    $archivo = $_FILES["tesis_completa"];

    if ($archivo["error"] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));

        if (in_array($extension, ["pdf", "doc", "docx"], true)) {
            $tesisAnterior = leerJson($rutaTesisInfo);

            if (!empty($tesisAnterior["archivo"])) {
                $rutaAnterior = $carpetaEstudiante
                    . DIRECTORY_SEPARATOR
                    . basename($tesisAnterior["archivo"]);

                if (is_file($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            $nombreFinal = "tesis_completa_" . date("Ymd_His") . "." . $extension;
            $destino = $carpetaEstudiante . DIRECTORY_SEPARATOR . $nombreFinal;

            if (move_uploaded_file($archivo["tmp_name"], $destino)) {
                $datosTesis = [
                    "id_usuario" => $idUsuario,
                    "correo_estudiante" => $correoEstudiante,
                    "codigo_universitario" => $codigoUniversitario,
                    "archivo" => $nombreFinal,
                    "nombre_original" => basename($archivo["name"]),
                    "fecha_subida" => date("Y-m-d H:i:s"),
                    "estado" => "Pendiente de revisión del asesor"
                ];

                file_put_contents(
                    $rutaTesisInfo,
                    json_encode($datosTesis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                if (is_file($rutaObservacion)) {
                    unlink($rutaObservacion);
                }

                $mensaje = "La tesis completa fue subida correctamente.";
            } else {
                $mensaje = "No se pudo guardar el archivo.";
            }
        } else {
            $mensaje = "Solo se permiten archivos PDF, DOC y DOCX.";
        }
    } else {
        $mensaje = "No se pudo subir el archivo seleccionado.";
    }
}

$tesis = leerJson($rutaTesisInfo);
$observacion = leerJson($rutaObservacion);
$asignacion = leerJson($rutaAsignacion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrega de Tesis</title>
    <link rel="stylesheet" href="entrega_tesis_estudiante.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="marca">
            <img src="logo.png" alt="Logo UNAM">
            <div>
                <h1>UNAM</h1>
                <p>Módulo del Estudiante</p>
            </div>
        </div>

        <nav class="menu">
            <a href="dashboard_estudiante.php">☁ Subida de Archivos</a>
            <a href="entrega_tesis_estudiante.php" class="activo">📄 Entrega de Tesis</a>
            <a href="mis_tramites_estudiante.php">📁 Mis Trámites</a>
            <a href="#observacion">💬 Observaciones</a>
            <a href="#">⭐ Evaluación</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <span class="subtitulo">Trabajo de investigación</span>
            <h1>Entrega de Tesis</h1>
            <p>Sube la versión completa de tu tesis para que sea revisada por el asesor designado.</p>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-titulo">
                <div>
                    <h2>Archivo de tesis completa</h2>
                    <p>Puedes reemplazar el documento cada vez que necesites presentar una nueva versión.</p>
                </div>

                <?php if (!empty($tesis["archivo"])): ?>
                    <span class="estado recibido">Tesis subida</span>
                <?php else: ?>
                    <span class="estado pendiente">Pendiente</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($asignacion)): ?>
                <div class="asesor-asignado">
                    <span>Asesor designado</span>
                    <strong><?php echo htmlspecialchars($asignacion["correo_asesor"] ?? "Asesor asignado"); ?></strong>
                </div>
            <?php else: ?>
                <div class="aviso">
                    La Dirección de la EPISI todavía no ha designado un asesor para tu solicitud.
                </div>
            <?php endif; ?>

            <?php if (!empty($tesis["archivo"])): ?>
                <div class="archivo-actual">
                    <div>
                        <span>Documento actual</span>
                        <strong><?php echo htmlspecialchars($tesis["nombre_original"] ?? $tesis["archivo"]); ?></strong>
                        <small>Subido: <?php echo htmlspecialchars($tesis["fecha_subida"] ?? ""); ?></small>
                    </div>

                    <a
                        href="ver_tesis.php?id=<?php echo rawurlencode((string) $idUsuario); ?>"
                        target="_blank"
                    >
                        Visualizar tesis
                    </a>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="formulario-subida">
                <label for="tesis_completa">Seleccionar tesis completa</label>
                <input
                    type="file"
                    id="tesis_completa"
                    name="tesis_completa"
                    accept=".pdf,.doc,.docx"
                    required
                >

                <button type="submit">
                    <?php echo !empty($tesis["archivo"])
                        ? "Subir nueva versión"
                        : "Subir tesis completa"; ?>
                </button>
            </form>
        </section>

        <section class="panel observacion-panel" id="observacion">
            <div class="panel-titulo">
                <div>
                    <span class="subtitulo">Respuesta del asesor</span>
                    <h2>Observación de la tesis</h2>
                </div>

                <?php if (!empty($observacion["observacion"])): ?>
                    <span class="estado recibido">Observación recibida</span>
                <?php else: ?>
                    <span class="estado pendiente">Sin observación</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($observacion["observacion"])): ?>
                <article class="comentario-asesor">
                    <p>
                        <?php echo nl2br(htmlspecialchars($observacion["observacion"])); ?>
                    </p>

                    <footer>
                        <strong><?php echo htmlspecialchars($observacion["correo_asesor"] ?? "Asesor de tesis"); ?></strong>
                        <span><?php echo htmlspecialchars($observacion["fecha_observacion"] ?? ""); ?></span>
                    </footer>
                </article>
            <?php else: ?>
                <div class="estado-vacio">
                    Cuando el asesor revise tu tesis, su observación aparecerá en esta sección.
                </div>
            <?php endif; ?>
        </section>

    </main>

</div>

</body>
</html>
