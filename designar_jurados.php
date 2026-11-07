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

$carpeta = __DIR__
    . DIRECTORY_SEPARATOR
    . "tramites_estudiante"
    . DIRECTORY_SEPARATOR
    . $idUsuario;

$rutaSolicitud = $carpeta . DIRECTORY_SEPARATOR . "solicitud_enviada.json";
$rutaTesis = $carpeta . DIRECTORY_SEPARATOR . "tesis_completa.json";
$rutaJurados = $carpeta . DIRECTORY_SEPARATOR . "jurados_asignados.json";

if (!is_file($rutaSolicitud) || !is_file($rutaTesis)) {
    $_SESSION["mensaje_direccion"] = "El estudiante todavía no ha entregado su tesis completa.";
    $_SESSION["tipo_mensaje_direccion"] = "error";
    header("Location: dashboard_direccion.php");
    exit;
}

$solicitud = json_decode(file_get_contents($rutaSolicitud), true);
$seleccionados = [];

if (is_file($rutaJurados)) {
    $datosActuales = json_decode(file_get_contents($rutaJurados), true);

    if (is_array($datosActuales["jurados"] ?? null)) {
        foreach ($datosActuales["jurados"] as $juradoActual) {
            $seleccionados[] = (int) ($juradoActual["id_jurado"] ?? 0);
        }
    }
}

$jurados = [];
$resultado = mysqli_query(
    $conexion,
    "SELECT id_jurado, correo_electronico
     FROM jurado
     ORDER BY correo_electronico ASC"
);

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $jurados[] = $fila;
    }
}

$mensaje = $_SESSION["mensaje_jurados"] ?? "";
unset($_SESSION["mensaje_jurados"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designar Jurados</title>
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
            <a href="#" class="activo">⚖ Designar jurados</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <div>
                <span class="subtitulo">Evaluación de tesis</span>
                <h1>Designar Jurados Dictaminadores</h1>
                <p>Selecciona exactamente tres jurados registrados en el sistema de la UNAM.</p>
            </div>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <section class="datos-estudiante">
            <article>
                <span>Código universitario</span>
                <strong><?php echo htmlspecialchars($solicitud["codigo_universitario"] ?? "Sin código"); ?></strong>
            </article>

            <article>
                <span>Correo electrónico</span>
                <strong><?php echo htmlspecialchars($solicitud["correo_estudiante"] ?? "Sin correo"); ?></strong>
            </article>
        </section>

        <section class="panel">
            <div class="panel-titulo">
                <div>
                    <h2>Jurados disponibles</h2>
                    <p>Debes seleccionar tres jurados diferentes.</p>
                </div>
            </div>

            <?php if (count($jurados) < 3): ?>
                <div class="estado-vacio">
                    Debes registrar al menos tres jurados en la tabla jurado.
                </div>
            <?php else: ?>
                <form
                    action="procesar_designacion_jurados.php"
                    method="POST"
                    class="formulario-designacion"
                >
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($idUsuario); ?>">

                    <div class="lista-asesores">
                        <?php foreach ($jurados as $jurado): ?>
                            <label class="tarjeta-asesor">
                                <input
                                    type="checkbox"
                                    name="jurados[]"
                                    value="<?php echo (int) $jurado["id_jurado"]; ?>"
                                    <?php echo in_array((int) $jurado["id_jurado"], $seleccionados, true)
                                        ? "checked"
                                        : ""; ?>
                                >

                                <span class="avatar-asesor">J</span>

                                <span class="informacion-asesor">
                                    <strong>
                                        <?php echo htmlspecialchars($jurado["correo_electronico"]); ?>
                                    </strong>

                                    <small>
                                        ID de jurado: <?php echo (int) $jurado["id_jurado"]; ?>
                                    </small>
                                </span>

                                <span class="seleccion">Seleccionar</span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <p class="contador-seleccion" id="contador-seleccion">
                        Seleccionados: 0 de 3
                    </p>

                    <button type="submit" class="boton-confirmar">
                        Confirmar jurados
                    </button>
                </form>
            <?php endif; ?>
        </section>

    </main>

</div>

<script>
const casillas = Array.from(
    document.querySelectorAll('input[name="jurados[]"]')
);
const contador = document.getElementById("contador-seleccion");

function actualizarJurados() {
    const seleccionadas = casillas.filter(casilla => casilla.checked);

    casillas.forEach(casilla => {
        casilla.disabled = seleccionadas.length >= 3 && !casilla.checked;
    });

    if (contador) {
        contador.textContent = `Seleccionados: ${seleccionadas.length} de 3`;
    }
}

casillas.forEach(casilla => {
    casilla.addEventListener("change", actualizarJurados);
});

actualizarJurados();
</script>

</body>
</html>
