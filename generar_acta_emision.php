<?php
session_start();

if (!isset($_SESSION["id_secretaria"])) {
    header("Location: login_secretaria.php");
    exit;
}

$idUsuario = preg_replace("/[^0-9]/", "", $_GET["id"] ?? $_POST["id_usuario"] ?? "");

if ($idUsuario === "") {
    header("Location: lista_expedientes.php");
    exit;
}

$carpeta = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante" . DIRECTORY_SEPARATOR . $idUsuario;
$rutaEnvio = $carpeta . DIRECTORY_SEPARATOR . "observaciones_enviadas_secretaria.json";
$rutaActa = $carpeta . DIRECTORY_SEPARATOR . "acta_emision_tesis.json";

function leerJsonActa(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
}

$envio = leerJsonActa($rutaEnvio);

if (empty($envio)) {
    header("Location: lista_expedientes.php");
    exit;
}

$acta = leerJsonActa($rutaActa);
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numeroActa = trim($_POST["numero_acta"] ?? "");
    $fechaEmision = trim($_POST["fecha_emision"] ?? "");
    $tituloTesis = trim($_POST["titulo_tesis"] ?? "");
    $resultado = trim($_POST["resultado"] ?? "");
    $observacionFinal = trim($_POST["observacion_final"] ?? "");

    if ($numeroActa === "" || $fechaEmision === "" || $tituloTesis === "" || $resultado === "") {
        $mensaje = "Complete todos los campos obligatorios.";
    } else {
        $acta = [
            "id_usuario" => (int) $idUsuario,
            "codigo_universitario" => $envio["codigo_universitario"] ?? "",
            "correo_estudiante" => $envio["correo_estudiante"] ?? "",
            "numero_acta" => $numeroActa,
            "fecha_emision" => $fechaEmision,
            "titulo_tesis" => $tituloTesis,
            "resultado" => $resultado,
            "observacion_final" => $observacionFinal,
            "fecha_registro" => date("Y-m-d H:i:s"),
            "id_secretaria" => (int) $_SESSION["id_secretaria"],
            "estado" => "Acta de emisión de tesis generada"
        ];

        file_put_contents(
            $rutaActa,
            json_encode($acta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $mensaje = "El acta de emisión de tesis fue generada correctamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Emisión de Tesis</title>
    <link rel="stylesheet" href="dashboard_secretaria.css">
    <link rel="stylesheet" href="acta_emision_tesis.css">
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
            <a href="dashboard_secretaria.php">📁 Trámites recibidos</a>
            <a href="lista_expedientes.php">← Lista de Expedientes</a>
            <a href="#" class="activo">📝 Acta de emisión</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="encabezado">
            <div>
                <span class="subtitulo">Documento administrativo</span>
                <h1>Acta de Emisión de Tesis</h1>
                <p>
                    Código:
                    <strong><?php echo htmlspecialchars($envio["codigo_universitario"] ?? ""); ?></strong>
                    ·
                    <?php echo htmlspecialchars($envio["correo_estudiante"] ?? ""); ?>
                </p>
            </div>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje-acta"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <section class="panel formulario-acta-panel">
            <form method="POST" class="formulario-acta">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($idUsuario); ?>">

                <label>
                    Número de acta *
                    <input
                        type="text"
                        name="numero_acta"
                        value="<?php echo htmlspecialchars($acta["numero_acta"] ?? ""); ?>"
                        placeholder="ACTA-EPISI-001-2026"
                        required
                    >
                </label>

                <label>
                    Fecha de emisión *
                    <input
                        type="date"
                        name="fecha_emision"
                        value="<?php echo htmlspecialchars($acta["fecha_emision"] ?? date("Y-m-d")); ?>"
                        required
                    >
                </label>

                <label class="campo-completo">
                    Título de la tesis *
                    <input
                        type="text"
                        name="titulo_tesis"
                        value="<?php echo htmlspecialchars($acta["titulo_tesis"] ?? ""); ?>"
                        required
                    >
                </label>

                <label>
                    Resultado *
                    <select name="resultado" required>
                        <option value="">Seleccionar</option>
                        <option value="APROBADA" <?php echo (($acta["resultado"] ?? "") === "APROBADA") ? "selected" : ""; ?>>
                            Aprobada
                        </option>
                        <option value="APROBADA CON OBSERVACIONES" <?php echo (($acta["resultado"] ?? "") === "APROBADA CON OBSERVACIONES") ? "selected" : ""; ?>>
                            Aprobada con observaciones
                        </option>
                        <option value="PENDIENTE DE CORRECCIÓN" <?php echo (($acta["resultado"] ?? "") === "PENDIENTE DE CORRECCIÓN") ? "selected" : ""; ?>>
                            Pendiente de corrección
                        </option>
                    </select>
                </label>

                <label class="campo-completo">
                    Observación final
                    <textarea
                        name="observacion_final"
                        rows="6"
                        placeholder="Ingrese una observación administrativa final..."
                    ><?php echo htmlspecialchars($acta["observacion_final"] ?? ""); ?></textarea>
                </label>

                <div class="campo-completo">
                    <button type="submit" class="boton-generar">
                        <?php echo empty($acta)
                            ? "Generar Acta de Emisión"
                            : "Actualizar Acta de Emisión"; ?>
                    </button>
                </div>
            </form>
        </section>

        <section class="panel">
            <h2>Observaciones de los jurados</h2>

            <div class="lista-observaciones-acta">
                <?php foreach (($envio["observaciones"] ?? []) as $observacion): ?>
                    <article>
                        <strong><?php echo htmlspecialchars($observacion["correo_jurado"] ?? "Jurado"); ?></strong>
                        <p><?php echo nl2br(htmlspecialchars($observacion["observacion"] ?? "")); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>
