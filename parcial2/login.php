<?php
// login.php
require_once 'config.php';
iniciarSesion();

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . ($_SESSION['usuario_rol'] === 'rh' ? 'dashboard_rh.php' : 'formulario.php'));
    exit;
}

$error = '';
$ip_actual = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

try {
    $pdo = getDB();

    // Mitigación Fuerza Bruta: Validar intentos erróneos
    $stmtB = $pdo->prepare('SELECT intentos, ultimo_intento FROM intentos_login WHERE ip_address = ?');
    $stmtB->execute([$ip_actual]);
    $bloqueo = $stmtB->fetch();

    if ($bloqueo && $bloqueo['intentos'] >= 5 && (time() - strtotime($bloqueo['ultimo_intento'])) < 300) {
        die('<div style="font-family:sans-serif; text-align:center; margin-top:50px;"><h2>⚠️ Acceso bloqueado temporalmente</h2><p>Demasiados intentos fallidos. Intente de nuevo en 5 minutos.</p></div>');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Por favor completa todos los campos.';
        } else {
            $stmt = $pdo->prepare('SELECT id, username, password_hash, rol FROM usuarios WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                // Login Exitoso: Limpiar penalización de IP
                $stmtDel = $pdo->prepare('DELETE FROM intentos_login WHERE ip_address = ?');
                $stmtDel->execute([$ip_actual]);

                session_regenerate_id(true);
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['username'];
                $_SESSION['usuario_rol']    = $usuario['rol'];

                header('Location: ' . ($usuario['rol'] === 'rh' ? 'dashboard_rh.php' : 'formulario.php'));
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos.';
                // Registrar intento fallido
                $stmtIns = $pdo->prepare('INSERT INTO intentos_login (ip_address, intentos) VALUES (?, 1) ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = CURRENT_TIMESTAMP');
                $stmtIns->execute([$ip_actual]);
            }
        }
    }
} catch (Exception $e) {
    $error = 'Error interno del sistema.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RH — Iniciar Sesión</title>
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="contenedor">
    <span class="logo">💼 RHApp</span>
    <h1>Iniciar Sesión</h1>
    <p style="font-size:13px;color:#777;margin-bottom:20px;">Plataforma de selección de Personal.</p>

    <?php if ($error): ?> <div class="error">⚠️ <?= limpiar($error) ?></div> <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username" required autocomplete="username">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <button class="btn btn-bloque" type="submit">Ingresar</button>
    </form>
    <a class="link-secundario" href="register.php">¿No tienes cuenta? Regístrate como aspirante</a>
</div>
</body>
</html>