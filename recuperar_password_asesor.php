<?php
session_start();

$mensaje = $_SESSION["mensaje_password_asesor"] ?? "";
$tipoMensaje = $_SESSION["tipo_mensaje_password_asesor"] ?? "";

unset($_SESSION["mensaje_password_asesor"]);
unset($_SESSION["tipo_mensaje_password_asesor"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña - Asesor UNAM</title>
    <link rel="stylesheet" href="recuperar_password_asesor.css">
</head>
<body>

<main class="pagina-recuperacion">

    <section class="panel-recuperacion">

        <h1>Cambiar contraseña</h1>

        <p>
            Ingresa el correo electrónico registrado y establece
            una contraseña nueva.
        </p>

        <?php if ($mensaje !== ""): ?>
            <div
                class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>"
                role="alert"
            >
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="cambiar_password_asesor.php" method="POST">

            <input
                type="email"
                name="correo_electronico"
                placeholder="Correo Electrónico"
                autocomplete="email"
                required
            >

            <input
                type="password"
                name="password_nueva"
                placeholder="Contraseña Nueva"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <input
                type="password"
                name="confirmar_password"
                placeholder="Confirmar Contraseña"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit">
                Cambiar Contraseña
            </button>

            <a href="login_asesor.php">
                Volver al Inicio de Sesión
            </a>

        </form>

    </section>

</main>

</body>
</html>
