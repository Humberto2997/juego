<?php
require_once 'config.php';
iniciarSesion();

if (!empty($_SESSION['usuario_id'])) {
    header('Location: formulario.php');
    exit;
}

$errores = [];
$exito   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validar nombre de usuario
    if ($username === '') { 
        $errores[] = 'El nombre de usuario es obligatorio.'; 
    } elseif (strlen($username) < 4) { 
        $errores[] = 'El usuario debe tener mínimo 4 caracteres.'; 
    }

    // Directiva de contrasena segura (Mínimo 15 caracteres, mayúsculas, minúsculas, números y símbolos)
    if (strlen($password) < 15) {
        $errores[] = 'La contraseña debe contener un mínimo estricto de 15 caracteres.';
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errores[] = 'La contraseña debe combinar letras mayúsculas, minúsculas, números y caracteres especiales.';
    }
    if ($password !== $password2) { 
        $errores[] = 'Las contraseñas ingresadas no coinciden.'; 
    }

    if (empty($errores)) {
        $pdo = getDB();
        
        try {
            $check = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? LIMIT 1');
            $check->execute([$username]);

            if ($check->fetch()) {
                $errores[] = 'El nombre de usuario ya se encuentra registrado.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                
                $pdo->beginTransaction();
                
                // Insertar solo las credenciales esenciales
                $ins1 = $pdo->prepare('INSERT INTO usuarios (username, password_hash, rol) VALUES (?, ?, "aspirante")');
                $ins1->execute([$username, $hash]);
                $uid = (int)$pdo->lastInsertId();

                // Crear fila vacía en aspirantes ligada al usuario para que exista su registro vacío
                $ins2 = $pdo->prepare('INSERT INTO aspirantes (usuario_id, estado) VALUES (?, "no revisado")');
                $ins2->execute([$uid]);
                
                $pdo->commit();
                $exito = true;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errores[] = 'No se pudo completar el registro debido a un fallo interno.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RH — Crear Cuenta</title>
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="contenedor">
    <span class="logo"> RRHH App</span>
    <h1>Registro de Cuenta</h1>
    <p style="font-size:13px;color:#777;margin-bottom:20px;">Crea tu usuario para poder postularte.</p>

    <?php if ($exito): ?>
        <div class="exito">
             <strong>¡Cuenta creada con éxito!</strong><br>
            Inicia sesión ahora para completar tus datos de Recursos Humanos.<br><br>
            <a class="btn" href="login.php" style="display:inline-block; text-decoration:none; text-align:center;">Ir al Login →</a>
        </div>
    <?php else: ?>

    <?php if (!empty($errores)): ?>
        <div class="error">
            <ul><?php foreach ($errores as $e): ?> <li><?= limpiar($e) ?></li> <?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="username">Nombre de Usuario *</label>
        <input type="text" id="username" name="username" value="<?= limpiar($_POST['username'] ?? '') ?>" placeholder="ej. juan_perez" required>

        <label for="password">Contraseña *</label>
        <input type="password" id="password" name="password" placeholder="Mínimo 15 caracteres complejos" required>

        <label for="password2">Confirmar Contraseña *</label>
        <input type="password" id="password2" name="password2" placeholder="Repite tu contraseña exactamente" required>

        <button class="btn btn-bloque" type="submit">Crear Cuenta</button>
    </form>
    <a class="link-secundario" href="login.php">¿Ya tienes cuenta? Inicia sesión aquí</a>
    <?php endif; ?>
</div>
</body>
</html>