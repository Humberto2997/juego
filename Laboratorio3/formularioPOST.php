<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Formulario Post</title>
</head>
<body>
  <form action="procesarformulario.php" method="POST">
    <h1>Formulario POST</h1>
  <label for="nombre">Nombre:</label>
  <input type="text" name="nombre">
  <br/><br/>
  <label for="email">Correo Electronico:</label>
  <input type="text" name="email">
  <br/><br/>
  <label for="cedula">Cedula:</label>
  <input type="text" name="cedula">
  <br/><br/>
  <label for="edad">Edad:</label>
  <input type="number" name="edad">
  <br/><br/>
  <input class = "boton" type="submit" value="Enviar">
</form>
</body>
</html>