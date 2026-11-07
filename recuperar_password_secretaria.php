<?php
session_start();

$mensaje = $_SESSION["mensaje_password_secretaria"] ?? "";
$tipoMensaje = $_SESSION["tipo_mensaje_password_secretaria"] ?? "";

unset($_SESSION["mensaje_password_secretaria"]);
unset($_SESSION["tipo_mensaje_password_secretaria"]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Restablecer contraseña - Secretaría UNAM</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        :root {
            --blanco: #ffffff;
            --turquesa: #53d1d4;
            --azul: #2878c7;
            --azul-oscuro: #1d5fa7;
            --negro: #111111;
            --gris: #6c6c6c;
            --rojo: #b3261e;
            --rojo-fondo: #ffdcd8;
            --verde: #176b3a;
            --verde-fondo: #dff6e8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            padding: 35px 20px;

            display: flex;
            justify-content: center;
            align-items: center;

            font-family: "Inter", Arial, sans-serif;
            background-color: var(--blanco);
            color: var(--negro);
        }

        input,
        button {
            font-family: inherit;
        }

        .pagina-recuperacion {
            width: 100%;
            max-width: 900px;
        }

        .logo-contenedor {
            margin-bottom: 25px;
            text-align: center;
        }

        .logo-unam {
            width: 230px;
            max-width: 80%;
            padding: 12px;
            border: 2px solid #d6d6d6;
            border-radius: 22px;
            background-color: var(--blanco);
        }

        .panel-recuperacion {
            width: 100%;
            padding: 45px 70px;
            background-color: var(--turquesa);
            border-radius: 38px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .titulo {
            margin-bottom: 15px;
            font-size: clamp(29px, 4vw, 42px);
            line-height: 1.2;
            text-align: center;
        }

        .descripcion {
            max-width: 650px;
            margin: 0 auto 32px;
            font-size: 18px;
            line-height: 1.5;
            text-align: center;
        }

        .grupo-campo {
            margin-bottom: 22px;
        }

        .campo {
            width: 100%;
            height: 66px;
            padding: 0 24px;

            border: 3px solid transparent;
            border-radius: 19px;
            background-color: var(--blanco);

            color: var(--negro);
            font-size: 19px;
            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .campo::placeholder {
            color: var(--gris);
        }

        .campo:focus {
            border-color: var(--azul);
            box-shadow: 0 0 0 4px rgba(40, 120, 199, 0.18);
        }

        .mensaje {
            margin-bottom: 22px;
            padding: 14px 18px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
        }

        .mensaje.error {
            color: var(--rojo);
            background-color: var(--rojo-fondo);
            border: 1px solid #edaaa3;
        }

        .mensaje.correcto {
            color: var(--verde);
            background-color: var(--verde-fondo);
            border: 1px solid #9dd8b4;
        }

        .acciones {
            margin-top: 30px;

            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .boton {
            min-height: 65px;
            padding: 15px 20px;

            display: flex;
            justify-content: center;
            align-items: center;

            border: none;
            border-radius: 18px;

            background-color: var(--azul);
            color: var(--blanco);

            font-size: 19px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;

            cursor: pointer;

            transition:
                background-color 0.2s ease,
                transform 0.2s ease;
        }

        .boton:hover {
            background-color: var(--azul-oscuro);
            transform: translateY(-2px);
        }

        .requisitos {
            margin-top: 22px;
            font-size: 14px;
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 650px) {
            body {
                padding: 20px 15px;
                align-items: flex-start;
            }

            .panel-recuperacion {
                padding: 32px 20px;
                border-radius: 28px;
            }

            .campo {
                height: 58px;
                font-size: 16px;
            }

            .acciones {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <main class="pagina-recuperacion">

        <div class="logo-contenedor">

            <img
                src="logo_unam.png"
                alt="Logo de la Universidad Nacional de Moquegua"
                class="logo-unam"
            >

        </div>

        <section class="panel-recuperacion">

            <h1 class="titulo">
                Restablecer contraseña
            </h1>

            <p class="descripcion">
                Secretaria, ingresa tu correo institucional y registra una
                nueva contraseña para recuperar el acceso al sistema.
            </p>

            <?php if ($mensaje !== ""): ?>

                <div
                    class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>"
                    role="alert"
                >
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>

            <?php endif; ?>

            <form action="cambiar_password_secretaria.php" method="POST">

                <div class="grupo-campo">

                    <input
                        type="email"
                        id="correo_electronico"
                        name="correo_electronico"
                        class="campo"
                        placeholder="Correo Electrónico"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["correo_electronico"] ?? ""
                            );
                        ?>"
                        autocomplete="email"
                        required
                    >

                </div>

                <div class="grupo-campo">

                    <input
                        type="password"
                        id="password_nueva"
                        name="password_nueva"
                        class="campo"
                        placeholder="Contraseña Nueva"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <div class="grupo-campo">

                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        class="campo"
                        placeholder="Confirmar Contraseña"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <p class="requisitos">
                    La contraseña debe contener como mínimo 8 caracteres.
                </p>

                <div class="acciones">

                    <button type="submit" class="boton">
                        Cambiar Contraseña
                    </button>

                    <a
                        href="login_secretaria.php"
                        class="boton"
                    >
                        Volver al Inicio de Sesión
                    </a>

                </div>

            </form>

        </section>

    </main>

</body>

</html>