<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: HTML_1_1.php");
    exit;
}

$carpeta = __DIR__ . DIRECTORY_SEPARATOR . "archivos_estudiante";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["archivo"])) {
    $archivo = $_FILES["archivo"];

    if ($archivo["error"] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
        $permitidas = ["pdf", "doc", "docx"];

        if (in_array($extension, $permitidas, true)) {
            $nombreSeguro = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($archivo["name"]));
            $nombreFinal = date("Ymd_His") . "_" . $nombreSeguro;
            $destino = $carpeta . DIRECTORY_SEPARATOR . $nombreFinal;

            if (move_uploaded_file($archivo["tmp_name"], $destino)) {
                $mensaje = "Archivo subido correctamente.";
            } else {
                $mensaje = "No se pudo guardar el archivo.";
            }
        } else {
            $mensaje = "Solo se permiten archivos PDF, DOC y DOCX.";
        }
    } else {
        $mensaje = "No se pudo subir el archivo.";
    }
}

$archivos = [];

if (is_dir($carpeta)) {
    foreach (scandir($carpeta) as $nombre) {
        if ($nombre === "." || $nombre === "..") {
            continue;
        }

        $ruta = $carpeta . DIRECTORY_SEPARATOR . $nombre;

        if (is_file($ruta)) {
            $archivos[] = [
                "nombre" => $nombre,
                "fecha" => filemtime($ruta),
                "tamano" => filesize($ruta)
            ];
        }
    }
}

usort($archivos, function ($a, $b) {
    return $b["fecha"] - $a["fecha"];
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subida de Archivos</title>
    <link rel="stylesheet" href="dashboard_estudiante_archivos.css">
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
            <a href="dashboard_estudiante_archivos.php" class="activo">☁ Subida de Archivos</a>
            <a href="entrega_tesis_estudiante.php">📄 Entrega de Tesis</a>
            <a href="mis_tramites_estudiante.php">📁 Mis Trámites</a>
            <a href="#">💬 Observaciones</a>
            <a href="#">⭐ Evaluación</a>
        </nav>
    </aside>

    <main class="contenido">
        <h1>Subida de Archivos</h1>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <section class="panel">
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="archivo" accept=".pdf,.doc,.docx" required>
                <button type="submit">Subir Archivo</button>
            </form>
        </section>

        <section class="panel">
            <h2>Historial de Archivos</h2>

            <?php if (count($archivos) === 0): ?>
                <p>No hay archivos almacenados.</p>
            <?php else: ?>
                <?php foreach ($archivos as $archivo): ?>
                    <div class="archivo">
                        <span><?php echo htmlspecialchars($archivo["nombre"]); ?></span>
                        <a href="archivos_estudiante/<?php echo rawurlencode($archivo["nombre"]); ?>" download>Descargar</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

</div>

</body>
</html>
