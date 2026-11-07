<?php
session_start();

if (!isset($_SESSION["id_director"])) {
    header("Location: login_direccion.php");
    exit;
}

$carpetaBase = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante";
$solicitudes = [];

function leerJsonDireccion(string $ruta): array
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

        $carpeta = $carpetaBase . DIRECTORY_SEPARATOR . $idSeguro;

        $solicitud = leerJsonDireccion($carpeta . DIRECTORY_SEPARATOR . "solicitud_enviada.json");
        $metadata = leerJsonDireccion($carpeta . DIRECTORY_SEPARATOR . "metadata.json");

        if (empty($solicitud) || empty($metadata)) {
            continue;
        }

        $completos = 0;

        foreach (["grado_bachiller", "proyecto_tesis", "acta_sustentacion"] as $tipo) {
            if (!empty($metadata[$tipo]["archivo"])) {
                $completos++;
            }
        }

        if ($completos !== 3) {
            continue;
        }

        $asesor = leerJsonDireccion($carpeta . DIRECTORY_SEPARATOR . "asesor_asignado.json");
        $tesis = leerJsonDireccion($carpeta . DIRECTORY_SEPARATOR . "tesis_completa.json");
        $jurados = leerJsonDireccion($carpeta . DIRECTORY_SEPARATOR . "jurados_asignados.json");
        $reportePlagio = leerJsonDireccion($carpeta . DIRECTORY_SEPARATOR . "reporte_plagio.json");

        $solicitudes[] = [
            "id_usuario" => $idSeguro,
            "codigo" => $solicitud["codigo_universitario"] ?? "Sin código",
            "correo" => $solicitud["correo_estudiante"] ?? "Sin correo",
            "asesor_asignado" => !empty($asesor),
            "correo_asesor" => $asesor["correo_asesor"] ?? "",
            "tesis_entregada" => !empty($tesis["archivo"]),
            "fecha_tesis" => $tesis["fecha_subida"] ?? "",
            "jurados_designados" => !empty($jurados["jurados"]),
            "reporte_plagio" => !empty($reportePlagio["archivo"])
        ];
    }
}

usort($solicitudes, fn(array $a, array $b): int => strcmp($a["codigo"], $b["codigo"]));

$mensaje = $_SESSION["mensaje_direccion"] ?? "";
$tipoMensaje = $_SESSION["tipo_mensaje_direccion"] ?? "";
unset($_SESSION["mensaje_direccion"], $_SESSION["tipo_mensaje_direccion"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dirección EPISI</title>
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
            <a href="dashboard_direccion.php" class="activo">📄 Solicitudes de tesis</a>
        </nav>
    </aside>

    <main class="contenido">

        <header class="encabezado">
            <div>
                <span class="subtitulo">Panel de Dirección</span>
                <h1>Solicitudes y entrega de tesis</h1>
                <p>Visualiza la entrega de tesis y designa a los tres jurados dictaminadores.</p>
            </div>

            <div class="perfil-director">D</div>
        </header>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-titulo">
                <div>
                    <h2>Estudiantes solicitantes</h2>
                    <p>El botón para designar jurados se habilita cuando la tesis completa fue entregada.</p>
                </div>
            </div>

            <?php if (count($solicitudes) === 0): ?>
                <div class="estado-vacio">No existen solicitudes disponibles.</div>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Código universitario</th>
                                <th>Correo electrónico</th>
                                <th>Asesor</th>
                                <th>Estado</th>
                                <th>Tesis entregada</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($solicitud["codigo"]); ?></strong></td>
                                <td><?php echo htmlspecialchars($solicitud["correo"]); ?></td>

                                <td>
                                    <?php if ($solicitud["asesor_asignado"]): ?>
                                        <span class="estado aprobado">Asignado</span>
                                        <small class="asesor-correo">
                                            <?php echo htmlspecialchars($solicitud["correo_asesor"]); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="estado pendiente">Sin asesor</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!$solicitud["asesor_asignado"]): ?>
                                        <span class="estado pendiente">Pendiente de asesor</span>
                                    <?php elseif (!$solicitud["tesis_entregada"]): ?>
                                        <span class="estado pendiente">Esperando tesis</span>
                                    <?php elseif ($solicitud["jurados_designados"]): ?>
                                        <span class="estado aprobado">Jurados designados</span>
                                    <?php else: ?>
                                        <span class="estado aprobado">Tesis entregada</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($solicitud["tesis_entregada"]): ?>
                                        <a
                                            class="boton-secundario"
                                            href="ver_documentos_tesis.php?id=<?php echo rawurlencode($solicitud["id_usuario"]); ?>"
                                            target="_blank"
                                        >
                                            Visualizar tesis y plagio
                                        </a>
                                        <small class="fecha-tesis">
                                            <?php echo htmlspecialchars($solicitud["fecha_tesis"]); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="estado pendiente">No entregada</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!$solicitud["asesor_asignado"]): ?>
                                        <a
                                            class="boton-aprobar"
                                            href="designar_asesor.php?id=<?php echo rawurlencode($solicitud["id_usuario"]); ?>"
                                        >
                                            Aprobar Solicitud
                                        </a>
                                    <?php elseif ($solicitud["tesis_entregada"]): ?>
                                        <a
                                            class="boton-aprobar"
                                            href="designar_jurados.php?id=<?php echo rawurlencode($solicitud["id_usuario"]); ?>"
                                        >
                                            <?php echo $solicitud["jurados_designados"]
                                                ? "Cambiar Jurados"
                                                : "Designar Jurados"; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="estado pendiente">Esperando entrega</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>

</div>

</body>
</html>
