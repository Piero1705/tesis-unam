<?php
session_start();

if (!isset($_SESSION["id_secretaria"])) {
    header("Location: login_secretaria.php");
    exit;
}

$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? "");

if ($idUsuario === "") {
    header("Location: dashboard_secretaria.php");
    exit;
}

$carpetaEstudiante = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaMetadata = $carpetaEstudiante . DIRECTORY_SEPARATOR . "metadata.json";
$rutaSolicitud = $carpetaEstudiante . DIRECTORY_SEPARATOR . "solicitud_enviada.json";

if (!is_file($rutaMetadata)) {
    header("Location: dashboard_secretaria.php");
    exit;
}

function cargarMetadata(string $ruta): array
{
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

$documentos = cargarMetadata($rutaMetadata);
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";
    $tipo = $_POST["tipo_documento"] ?? "";

    $tiposPermitidos = [
        "grado_bachiller",
        "proyecto_tesis",
        "acta_sustentacion"
    ];

    if ($accion === "eliminar" && in_array($tipo, $tiposPermitidos, true)) {
        $item = $documentos[$tipo] ?? null;

        if ($item && !empty($item["archivo"])) {
            $archivo = basename($item["archivo"]);
            $rutaArchivo = $carpetaEstudiante . DIRECTORY_SEPARATOR . $archivo;

            if (is_file($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            unset($documentos[$tipo]);
            guardarMetadata($rutaMetadata, $documentos);

            if (is_file($rutaSolicitud)) {
                unlink($rutaSolicitud);
            }

            $mensaje = "El archivo fue eliminado. El estudiante ya puede volver a subirlo.";
        }
    }
}

$documentos = cargarMetadata($rutaMetadata);
$primerDocumento = reset($documentos);

$correo = $primerDocumento["correo_estudiante"] ?? "Sin correo";
$codigo = $primerDocumento["codigo_universitario"] ?? "Sin código";

$orden = [
    "grado_bachiller",
    "proyecto_tesis",
    "acta_sustentacion"
];

$titulos = [
    "grado_bachiller" => "Grado de Bachiller",
    "proyecto_tesis" => "Proyecto de Tesis",
    "acta_sustentacion" => "Acta de Sustentación"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trámites del estudiante</title>
    <link rel="stylesheet" href="dashboard_secretaria.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="marca">
            <img src="logo.png" alt="Logo UNAM">

            <div>
                <h1>UNAM</h1>
                <p>Módulo de Secretaría</p>
            </div>
        </div>

        <nav class="menu">
            <a href="dashboard_secretaria.php">← Volver al dashboard</a>
            <a href="#" class="activo">📄 Documentos del estudiante</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <div>
                <span class="subtitulo">Expediente digital</span>
                <h1>Dashboard del estudiante</h1>

                <p>
                    Código:
                    <strong><?php echo htmlspecialchars($codigo); ?></strong>
                    · <?php echo htmlspecialchars($correo); ?>
                </p>
            </div>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje-accion">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-titulo">
                <div>
                    <h2>Archivos subidos</h2>
                    <p>
                        Puedes visualizar o eliminar un archivo para que el tesista lo vuelva a presentar.
                    </p>
                </div>
            </div>

            <div class="rejilla-documentos">
                <?php foreach ($orden as $tipo): ?>
                    <?php $item = $documentos[$tipo] ?? null; ?>

                    <article class="tarjeta-documento">
                        <div class="icono-documento">📄</div>

                        <?php if ($item): ?>
                            <span class="estado listo">Recibido</span>

                            <h3>
                                <?php echo htmlspecialchars($item["titulo"] ?? $titulos[$tipo]); ?>
                            </h3>

                            <p>
                                Fecha de subida:
                                <?php echo htmlspecialchars($item["fecha_subida"] ?? "Sin fecha"); ?>
                            </p>

                            <p class="nombre-archivo">
                                <?php echo htmlspecialchars(
                                    !empty($item["nombre_original"])
                                        ? $item["nombre_original"]
                                        : $item["archivo"]
                                ); ?>
                            </p>

                            <div class="acciones-documento">
                                <a
                                    class="boton-ver"
                                    href="tramites_estudiante/<?php echo rawurlencode($idUsuario); ?>/<?php echo rawurlencode($item["archivo"]); ?>"
                                    target="_blank"
                                >
                                    Visualizar archivo
                                </a>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este archivo? El estudiante tendrá que volver a subirlo.');"
                                >
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input
                                        type="hidden"
                                        name="tipo_documento"
                                        value="<?php echo htmlspecialchars($tipo); ?>"
                                    >

                                    <button type="submit" class="boton-eliminar">
                                        Eliminar archivo
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <span class="estado pendiente">Pendiente</span>

                            <h3><?php echo htmlspecialchars($titulos[$tipo]); ?></h3>

                            <p>
                                El estudiante debe volver a adjuntar este documento.
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

</div>

</body>
</html>
