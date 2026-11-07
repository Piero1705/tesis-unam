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

$carpetaArchivos = __DIR__ . DIRECTORY_SEPARATOR . "archivos_estudiante";
$carpetaBaseTramites = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante";
$carpetaTramites = $carpetaBaseTramites . DIRECTORY_SEPARATOR . $idUsuario;
$archivoMetadata = $carpetaTramites . DIRECTORY_SEPARATOR . "metadata.json";
$archivoSolicitud = $carpetaTramites . DIRECTORY_SEPARATOR . "solicitud_enviada.json";

foreach ([$carpetaArchivos, $carpetaBaseTramites, $carpetaTramites] as $carpeta) {
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }
}

$mensaje = "";

$tipos = [
    "grado_bachiller" => "Grado de Bachiller",
    "proyecto_tesis" => "Proyecto de Tesis",
    "acta_sustentacion" => "Acta de Sustentación"
];

function cargarMetadata(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
}

function guardarMetadata(string $ruta, array $datos): bool
{
    return file_put_contents(
        $ruta,
        json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

$metadata = cargarMetadata($archivoMetadata);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "subir_documento";

    if ($accion === "enviar_solicitud") {
        $completos = true;

        foreach (array_keys($tipos) as $tipoRequerido) {
            if (empty($metadata[$tipoRequerido]["archivo"])) {
                $completos = false;
                break;
            }
        }

        if (!$completos) {
            $mensaje = "Debes adjuntar los tres documentos antes de enviar el trámite.";
        } else {
            file_put_contents(
                $archivoSolicitud,
                json_encode(
                    [
                        "id_usuario" => $idUsuario,
                        "correo_estudiante" => $correoEstudiante,
                        "codigo_universitario" => $codigoUniversitario,
                        "fecha_envio" => date("Y-m-d H:i:s"),
                        "estado" => "Enviado a Secretaría"
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                )
            );

            $mensaje = "El trámite fue enviado correctamente a Secretaría.";
        }
    } else {
        $tipo = $_POST["tipo_tramite"] ?? "";
        $origen = $_POST["origen"] ?? "";

        if (!isset($tipos[$tipo])) {
            $mensaje = "Trámite inválido.";
        } else {
            $nombreFinal = "";
            $nombreOriginal = "";

            if ($origen === "computadora" && isset($_FILES["archivo_nuevo"])) {
                $archivo = $_FILES["archivo_nuevo"];

                if ($archivo["error"] === UPLOAD_ERR_OK) {
                    $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
                    $nombreOriginal = basename($archivo["name"]);

                    if (in_array($extension, ["pdf", "doc", "docx"], true)) {
                        $nombreFinal = $tipo . "_" . date("Ymd_His") . "." . $extension;
                        $destino = $carpetaTramites . DIRECTORY_SEPARATOR . $nombreFinal;

                        if (!move_uploaded_file($archivo["tmp_name"], $destino)) {
                            $nombreFinal = "";
                        }
                    }
                }
            }

            if ($origen === "historial") {
                $seleccionado = basename($_POST["archivo_historial"] ?? "");
                $origenArchivo = $carpetaArchivos . DIRECTORY_SEPARATOR . $seleccionado;

                if ($seleccionado !== "" && is_file($origenArchivo)) {
                    $extension = strtolower(pathinfo($seleccionado, PATHINFO_EXTENSION));
                    $nombreOriginal = $seleccionado;
                    $nombreFinal = $tipo . "_" . date("Ymd_His") . "." . $extension;
                    $destino = $carpetaTramites . DIRECTORY_SEPARATOR . $nombreFinal;

                    if (!copy($origenArchivo, $destino)) {
                        $nombreFinal = "";
                    }
                }
            }

            if ($nombreFinal !== "") {
                if (!empty($metadata[$tipo]["archivo"])) {
                    $archivoAnterior = basename($metadata[$tipo]["archivo"]);
                    $rutaAnterior = $carpetaTramites . DIRECTORY_SEPARATOR . $archivoAnterior;

                    if (is_file($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }

                $metadata[$tipo] = [
                    "tipo" => $tipo,
                    "titulo" => $tipos[$tipo],
                    "archivo" => $nombreFinal,
                    "nombre_original" => $nombreOriginal,
                    "id_usuario" => $idUsuario,
                    "correo_estudiante" => $correoEstudiante,
                    "codigo_universitario" => $codigoUniversitario,
                    "fecha_subida" => date("Y-m-d H:i:s"),
                    "estado" => "Pendiente de revisión"
                ];

                guardarMetadata($archivoMetadata, $metadata);

                if (is_file($archivoSolicitud)) {
                    unlink($archivoSolicitud);
                }

                $mensaje = "Documento guardado correctamente. Puedes reemplazarlo nuevamente si lo necesitas.";
            } else {
                $mensaje = "No se pudo guardar el documento. Verifica que sea PDF, DOC o DOCX.";
            }
        }
    }

    $metadata = cargarMetadata($archivoMetadata);
}

$historial = [];

if (is_dir($carpetaArchivos)) {
    foreach (scandir($carpetaArchivos) as $archivo) {
        if (
            $archivo !== "." &&
            $archivo !== ".." &&
            is_file($carpetaArchivos . DIRECTORY_SEPARATOR . $archivo)
        ) {
            $historial[] = $archivo;
        }
    }
}

$documentosCompletos = 0;

foreach (array_keys($tipos) as $tipo) {
    if (!empty($metadata[$tipo]["archivo"])) {
        $documentosCompletos++;
    }
}

$solicitudEnviada = is_file($archivoSolicitud);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Trámites</title>
    <link rel="stylesheet" href="mis_tramites_estudiante.css">
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
            <a href="entrega_tesis_estudiante.php">📄 Entrega de Tesis</a>
            <a href="mis_tramites_estudiante.php" class="activo">📁 Mis Trámites</a>
            <a href="#">💬 Observaciones</a>
            <a href="#">⭐ Evaluación</a>
        </nav>
    </aside>

    <main class="contenido">

        <div class="encabezado-pagina">
            <span class="subtitulo">Gestión documental</span>
            <h1>Mis Trámites</h1>

            <p>
                Adjunta o reemplaza los tres documentos requeridos antes de enviar tu solicitud.
            </p>
        </div>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php foreach ($tipos as $clave => $titulo): ?>
            <section class="tramite">
                <div class="titulo-tramite">
                    <h2><?php echo htmlspecialchars($titulo); ?></h2>

                    <?php if (!empty($metadata[$clave]["archivo"])): ?>
                        <span class="estado-documento">Documento adjuntado</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($metadata[$clave]["archivo"])): ?>
                    <p class="archivo-actual">
                        Archivo actual:

                        <a
                            href="tramites_estudiante/<?php echo rawurlencode((string) $idUsuario); ?>/<?php echo rawurlencode($metadata[$clave]["archivo"]); ?>"
                            target="_blank"
                        >
                            <?php echo htmlspecialchars($metadata[$clave]["archivo"]); ?>
                        </a>
                    </p>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="subir_documento">
                    <input
                        type="hidden"
                        name="tipo_tramite"
                        value="<?php echo htmlspecialchars($clave); ?>"
                    >

                    <label class="opcion">
                        <input type="radio" name="origen" value="computadora" checked>
                        Subir desde mi computadora
                    </label>

                    <input
                        type="file"
                        name="archivo_nuevo"
                        accept=".pdf,.doc,.docx"
                    >

                    <label class="opcion">
                        <input type="radio" name="origen" value="historial">
                        Elegir archivo anterior
                    </label>

                    <select name="archivo_historial">
                        <option value="">Seleccionar archivo</option>

                        <?php foreach ($historial as $archivo): ?>
                            <option value="<?php echo htmlspecialchars($archivo); ?>">
                                <?php echo htmlspecialchars($archivo); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">
                        <?php echo !empty($metadata[$clave]["archivo"])
                            ? "Reemplazar Documento"
                            : "Guardar Documento"; ?>
                    </button>
                </form>
            </section>
        <?php endforeach; ?>

        <section class="envio-final">
            <div>
                <span class="subtitulo">Paso final</span>
                <h2>Enviar trámite para solicitud</h2>

                <p>
                    Documentos completados:
                    <strong><?php echo $documentosCompletos; ?>/3</strong>
                </p>
            </div>

            <form method="POST">
                <input type="hidden" name="accion" value="enviar_solicitud">

                <button
                    type="submit"
                    class="boton-final"
                    <?php echo $documentosCompletos < 3 ? "disabled" : ""; ?>
                >
                    <?php echo $solicitudEnviada
                        ? "Trámite enviado a Secretaría"
                        : "Adjuntar archivos Trámite para Solicitud"; ?>
                </button>
            </form>
        </section>

    </main>

</div>

</body>
</html>
