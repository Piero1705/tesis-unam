<?php
session_start();

$mensaje = $_SESSION["mensaje"] ?? "";
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Dirección EPISI</title>

    <link rel="stylesheet" href="recuperar_password_direccion.css">
</head>
<body>

<div class="contenedor">

    <section class="panel-recuperacion">

        <h1>Restablecer contraseña</h1>
        <h2>Dirección de la EPISI</h2>

        <?php if ($mensaje !== ""): ?>
            <p class="mensaje <?= htmlspecialchars($tipo_mensaje, ENT_QUOTES, "UTF-8") ?>">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form
            action="procesar_cambio_password_direccion.php"
            method="POST"
            autocomplete="off"
        >

            <input
                type="email"
                name="correo_electronico"
                placeholder="Correo Electrónico"
                autocomplete="email"
                required
            >

            <input
                type="password"
                name="nueva_password"
                placeholder="Nueva Contraseña"
                autocomplete="new-password"
                minlength="6"
                required
            >

            <input
                type="password"
                name="confirmar_password"
                placeholder="Confirmar Contraseña"
                autocomplete="new-password"
                minlength="6"
                required
            >

            <button type="submit">
                Actualizar Contraseña
            </button>

        </form>

        <a href="login_direccion.php" class="volver">
            Volver al inicio de sesión
        </a>

    </section>

</div>

</body>
</html>
