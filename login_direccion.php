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
<title>Inicio de Sesión - Dirección EPISI</title>
<link rel="stylesheet" href="style_direccion.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="pagina-login">
<aside class="zona-logo"><img src="logo.png" alt="Logo UNAM" class="logo-unam"></aside>
<main class="zona-formulario">
<section class="panel-login">
<h1>Ingresa tu información para acceder al módulo de Dirección de la EPISI.</h1>
<?php if ($mensaje !== ""): ?><p class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?></p><?php endif; ?>
<form id="form-direccion" action="procesar_login_direccion.php" method="POST" class="formulario-login">
<input type="email" name="correo_electronico" placeholder="Correo Electrónico" required>
<input type="password" name="password" placeholder="Contraseña" required>
<div class="acciones-movil">
<button type="submit" class="boton">Iniciar Sesión</button>
<a href="recuperar_password_direccion.php" class="boton">Olvidé mi Contraseña</a>
</div>
</form>
<p class="bienvenida">Bienvenido, Director de la EPISI de la UNAM</p>
</section>
<div class="imagen-direccion"><img src="direccion.png" alt="Dirección de la EPISI"></div>
</main>
<aside class="zona-acciones">
<button type="button" class="boton" onclick="document.getElementById('form-direccion').requestSubmit();">Iniciar Sesión</button>
<a href="recuperar_password_direccion.php" class="boton">Olvidé mi Contraseña</a>
</aside>
</div>
</body>
</html>
