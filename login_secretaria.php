<?php
session_start();

$mensaje = $_SESSION["mensaje_login_secretaria"] ?? "";
$tipoMensaje = $_SESSION["tipo_mensaje_login_secretaria"] ?? "";

unset($_SESSION["mensaje_login_secretaria"]);
unset($_SESSION["tipo_mensaje_login_secretaria"]);

if (isset($_SESSION["id_secretaria"])) {
    header("Location: dashboard_secretaria.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Inicio de sesión - Secretaría UNAM</title>

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
            font-family: "Inter", Arial, sans-serif;
            background-color: var(--blanco);
            color: var(--negro);
        }

        button,
        input {
            font-family: inherit;
        }

        .pagina-login {
            width: 100%;
            min-height: 100vh;
            padding: 32px 45px;

            display: grid;
            grid-template-columns: 280px minmax(500px, 790px) 280px;
            grid-template-rows: auto 1fr;

            justify-content: space-between;
            align-items: start;
            gap: 30px;
        }

        .contenedor-logo {
            display: flex;
            justify-content: flex-start;
        }

        .logo-unam {
            width: 280px;
            max-width: 100%;
            padding: 15px;
            background-color: var(--blanco);
            border: 2px solid #d6d6d6;
            border-radius: 25px;
            object-fit: contain;
        }

        .zona-central {
            width: 100%;

            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .panel-login {
            width: 100%;
            padding: 40px 70px 38px;
            background-color: var(--turquesa);
            border-radius: 38px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
        }

        .titulo-login {
            max-width: 700px;
            margin: 0 auto 35px;
            color: var(--negro);
            font-size: clamp(28px, 2.6vw, 43px);
            font-weight: 700;
            line-height: 1.25;
            text-align: center;
        }

        .grupo-campo {
            margin-bottom: 25px;
        }

        .campo {
            width: 100%;
            height: 68px;
            padding: 0 25px;

            background-color: var(--blanco);
            border: 3px solid transparent;
            border-radius: 20px;

            color: var(--negro);
            font-size: 21px;
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

        .bienvenida {
            margin-top: 35px;
            color: var(--negro);
            font-size: clamp(27px, 2.2vw, 40px);
            font-weight: 700;
            text-align: center;
        }

        .mensaje {
            width: 100%;
            margin-bottom: 22px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 15px;
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

        .contenedor-ilustracion {
            width: 100%;
            margin-top: 25px;

            display: flex;
            justify-content: center;
        }

        .ilustracion-secretaria {
            width: 470px;
            max-width: 85%;
            max-height: 320px;
            object-fit: contain;
        }

        .zona-botones {
            min-height: 520px;
            padding-top: 270px;

            display: flex;
            flex-direction: column;
            gap: 38px;
        }

        .boton {
            width: 100%;
            min-height: 78px;
            padding: 16px 18px;

            display: flex;
            justify-content: center;
            align-items: center;

            border: none;
            border-radius: 20px;

            background-color: var(--azul);
            color: var(--blanco);

            font-size: 23px;
            font-weight: 700;
            line-height: 1.15;
            text-align: center;
            text-decoration: none;

            cursor: pointer;

            transition:
                background-color 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .boton:hover {
            background-color: var(--azul-oscuro);
            box-shadow: 0 8px 18px rgba(40, 120, 199, 0.25);
            transform: translateY(-2px);
        }

        .boton:active {
            transform: translateY(0);
        }

        @media (max-width: 1250px) {
            .pagina-login {
                grid-template-columns: 220px minmax(480px, 690px) 230px;
                padding: 25px;
            }

            .logo-unam {
                width: 220px;
            }

            .panel-login {
                padding: 35px 50px;
            }

            .zona-botones {
                padding-top: 260px;
            }

            .boton {
                font-size: 20px;
            }
        }

        @media (max-width: 950px) {
            .pagina-login {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 25px 20px 40px;
            }

            .contenedor-logo {
                width: 100%;
                justify-content: center;
            }

            .logo-unam {
                width: 240px;
            }

            .zona-central {
                max-width: 750px;
            }

            .zona-botones {
                width: 100%;
                max-width: 750px;
                min-height: auto;
                padding-top: 0;

                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 620px) {
            .pagina-login {
                padding: 20px 15px 35px;
            }

            .panel-login {
                padding: 28px 20px;
                border-radius: 28px;
            }

            .titulo-login {
                margin-bottom: 25px;
                font-size: 28px;
            }

            .campo {
                height: 58px;
                padding: 0 18px;
                border-radius: 16px;
                font-size: 17px;
            }

            .bienvenida {
                margin-top: 27px;
                font-size: 27px;
            }

            .zona-botones {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .boton {
                min-height: 65px;
                font-size: 19px;
            }
        }
    </style>
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
                    Ingresa tu información para acceder al módulo de Secretaría.
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
                    id="formularioSecretaria"
                    action="procesar_login_secretaria.php"
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
                            id="password"
                            name="password"
                            class="campo"
                            placeholder="Contraseña"
                            autocomplete="current-password"
                            required
                        >

                    </div>

                    <p class="bienvenida">
                        Bienvenida, Secretaría de la UNAM
                    </p>

                </form>

            </div>

            <div class="contenedor-ilustracion">

                <img
                    src="secretaria.png"
                    alt="Secretaria trabajando en una computadora"
                    class="ilustracion-secretaria"
                >

            </div>

        </section>

        <aside class="zona-botones">

            <button
                type="submit"
                form="formularioSecretaria"
                class="boton"
            >
                Iniciar Sesión
            </button>

            <a
                href="recuperar_password_secretaria.php"
                class="boton"
            >
                Olvidé mi Contraseña
            </a>

        </aside>

    </main>

</body>

</html>