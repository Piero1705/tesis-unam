<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Asesor</title>

    <link rel="stylesheet" href="asesor_recuperacion.css">
</head>

<body>

<div class="contenedor">

    <section class="panel-logo">
        <img src="logo.png" alt="Logo UNAM">
    </section>

    <section class="panel-recuperacion">

        <h1>Recuperar Contraseña del Asesor</h1>

        <form action="actualizar_contrasena_asesor.php" method="POST">

            <input
                type="email"
                name="correo_electronico"
                placeholder="Correo Electrónico del Asesor"
                required>

            <input
                type="password"
                name="nueva_password"
                placeholder="Nueva Contraseña"
                required>

            <input
                type="password"
                name="confirmar_password"
                placeholder="Confirmar Contraseña"
                required>

            <button type="submit">
                Actualizar Contraseña
            </button>

            <a href="HTML_asesor_1_1.php" class="volver">
                Volver al Inicio
            </a>

        </form>

    </section>

</div>

</body>
</html>
