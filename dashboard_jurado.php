<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["id_jurado"])) {
    header("Location: login_jurado.php");
    exit;
}

$idJurado = (int) $_SESSION["id_jurado"];
$correoJurado = $_SESSION["correo_jurado"] ?? "Jurado Dictaminador";
$carpetaBase = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante";
$tesisAsignadas = [];

function leerJsonJurado(string $ruta): array
{
    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode(file_get_contents($ruta), true);
    return is_array($datos) ? $datos : [];
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
        $juradosAsignados = leerJsonJurado($carpetaEstudiante . DIRECTORY_SEPARATOR . "jurados_asignados.json");
        $tesis = leerJsonJurado($carpetaEstudiante . DIRECTORY_SEPARATOR . "tesis_completa.json");
        $solicitud = leerJsonJurado($carpetaEstudiante . DIRECTORY_SEPARATOR . "solicitud_enviada.json");

        if (empty($juradosAsignados["jurados"]) || empty($tesis["archivo"])) {
            continue;
        }

        $juradoSeleccionado = false;

        foreach ($juradosAsignados["jurados"] as $jurado) {
            if ((int) ($jurado["id_jurado"] ?? 0) === $idJurado) {
                $juradoSeleccionado = true;
                break;
            }
        }

        if (!$juradoSeleccionado) {
            continue;
        }

        $observaciones = leerJsonJurado($carpetaEstudiante . DIRECTORY_SEPARATOR . "observaciones_jurados.json");
        $totalObservaciones = 0;

        foreach ($juradosAsignados["jurados"] as $jurado) {
            $idAsignado = (string) ((int) ($jurado["id_jurado"] ?? 0));

            if (!empty($observaciones[$idAsignado]["observacion"])) {
                $totalObservaciones++;
            }
        }

        $tesisAsignadas[] = [
            "id_usuario" => $idSeguro,
            "codigo" => $solicitud["codigo_universitario"] ?? "Sin código",
            "correo" => $solicitud["correo_estudiante"] ?? "Sin correo",
            "nombre_archivo" => $tesis["nombre_original"] ?? $tesis["archivo"],
            "fecha_subida" => $tesis["fecha_subida"] ?? "",
            "total_observaciones" => $totalObservaciones
        ];
    }
}

usort($tesisAsignadas, fn(array $a, array $b): int => strcmp($b["fecha_subida"], $a["fecha_subida"]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Jurado Dictaminador</title>
    <link rel="stylesheet" href="dashboard_jurado_simple.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="marca">
            <img src="logo.png" alt="Logo UNAM">
            <div><h1>UNAM</h1><p>Módulo del Jurado</p></div>
        </div>

        <div class="perfil">
            <span>Jurado Dictaminador</span>
            <strong><?php echo htmlspecialchars((string) $correoJurado); ?></strong>
        </div>

        <nav class="menu">
            <a href="dashboard_jurado.php" class="activo">📄 Tesis asignadas</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="encabezado">
            <span class="subtitulo">Evaluación de tesis</span>
            <h1>Tesis asignadas</h1>
            <p>Selecciona una tesis para revisar el documento y las observaciones de los tres jurados.</p>
        </header>

        <?php if (count($tesisAsignadas) === 0): ?>
            <section class="panel estado-vacio">
                La Dirección de la EPISI todavía no te ha designado como jurado de una tesis.
            </section>
        <?php else: ?>
            <section class="lista-tesis">
                <?php foreach ($tesisAsignadas as $tesis): ?>
                    <article class="tarjeta-tesis">
                        <div class="datos-tesis">
                            <span class="estado recibido">Tesis asignada</span>
                            <h2><?php echo htmlspecialchars($tesis["nombre_archivo"]); ?></h2>
                            <p><strong>Código:</strong> <?php echo htmlspecialchars($tesis["codigo"]); ?></p>
                            <p><strong>Correo:</strong> <?php echo htmlspecialchars($tesis["correo"]); ?></p>
                            <small>Entregada: <?php echo htmlspecialchars($tesis["fecha_subida"]); ?></small>
                        </div>

                        <div class="resumen-observaciones">
                            <span>Observaciones registradas</span>
                            <strong><?php echo (int) $tesis["total_observaciones"]; ?>/3</strong>
                        </div>

                        <a class="boton-principal" href="revisar_tesis_jurado.php?id=<?php echo rawurlencode($tesis["id_usuario"]); ?>">
                            Ingresar a revisión
                        </a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
