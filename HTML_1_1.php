<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inicio de Sesión - UNAM</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="contenedor">
    <aside class="logo">
        <img src="logo.png" alt="Logo UNAM">
    </aside>

    <main class="contenido">
        <section class="login">
            <h1>Ingresa tu información para iniciar sesión.</h1>

            <form id="formularioLogin" action="login_1_1.php" method="POST">
                <input type="email" name="correo_electronico" placeholder="Correo Electrónico" required>
                <input type="text" name="codigo_universitario" placeholder="Código Universitario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
            </form>

            <h2>Bienvenido, Tesista Afortunado</h2>
        </section>

        <section class="imagen">
            <img src="student.png" alt="Estudiante">
        </section>
    </main>

    <aside class="botones">
        <button class="btn" type="submit" form="formularioLogin">
            Iniciar Sesión
        </button>

        <button
            class="btn"
            type="button"
            onclick="window.location.href='HTML_1_2.php'">

            Olvidé mi Contraseña

        </button>
    </aside>
</div>
</body>
</html>