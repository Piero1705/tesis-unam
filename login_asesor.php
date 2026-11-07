<?php
session_start();

$mensaje = $_SESSION["mensaje_login_asesor"] ?? "";
$tipoMensaje = $_SESSION["tipo_mensaje_login_asesor"] ?? "";

unset($_SESSION["mensaje_login_asesor"]);
unset($_SESSION["tipo_mensaje_login_asesor"]);

if (isset($_SESSION["id_asesor"])) {
    header("Location: dashboard_asesor.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión - Asesor UNAM</title>
    <link rel="stylesheet" href="login_asesor.css">
</head>
<body>

<main class="pagina-login">

    <section class="contenedor-logo">
        <img
            src="logo_unam.png"
            alt="Logo de la Universidad Nacional de Moquegua"
            class="logo-unam"
        >
    </section>

    <section class="zona-central">

        <div class="panel-login">

            <h1 class="titulo-login">
                Ingresa tu información para iniciar sesión.
            </h1>

            <?php if ($mensaje !== ""): ?>
                <div
                    class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>"
                    role="alert"
                >
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <form
                id="formularioAsesor"
                action="procesar_login_asesor.php"
                method="POST"
                autocomplete="on"
            >

                <div class="grupo-campo">
                    <input
                        type="email"
                        id="correo_electronico"
                        name="correo_electronico"
                        class="campo"
                        placeholder="Correo Electrónico"
                        value="<?php echo htmlspecialchars($_POST["correo_electronico"] ?? ""); ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="grupo-campo">
                    <input
                        type="text"
                        id="codigo_estudiante"
                        name="codigo_estudiante"
                        class="campo"
                        placeholder="Código del Estudiante"
                        value="<?php echo htmlspecialchars($_POST["codigo_estudiante"] ?? ""); ?>"
                        maxlength="30"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="grupo-campo">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="campo"
                        placeholder="Contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <p class="bienvenida">
                    Bienvenido, Asesor de Tesis
                </p>

            </form>

        </div>

        <div class="contenedor-ilustracion">
            <img
                src="asesor.png"
                alt="Ilustración de un asesor de tesis"
                class="ilustracion-asesor"
            >
        </div>

    </section>

    <aside class="zona-botones">

        <button
            type="submit"
            form="formularioAsesor"
            class="boton"
        >
            Iniciar Sesión
        </button>

        <a
            href="recuperar_password_asesor.php"
            class="boton"
        >
            Olvidé mi Contraseña
        </a>

    </aside>

</main>

</body>
</html>
