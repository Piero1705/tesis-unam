<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - UNAM</title>

    <link rel="stylesheet" href="recuperar_contrasena.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>

<div class="contenedor">

    <aside class="logo">
        <img src="logo.png" alt="Logo UNAM">
    </aside>

    <main class="contenido">

        <section class="recuperacion">

            <h1>Recuperación de Contraseña</h1>

            <p class="descripcion">
                Ingresa tu correo electrónico y establece una nueva contraseña.
            </p>

            <p id="mensaje" class="mensaje" aria-live="polite"></p>

            <form
                id="formularioRecuperacion"
                action="actualizar_contrasena.php"
                method="POST"
                novalidate>

                <input
                    id="correo"
                    type="email"
                    name="correo_electronico"
                    placeholder="Correo Electrónico"
                    autocomplete="email">

                <input
                    id="nuevaContrasena"
                    type="password"
                    name="nueva_password"
                    placeholder="Nueva Contraseña"
                    autocomplete="new-password">

                <input
                    id="confirmarContrasena"
                    type="password"
                    name="confirmar_password"
                    placeholder="Confirmar Contraseña"
                    autocomplete="new-password">

            </form>

            <h2>Protege tu cuenta universitaria</h2>

        </section>

        <section class="imagen">
            <img src="student.png" alt="Estudiante">
        </section>

    </main>

    <aside class="botones">

        <button
            id="btnActualizar"
            class="btn"
            type="submit"
            form="formularioRecuperacion">
            Actualizar Contraseña
        </button>

        <a href="HTML_1_1.php" class="btn btn-enlace">
            Volver al Inicio
        </a>

    </aside>

</div>

<script>
    const formulario = document.getElementById("formularioRecuperacion");
    const correo = document.getElementById("correo");
    const nuevaContrasena = document.getElementById("nuevaContrasena");
    const confirmarContrasena = document.getElementById("confirmarContrasena");
    const mensaje = document.getElementById("mensaje");

    function mostrarMensaje(texto, tipo = "error") {
        mensaje.textContent = texto;
        mensaje.className = "mensaje " + tipo;
    }

    formulario.addEventListener("submit", function (evento) {

        if (correo.value.trim() === "") {
            evento.preventDefault();
            mostrarMensaje("Ingrese un correo electrónico.");
            correo.focus();
            return;
        }

        if (!correo.checkValidity()) {
            evento.preventDefault();
            mostrarMensaje("Ingrese un correo electrónico válido.");
            correo.focus();
            return;
        }

        if (nuevaContrasena.value === "") {
            evento.preventDefault();
            mostrarMensaje("Ingrese una nueva contraseña.");
            nuevaContrasena.focus();
            return;
        }

        if (nuevaContrasena.value.length < 8) {
            evento.preventDefault();
            mostrarMensaje("La contraseña debe tener al menos 8 caracteres.");
            nuevaContrasena.focus();
            return;
        }

        if (confirmarContrasena.value === "") {
            evento.preventDefault();
            mostrarMensaje("Confirme la nueva contraseña.");
            confirmarContrasena.focus();
            return;
        }

        if (nuevaContrasena.value !== confirmarContrasena.value) {
            evento.preventDefault();
            mostrarMensaje("Las contraseñas no coinciden.");
            confirmarContrasena.focus();
            return;
        }

        mostrarMensaje("Actualizando contraseña...", "exito");

        /*
         * Importante:
         * aquí NO se usa evento.preventDefault(),
         * por lo tanto el formulario sí se envía al PHP.
         */
    });
</script>

</body>
</html>
