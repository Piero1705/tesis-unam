<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_director"])) {
    header("Location: login_direccion.php");
    exit;
}

$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? "");

if ($idUsuario === "") {
    header("Location: dashboard_direccion.php");
    exit;
}

$carpetaEstudiante = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaSolicitud = $carpetaEstudiante . DIRECTORY_SEPARATOR . "solicitud_enviada.json";
$rutaMetadata = $carpetaEstudiante . DIRECTORY_SEPARATOR . "metadata.json";
$rutaAsignacion = $carpetaEstudiante . DIRECTORY_SEPARATOR . "asesor_asignado.json";

if (!is_file($rutaSolicitud) || !is_file($rutaMetadata)) {
    header("Location: dashboard_direccion.php");
    exit;
}

$solicitud = json_decode(file_get_contents($rutaSolicitud), true);
$metadata = json_decode(file_get_contents($rutaMetadata), true);

if (!is_array($solicitud) || !is_array($metadata)) {
    header("Location: dashboard_direccion.php");
    exit;
}

$codigoEstudiante = $solicitud["codigo_universitario"] ?? "Sin código";
$correoEstudiante = $solicitud["correo_estudiante"] ?? "Sin correo";

$asesores = [];

$sql = "SELECT id_asesor, correo_electronico, codigo_universitario
        FROM asesor
        ORDER BY correo_electronico ASC";

$resultado = mysqli_query($conexion, $sql);

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $asesores[] = $fila;
    }
}

$asignacionActual = [];

if (is_file($rutaAsignacion)) {
    $datos = json_decode(file_get_contents($rutaAsignacion), true);
    $asignacionActual = is_array($datos) ? $datos : [];
}

$mensaje = $_SESSION["mensaje_designacion"] ?? "";
unset($_SESSION["mensaje_designacion"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designar Asesor de Tesis</title>
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
            <a href="dashboard_direccion.php">
                ← Volver a solicitudes
            </a>

            <a href="#" class="activo">
                👤 Designar asesor
            </a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <div>
                <span class="subtitulo">Aprobación de solicitud</span>
                <h1>Designar asesor de tesis</h1>
                <p>
                    Selecciona un asesor registrado en el sistema de la UNAM.
                </p>
            </div>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje error">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="datos-estudiante">
            <article>
                <span>Código universitario</span>
                <strong><?php echo htmlspecialchars($codigoEstudiante); ?></strong>
            </article>

            <article>
                <span>Correo electrónico</span>
                <strong><?php echo htmlspecialchars($correoEstudiante); ?></strong>
            </article>
        </section>

        <section class="panel">
            <div class="panel-titulo">
                <div>
                    <h2>Asesores disponibles</h2>
                    <p>El asesor seleccionado quedará vinculado a la solicitud del estudiante.</p>
                </div>
            </div>

            <?php if (count($asesores) === 0): ?>
                <div class="estado-vacio">
                    No existen asesores registrados en la tabla asesor.
                </div>
            <?php else: ?>
                <form
                    action="procesar_designacion_asesor.php"
                    method="POST"
                    class="formulario-designacion"
                >
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($idUsuario); ?>">

                    <div class="lista-asesores">
                        <?php foreach ($asesores as $asesor): ?>
                            <label class="tarjeta-asesor">
                                <input
                                    type="radio"
                                    name="id_asesor"
                                    value="<?php echo (int) $asesor["id_asesor"]; ?>"
                                    <?php echo (
                                        isset($asignacionActual["id_asesor"]) &&
                                        (int) $asignacionActual["id_asesor"] === (int) $asesor["id_asesor"]
                                    ) ? "checked" : ""; ?>
                                    required
                                >

                                <span class="avatar-asesor">A</span>

                                <span class="informacion-asesor">
                                    <strong>
                                        <?php echo htmlspecialchars($asesor["correo_electronico"]); ?>
                                    </strong>

                                    <small>
                                        Código:
                                        <?php echo htmlspecialchars(
                                            $asesor["codigo_universitario"] ?? "Sin código"
                                        ); ?>
                                    </small>
                                </span>

                                <span class="seleccion">Seleccionar</span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="boton-confirmar">
                        Confirmar y designar asesor
                    </button>
                </form>
            <?php endif; ?>
        </section>

    </main>

</div>

</body>
</html>
