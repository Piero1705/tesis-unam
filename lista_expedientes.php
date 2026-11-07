<?php
session_start();

if (!isset($_SESSION["id_secretaria"])) {
    header("Location: login_secretaria.php");
    exit;
}

$carpetaBase = __DIR__ . DIRECTORY_SEPARATOR . "tramites_estudiante";
$expedientes = [];

function leerJsonSecretaria(string $ruta): array
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
        $envio = leerJsonSecretaria($carpeta . DIRECTORY_SEPARATOR . "observaciones_enviadas_secretaria.json");

        if (empty($envio)) {
            continue;
        }

        $acta = leerJsonSecretaria($carpeta . DIRECTORY_SEPARATOR . "acta_emision_tesis.json");

        $expedientes[] = [
            "id_usuario" => $idSeguro,
            "codigo" => $envio["codigo_universitario"] ?? "Sin código",
            "correo" => $envio["correo_estudiante"] ?? "Sin correo",
            "fecha_envio" => $envio["fecha_envio"] ?? "",
            "estado" => !empty($acta)
                ? "Acta generada"
                : "Pendiente de acta",
            "acta_generada" => !empty($acta)
        ];
    }
}

usort($expedientes, fn(array $a, array $b): int => strcmp($b["fecha_envio"], $a["fecha_envio"]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Expedientes</title>
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
            <a href="lista_expedientes.php" class="activo">📚 Lista de Expedientes</a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="encabezado">
            <div>
                <span class="subtitulo">Gestión de expedientes</span>
                <h1>Lista de Expedientes</h1>
                <p>Tesis con observaciones completas enviadas por los jurados.</p>
            </div>
        </header>

        <section class="panel">
            <?php if (count($expedientes) === 0): ?>
                <div class="estado-vacio">
                    No existen tesis enviadas por los jurados.
                </div>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Código universitario</th>
                                <th>Correo electrónico</th>
                                <th>Fecha de envío</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($expedientes as $expediente): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($expediente["codigo"]); ?></strong></td>
                                <td><?php echo htmlspecialchars($expediente["correo"]); ?></td>
                                <td><?php echo htmlspecialchars($expediente["fecha_envio"]); ?></td>
                                <td>
                                    <span class="estado <?php echo $expediente["acta_generada"] ? "listo" : "pendiente"; ?>">
                                        <?php echo htmlspecialchars($expediente["estado"]); ?>
                                    </span>
                                </td>
                                <td>
                                    <a
                                        class="boton-ver"
                                        href="generar_acta_emision.php?id=<?php echo rawurlencode($expediente["id_usuario"]); ?>"
                                    >
                                        <?php echo $expediente["acta_generada"]
                                            ? "Ver acta"
                                            : "Generar acta"; ?>
                                    </a>
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
