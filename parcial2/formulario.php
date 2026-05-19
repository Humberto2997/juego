<?php
require_once 'config.php';
verificarSesion();

if ($_SESSION['usuario_rol'] !== 'aspirante') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$usuario_id = (int)$_SESSION['usuario_id'];
$mensaje = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula       = trim($_POST['cedula_pasaporte'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $apellido     = trim($_POST['apellido'] ?? '');
    $estado_civil = trim($_POST['estado_civil'] ?? '');
    $genero       = trim($_POST['genero'] ?? '');
    $tipo_sangre  = trim($_POST['tipo_sangre'] ?? '');
    $fecha_nac    = trim($_POST['fecha_nacimiento'] ?? '');
    $nacionalidad = trim($_POST['nacionalidad'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    $residencia   = trim($_POST['residencia'] ?? '');
    $correo       = trim($_POST['correo_electronico'] ?? '');

    // Validaciones estrictas
    if ($cedula === '')       $errores[] = 'La Cédula o pasaporte es obligatorio.';
    if ($nombre === '')       $errores[] = 'El nombre es obligatorio.';
    if ($apellido === '')     $errores[] = 'El apellido es obligatorio.';
    if (!in_array($genero, ['masculino', 'femenino'])) $errores[] = 'El género seleccionado no es válido (debe elegir Masculino o Femenino).';
    if ($fecha_nac === '') {
        $errores[] = 'La fecha de nacimiento es obligatoria.';
    } else {
        $fecha_nacimiento_dt = new DateTime($fecha_nac);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento_dt)->y;

        // Comprobar si la fecha ingresada es del futuro
        if ($fecha_nacimiento_dt > $hoy) {
            $errores[] = 'La fecha de nacimiento no puede ser una fecha futura.';
        } elseif ($edad <= 18) {
            $errores[] = 'Debe ser mayor de edad para poder postularse.';
        } elseif ($edad >= 45) {
            $errores[] = 'La edad máxima permitida es de 45 años.';
        }
    }
    if ($nacionalidad === '') $errores[] = 'La nacionalidad es obligatoria.';
    if ($telefono === '')     $errores[] = 'El teléfono de contacto es obligatorio.';
    if ($residencia === '')   $errores[] = 'La dirección de residencia es obligatoria.';
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico es obligatorio y debe tener un formato válido.';
    }

    if (empty($errores)) {
        try {
            $stmtUp = $pdo->prepare('
                UPDATE aspirantes SET 
                    cedula_pasaporte = ?, nombre = ?, apellido = ?, estado_civil = ?, 
                    genero = ?, tipo_sangre = ?, fecha_nacimiento = ?, nacionalidad = ?, 
                    telefono = ?, residencia = ?, correo_electronico = ?
                WHERE usuario_id = ?
            ');
            $stmtUp->execute([$cedula, $nombre, $apellido, $estado_civil, $genero, $tipo_sangre, $fecha_nac, $nacionalidad, $telefono, $residencia, $correo, $usuario_id]);
            $mensaje = 'Tu expediente de Recursos Humanos ha sido guardado exitosamente.';
        } catch (Exception $e) {
            $errores[] = 'Ocurrió un error al intentar guardar tus datos corporativos.';
        }
    }
}

$stmtC = $pdo->prepare('SELECT * FROM aspirantes WHERE usuario_id = ? LIMIT 1');
$stmtC->execute([$usuario_id]);
$aspirante = $stmtC->fetch();

$hoy = new DateTime();
$max_fecha = (clone $hoy)->modify('-18 years')->format('Y-m-d'); // Fecha máxima permitida (para cumplir los 18 años)
$min_fecha = (clone $hoy)->modify('-45 years')->format('Y-m-d'); // Fecha mínima permitida (máximo 45 años)

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aspirante — Formulario de Datos</title>
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="contenedor-ancho">
    <div class="topbar">
        <span class="logo" style="margin-bottom:0;"> Portal de Talento</span>
        <div>
            <span class="usuario"> <?= limpiar($_SESSION['usuario_nombre']) ?></span>&nbsp;|&nbsp;
            <a class="logout" href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <h1>Datos de Recursos Humanos</h1>
    <p style="font-size:13px;color:#777;margin-bottom:20px;">
        Por favor, complete los siguientes campos obligatorios (*) requeridos para procesar su postulación.
    </p>

    <?php if ($mensaje): ?> <div class="exito"> <?= limpiar($mensaje) ?></div> <?php endif; ?>
    <?php if (!empty($errores)): ?>
        <div class="error">
            <ul><?php foreach ($errores as $err): ?> <li><?= limpiar($err) ?></li> <?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="formulario.php">
        <label for="cedula_pasaporte">Cédula o Pasaporte *</label>
        <input type="text" id="cedula_pasaporte" name="cedula_pasaporte" value="<?= limpiar($aspirante['cedula_pasaporte'] ?? '') ?>" required>

        <div class="fila">
            <div>
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="<?= limpiar($aspirante['nombre'] ?? '') ?>" required>
            </div>
            <div>
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" value="<?= limpiar($aspirante['apellido'] ?? '') ?>" required>
            </div>
        </div>

        <div class="fila">
            <div>
                <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
        <input type="date" 
               id="fecha_nacimiento" 
               name="fecha_nacimiento" 
               min="<?= $min_fecha ?>" 
               max="<?= $max_fecha ?>" 
               value="<?= limpiar($aspirante['fecha_nacimiento'] ?? '') ?>" 
               required>
            </div>
            <div>
                <label for="genero">Género *</label>
                <select id="genero" name="genero" required>
                    <option value="" disabled <?= empty($aspirante['genero']) ? 'selected' : '' ?>>Seleccionar...</option>
                    <option value="masculino" <?= (($aspirante['genero'] ?? '') === 'masculino') ? 'selected' : '' ?>>Masculino</option>
                    <option value="femenino" <?= (($aspirante['genero'] ?? '') === 'femenino') ? 'selected' : '' ?>>Femenino</option>
                </select>
            </div>
        </div>

        <div class="fila">
            <div>
                <label for="estado_civil">Estado Civil (Opcional)</label>
                <input type="text" id="estado_civil" name="estado_civil" value="<?= limpiar($aspirante['estado_civil'] ?? '') ?>" placeholder="ej. Soltero/a">
            </div>
            <div>
                <label for="tipo_sangre">Tipo de Sangre (Opcional)</label>
                <input type="text" id="tipo_sangre" name="tipo_sangre" value="<?= limpiar($aspirante['tipo_sangre'] ?? '') ?>" placeholder="ej. O+">
            </div>
        </div>

        <div class="fila">
            <div>
                <label for="nacionalidad">Nacionalidad *</label>
                <input type="text" id="nacionalidad" name="nacionalidad" value="<?= limpiar($aspirante['nacionalidad'] ?? '') ?>" required>
            </div>
            <div>
                <label for="telefono">Teléfono *</label>
                <input type="text" id="telefono" name="telefono" value="<?= limpiar($aspirante['telefono'] ?? '') ?>" required>
            </div>
        </div>

        <label for="correo_electronico">Correo Electrónico *</label>
        <input type="email" id="correo_electronico" name="correo_electronico" value="<?= limpiar($aspirante['correo_electronico'] ?? '') ?>" required
               style="width:100%; padding:8px 10px; border:1px solid #bbb; border-radius:4px; margin-bottom:14px; font-size:14px;">

        <label for="residencia">Dirección de Residencia *</label>
        <textarea id="residencia" name="residencia" placeholder="Provincia, Distrito, Corregimiento, Calle..." required><?= limpiar($aspirante['residencia'] ?? '') ?></textarea>

        <button class="btn btn-bloque" type="submit">Enviar Formulario a RH</button>
    </form>
</div>

<script>
    // Script para poner Edad maxima y minima en la alerta
const fecha = document.getElementById('fecha_nacimiento');

fecha.addEventListener('invalid', function () {
    if (fecha.validity.rangeOverflow) {
        fecha.setCustomValidity('Debe tener al menos 18 años para poder postularse.');
    } else if (fecha.validity.rangeUnderflow) {
        fecha.setCustomValidity('No debe tener más de 45 años para poder postularse.');
    } else if (fecha.validity.valueMissing) {
        fecha.setCustomValidity('La fecha de nacimiento es obligatoria.');
    }
});

fecha.addEventListener('input', function () {
    fecha.setCustomValidity('');
});
</script>
</body>
</html>