<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["id_jurado"])) {
    header("Location: login_jurado.php");
    exit;
}

$idJurado = (int) $_SESSION["id_jurado"];
$correoJurado = $_SESSION["correo_jurado"] ?? "Jurado Dictaminador";
$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? $_POST["id_usuario"] ?? "");

if ($idUsuario === "") {
    header("Location: dashboard_jurado.php");
    exit;
}

$carpetaEstudiante = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante" . DIRECTORY_SEPARATOR . $idUsuario;
$rutaJurados = $carpetaEstudiante . DIRECTORY_SEPARATOR . "jurados_asignados.json";
$rutaTesis = $carpetaEstudiante . DIRECTORY_SEPARATOR . "tesis_completa.json";
$rutaSolicitud = $carpetaEstudiante . DIRECTORY_SEPARATOR . "solicitud_enviada.json";
$rutaObservaciones = $carpetaEstudiante . DIRECTORY_SEPARATOR . "observaciones_jurados.json";
$rutaEnvioSecretaria = $carpetaEstudiante . DIRECTORY_SEPARATOR . "observaciones_enviadas_secretaria.json";

function leerJsonRevision(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
}

function guardarJsonRevision(string $ruta, array $datos): bool
{
    return file_put_contents(
        $ruta,
        json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

$juradosAsignados = leerJsonRevision($rutaJurados);
$tesis = leerJsonRevision($rutaTesis);
$solicitud = leerJsonRevision($rutaSolicitud);
$observaciones = leerJsonRevision($rutaObservaciones);

if (empty($juradosAsignados["jurados"]) || empty($tesis["archivo"])) {
    header("Location: dashboard_jurado.php");
    exit;
}

$juradoAutorizado = false;

foreach ($juradosAsignados["jurados"] as $jurado) {
    if ((int) ($jurado["id_jurado"] ?? 0) === $idJurado) {
        $juradoAutorizado = true;
        break;
    }
}

if (!$juradoAutorizado) {
    header("Location: dashboard_jurado.php");
    exit;
}

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "guardar_observacion";

    if ($accion === "guardar_observacion") {
        $observacionTexto = trim($_POST["observacion"] ?? "");

        if ($observacionTexto === "") {
            $mensaje = "La observación no puede estar vacía.";
            $tipoMensaje = "error";
        } else {
            $observaciones[(string) $idJurado] = [
                "id_jurado" => $idJurado,
                "correo_jurado" => $correoJurado,
                "observacion" => $observacionTexto,
                "fecha_observacion" => date("Y-m-d H:i:s"),
                "estado" => "Registrada"
            ];

            if (guardarJsonRevision($rutaObservaciones, $observaciones)) {
                $mensaje = "Tu observación fue guardada correctamente.";
                $tipoMensaje = "exito";
            } else {
                $mensaje = "No se pudo guardar la observación.";
                $tipoMensaje = "error";
            }
        }
    }

    if ($accion === "enviar_secretaria") {
        $observacionesActuales = leerJsonRevision($rutaObservaciones);
        $totalActual = 0;

        foreach ($juradosAsignados["jurados"] as $juradoAsignado) {
            $idAsignado = (string) ((int) ($juradoAsignado["id_jurado"] ?? 0));

            if (!empty($observacionesActuales[$idAsignado]["observacion"])) {
                $totalActual++;
            }
        }

        if ($totalActual !== 3) {
            $mensaje = "Las tres observaciones deben estar registradas antes de enviarlas a Secretaría.";
            $tipoMensaje = "error";
        } elseif (is_file($rutaEnvioSecretaria)) {
            $mensaje = "Las observaciones ya fueron enviadas a Secretaría.";
            $tipoMensaje = "error";
        } else {
            $datosEnvio = [
                "id_usuario" => (int) $idUsuario,
                "codigo_universitario" => $solicitud["codigo_universitario"] ?? "",
                "correo_estudiante" => $solicitud["correo_estudiante"] ?? "",
                "tesis" => $tesis["archivo"] ?? "",
                "fecha_envio" => date("Y-m-d H:i:s"),
                "enviado_por_id_jurado" => $idJurado,
                "enviado_por_correo" => $correoJurado,
                "estado" => "Pendiente de acta de emisión de tesis",
                "observaciones" => $observacionesActuales
            ];

            if (guardarJsonRevision($rutaEnvioSecretaria, $datosEnvio)) {
                $mensaje = "Las observaciones fueron enviadas correctamente a Secretaría.";
                $tipoMensaje = "exito";
            } else {
                $mensaje = "No se pudo enviar la información a Secretaría.";
                $tipoMensaje = "error";
            }
        }
    }
}

$observaciones = leerJsonRevision($rutaObservaciones);
$miObservacion = $observaciones[(string) $idJurado]["observacion"] ?? "";
$enviadoSecretaria = is_file($rutaEnvioSecretaria);
$totalObservaciones = 0;

foreach ($juradosAsignados["jurados"] as $jurado) {
    $idAsignado = (string) ((int) ($jurado["id_jurado"] ?? 0));

    if (!empty($observaciones[$idAsignado]["observacion"])) {
        $totalObservaciones++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Tesis - Jurado</title>
    <link rel="stylesheet" href="dashboard_jurado_simple.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="marca">
            <img src="logo.png" alt="Logo UNAM">
            <div><h1>UNAM</h1><p>Módulo del Jurado</p></div>
        </div>

        <nav class="menu">
            <a href="dashboard_jurado.php">← Volver a tesis asignadas</a>
            <a href="#" class="activo">✎ Revisión conjunta</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="encabezado revision-encabezado">
            <div>
                <span class="subtitulo">Revisión conjunta</span>
                <h1>Tesis del estudiante</h1>
                <p>
                    Código:
                    <strong><?php echo htmlspecialchars($solicitud["codigo_universitario"] ?? "Sin código"); ?></strong>
                    ·
                    <?php echo htmlspecialchars($solicitud["correo_estudiante"] ?? "Sin correo"); ?>
                </p>
            </div>

            <div class="contador-general">
                <span>Observaciones</span>
                <strong><?php echo $totalObservaciones; ?>/3</strong>
            </div>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="panel documento-principal">
            <div>
                <span class="subtitulo">Documento entregado</span>
                <h2><?php echo htmlspecialchars($tesis["nombre_original"] ?? $tesis["archivo"]); ?></h2>
                <small>Fecha: <?php echo htmlspecialchars($tesis["fecha_subida"] ?? ""); ?></small>
            </div>

            <a class="boton-principal" href="ver_tesis.php?id=<?php echo rawurlencode($idUsuario); ?>" target="_blank">
                Visualizar tesis
            </a>
        </section>

        <section class="rejilla-revision">
            <article class="panel">
                <div class="panel-titulo">
                    <div>
                        <span class="subtitulo">Evaluación personal</span>
                        <h2>Mi observación</h2>
                    </div>

                    <?php if ($miObservacion !== ""): ?>
                        <span class="estado recibido">Registrada</span>
                    <?php else: ?>
                        <span class="estado pendiente">Pendiente</span>
                    <?php endif; ?>
                </div>

                <form method="POST" class="formulario-observacion">
                    <input type="hidden" name="accion" value="guardar_observacion">
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($idUsuario); ?>">

                    <label for="observacion">Comentario para el estudiante</label>

                    <textarea id="observacion" name="observacion" rows="9" placeholder="Escribe aquí tus observaciones..." required><?php echo htmlspecialchars($miObservacion); ?></textarea>

                    <button type="submit" class="boton-principal">
                        <?php echo $miObservacion !== "" ? "Actualizar observación" : "Guardar observación"; ?>
                    </button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-titulo">
                    <div>
                        <span class="subtitulo">Evaluación compartida</span>
                        <h2>Observaciones de los tres jurados</h2>
                    </div>
                </div>

                <div class="lista-observaciones">
                    <?php foreach ($juradosAsignados["jurados"] as $indice => $jurado): ?>
                        <?php
                        $idAsignado = (string) ((int) ($jurado["id_jurado"] ?? 0));
                        $observacionJurado = $observaciones[$idAsignado] ?? [];
                        $esMiObservacion = (int) $idAsignado === $idJurado;
                        ?>

                        <article class="tarjeta-observacion">
                            <div class="cabecera-observacion">
                                <div>
                                    <strong>Jurado <?php echo $indice + 1; ?><?php echo $esMiObservacion ? " · Tú" : ""; ?></strong>
                                    <small><?php echo htmlspecialchars($jurado["correo_electronico"] ?? $observacionJurado["correo_jurado"] ?? "Jurado"); ?></small>
                                </div>

                                <?php if (!empty($observacionJurado["observacion"])): ?>
                                    <span class="estado recibido">Registrada</span>
                                <?php else: ?>
                                    <span class="estado pendiente">Pendiente</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($observacionJurado["observacion"])): ?>
                                <p><?php echo nl2br(htmlspecialchars($observacionJurado["observacion"])); ?></p>
                                <small class="fecha-observacion"><?php echo htmlspecialchars($observacionJurado["fecha_observacion"] ?? ""); ?></small>
                            <?php else: ?>
                                <p class="texto-pendiente">Este jurado todavía no ha registrado una observación.</p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalObservaciones === 3): ?>
                    <form method="POST" class="formulario-envio-secretaria">
                        <input type="hidden" name="accion" value="enviar_secretaria">
                        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($idUsuario); ?>">

                        <button
                            type="submit"
                            class="boton-enviar-secretaria"
                            <?php echo $enviadoSecretaria ? "disabled" : ""; ?>
                        >
                            <?php echo $enviadoSecretaria
                                ? "Observaciones enviadas a Secretaría"
                                : "Enviar observaciones a Secretaría"; ?>
                        </button>
                    </form>

                    <p class="ayuda-envio">
                        El último jurado que complete la evaluación puede enviar las tres observaciones.
                    </p>
                <?php else: ?>
                    <p class="ayuda-envio">
                        El envío se habilitará cuando las tres observaciones estén registradas.
                    </p>
                <?php endif; ?>
            </article>
        </section>
    </main>
</div>
</body>
</html>
