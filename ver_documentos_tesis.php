<?php
session_start();

if (!isset($_SESSION["id_director"])) {
    header("Location: login_direccion.php");
    exit;
}

$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? "");

if ($idUsuario === "") {
    header("Location: dashboard_direccion.php");
    exit;
}

$carpeta = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

function leerDocumento(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
}

$tesis = leerDocumento($carpeta . DIRECTORY_SEPARATOR . "tesis_completa.json");
$reporte = leerDocumento($carpeta . DIRECTORY_SEPARATOR . "reporte_plagio.json");
$solicitud = leerDocumento($carpeta . DIRECTORY_SEPARATOR . "solicitud_enviada.json");

if (empty($tesis["archivo"])) {
    header("Location: dashboard_direccion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de la Tesis</title>
    <link rel="stylesheet" href="dashboard_direccion.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="marca">
            <img src="logo.png" alt="Logo UNAM">
            <div>
                <h1>UNAM</h1>
                <p>Dirección de la EPISI</p>
            </div>
        </div>

        <nav class="menu">
            <a href="dashboard_direccion.php">← Volver al dashboard</a>
            <a href="#" class="activo">📄 Documentos de tesis</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <div>
                <span class="subtitulo">Revisión documental</span>
                <h1>Tesis y reporte de plagio</h1>
                <p>
                    Código:
                    <strong>
                        <?php echo htmlspecialchars($solicitud["codigo_universitario"] ?? "Sin código"); ?>
                    </strong>
                    ·
                    <?php echo htmlspecialchars($solicitud["correo_estudiante"] ?? "Sin correo"); ?>
                </p>
            </div>
        </header>

        <section class="documentos-grid">

            <article class="tarjeta-documento-direccion">
                <span class="estado aprobado">Tesis entregada</span>
                <h2>Documento completo de tesis</h2>

                <p>
                    <?php echo htmlspecialchars(
                        $tesis["nombre_original"] ?? $tesis["archivo"]
                    ); ?>
                </p>

                <small>
                    Fecha:
                    <?php echo htmlspecialchars($tesis["fecha_subida"] ?? ""); ?>
                </small>

                <a
                    class="boton-aprobar"
                    href="ver_tesis.php?id=<?php echo rawurlencode($idUsuario); ?>"
                    target="_blank"
                >
                    Abrir tesis
                </a>
            </article>

            <article class="tarjeta-documento-direccion">
                <?php if (!empty($reporte["archivo"])): ?>
                    <span class="estado aprobado">Reporte presentado</span>
                    <h2>Reporte de plagio</h2>

                    <p>
                        <?php echo htmlspecialchars(
                            $reporte["nombre_original"] ?? $reporte["archivo"]
                        ); ?>
                    </p>

                    <small>
                        Fecha:
                        <?php echo htmlspecialchars($reporte["fecha_subida"] ?? ""); ?>
                    </small>

                    <a
                        class="boton-aprobar"
                        href="ver_reporte_plagio.php?id=<?php echo rawurlencode($idUsuario); ?>"
                        target="_blank"
                    >
                        Abrir reporte de plagio
                    </a>
                <?php else: ?>
                    <span class="estado pendiente">Reporte pendiente</span>
                    <h2>Reporte de plagio</h2>
                    <p>El asesor todavía no ha adjuntado el reporte obligatorio.</p>
                <?php endif; ?>
            </article>

        </section>

    </main>

</div>

</body>
</html>
