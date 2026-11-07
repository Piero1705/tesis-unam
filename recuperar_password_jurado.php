<?php
session_start();

$mensaje = $_SESSION["mensaje"] ?? "";
unset($_SESSION["mensaje"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña - Jurado</title>
<link rel="stylesheet" href="recuperar_password_jurado.css">
</head>
<body>

<div class="contenedor">
    <main class="contenido">
        <section class="login">

            <h1>Restablecer contraseña<br>Jurado Dictaminador</h1>

            <?php if($mensaje!=""){ ?>
                <p class="mensaje"><?php echo $mensaje; ?></p>
            <?php } ?>

            <form action="procesar_cambio_password_jurado.php" method="POST">

                <input type="email"
                       name="correo_electronico"
                       placeholder="Correo Electrónico"
                       required>

                <input type="password"
                       name="nueva_password"
                       placeholder="Nueva Contraseña"
                       required>

                <input type="password"
                       name="confirmar_password"
                       placeholder="Confirmar Contraseña"
                       required>

                <button type="submit">
                    Actualizar Contraseña
                </button>

            </form>

        </section>
    </main>
</div>

</body>
</html>
