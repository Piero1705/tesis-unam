<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inicio de Sesión - Asesor</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="contenedor">
<aside class="logo"><img src="logo.png" alt="Logo UNAM"></aside>
<main class="contenido">
<section class="login">
<h1>Inicio de Sesión del Asesor de Tesis</h1>
<form id="formularioAsesor" action="login_asesor.php" method="POST">
<input type="email" name="correo_electronico" placeholder="Correo Electrónico del Asesor" required>
<input type="text" name="codigo_tesista" placeholder="Código del Tesista / Estudiante" required>
<input type="password" name="password" placeholder="Contraseña" required>
</form>
<h2>Bienvenido, Asesor de Tesis</h2>
</section>
<section class="imagen"><img src="student.png" alt="Asesor"></section>
</main>
<aside class="botones">
<button class="btn" type="submit" form="formularioAsesor">Iniciar Sesión</button>
<button class="btn" type="button" onclick="window.location.href='HTML_1_2.php'">Olvidé mi Contraseña</button>
</aside>
<footer>Universidad Nacional de Moquegua (UNAM)</footer>
</div>
</body>
</html>