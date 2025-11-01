<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
  </head>
  <body>
    <h2>Registro de usuario</h2>
    <form action="/loginPooMejorado/src/php/CRUD/crear.php" method="post">
      <label>Nombre:</label>
      <input type="text" name="nombre" required /><br />
      <label>Email:</label>
      <input type="email" name="email" required /><br />
      <label>Contraseña:</label>
      <input type="password" name="password" required /><br />
      <label>Confirmar contraseña:</label>
      <input type="password" name="confirmPassword" required />
      <button type="submit">Ingresar</button>
    </form>
  </body>
</html>
