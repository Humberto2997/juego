<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style2.css">
  <title>Formulario GET</title>
</head>
<body>
  <form action="procesarformulario.php" method="GET">​
    <h1>Formulario GET</h1>
    <label for="nombre">Nombre</label>
    <input type="text" name="nombre">
    <br/><br/>
    <label for="peso">Peso (kg)</label>
    <input type="number" name="peso" step="0.01">
    <br/><br/>
    <label for="altura">Altura (m)</label>
    <input type="number" name="altura" step="0.01">

    <br/><br/>
    <input class="boton" type="submit" value="Calcular">​
  </form> 
</body>
</html>



