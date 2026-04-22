<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Datos Registrados</title>
</head>
<body>
    <div class="resultado">
        <h1>Datos Registrados</h1>
        <p><strong>Nombre:</strong> <?= $procesador->nombre ?></p>
        <p><strong>Correo:</strong> <?= $procesador->email ?></p>
        <p><strong>Cédula:</strong> <?= $procesador->cedula ?></p>
        <p><strong>Edad:</strong> <?= $procesador->edad ?></p>
        
        <h2>Información del servidor</h2>
        <p><strong>PHP_SELF:</strong> <?= $_SERVER['PHP_SELF'] ?></p>
        <p><strong>SERVER_NAME:</strong> <?= $_SERVER['SERVER_NAME'] ?></p>
        <p><strong>HTTP_USER_AGENT:</strong> <?= $_SERVER['HTTP_USER_AGENT'] ?></p>
        <p><strong>REQUEST_METHOD:</strong> <?= $_SERVER['REQUEST_METHOD'] ?></p>
        <p><strong>REMOTE_ADDR:</strong> <?= $_SERVER['REMOTE_ADDR'] ?></p>
        <p><strong>QUERY_STRING:</strong> <?= $_SERVER['QUERY_STRING'] ?></p>
    </div>
</body>
</html>