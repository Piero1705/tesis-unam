<?php
session_start();

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Jurado Dictaminador</title>
    <link rel="stylesheet" href="style_jurado.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="pagina-login">
        <aside class="zona-logo">
            <img src="logo.png" alt="Logo de la Universidad Nacional de Moquegua" class="logo-unam">
        </aside>

        <main class="zona-formulario">
            <section class="panel-login" aria-labelledby="titulo-login">
                <h1 id="titulo-login">
                    Ingresa tu información para acceder al módulo de Jurado Dictaminador.
                </h1>

                <?php if ($mensaje !== ''): ?>
                    <p class="mensaje" role="alert">
                        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <form id="form-jurado" action="procesar_login_jurado.php" method="POST" class="formulario-login" autocomplete="on">
                    <label class="sr-only" for="correo_electronico">Correo electrónico</label>
                    <input
                        type="email"
                        id="correo_electronico"
                        name="correo_electronico"
                        placeholder="Correo Electrónico"
                        autocomplete="email"
                        required
                    >

                    <label class="sr-only" for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Contraseña"
                        autocomplete="current-password"
                        required
                    >

                    <div class="acciones-movil">
                        <button type="submit" class="boton">Iniciar Sesión</button>
                        <a href="recuperar_password_jurado.php" class="boton enlace-boton">Olvidé mi Contraseña</a>
                    </div>
                </form>

                <p class="bienvenida">Bienvenido, Jurado Dictaminador de la UNAM</p>
            </section>

            <div class="imagen-jurado">
                <img src="jurado.png" alt="Ilustración del Jurado Dictaminador">
            </div>
        </main>

        <aside class="zona-acciones">
            <button type="button" class="boton" onclick="document.querySelector('.formulario-login').requestSubmit();">
                Iniciar Sesión
            </button>
            <a href="recuperar_password_jurado.php" class="boton enlace-boton">Olvidé mi Contraseña</a>
        </aside>
    </div>
</body>
</html>
