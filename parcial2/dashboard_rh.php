<?php

require_once 'config.php';
verificarSesion();

if ($_SESSION['usuario_rol'] !== 'rh') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$notificacion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aspirante_id'], $_POST['cambio_estado'])) {
    $asp_id = (int)$_POST['aspirante_id'];
    $estado_nuevo = $_POST['cambio_estado'];

    if (in_array($estado_nuevo, ['no considerado', 'no revisado', 'considerado'])) {
        try {
            $stmtUp = $pdo->prepare('UPDATE aspirantes SET estado = ? WHERE id = ?');
            $stmtUp->execute([$estado_nuevo, $asp_id]);
            $notificacion = 'El estado de la postulación fue actualizado.';
        } catch (Exception $e) {
            $notificacion = 'Fallo interno al actualizar el estado.';
        }
    }
}

// Carga de todas las solicitudes de empleo registradas en el sistema
$solicitudes = $pdo->query('SELECT * FROM aspirantes ORDER BY updated_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RH — Gestión de Solicitudes</title>
<link rel="stylesheet" href="estilo.css">
<style>
    .tabla-gestion { width:100%; border-collapse:collapse; margin-top:15px; font-size:13px; }
    .tabla-gestion th { background:#2c3e50; color:#fff; padding:10px; text-align:left; }
    .tabla-gestion td { padding:10px; border-bottom:1px solid #ddd; }
    .cambio-estado-select { width:auto; display:inline-block; padding:4px; margin-bottom:0; font-size:12px; }
    .badge-estado { padding:3px 6px; border-radius:3px; font-weight:bold; font-size:11px; text-transform:uppercase; }
    .status-no_revisado { background:#f39c12; color:#fff; }
    .status-considerado { background:#2ecc71; color:#fff; }
    .status-no_considerado { background:#e74c3c; color:#fff; }
</style>
</head>
<body>
<div class="contenedor-ancho" style="max-width:900px;">
    <div class="topbar">
        <span class="logo" style="margin-bottom:0;"> Módulo Reclutamiento (RH)</span>
        <div>
            <span class="usuario">Analista: <strong><?= limpiar($_SESSION['usuario_nombre']) ?></strong></span>&nbsp;|&nbsp;
            <a class="logout" href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <h1>Solicitudes de Empleo Recibidas</h1>
    <p style="font-size:13px;color:#777;">Control de aspirantes registrados en el sistema.</p>

    <?php if ($notificacion): ?> <div class="exito" style="text-align:left;"> <?= limpiar($notificacion) ?></div> <?php endif; ?>

    <table class="tabla-gestion">
        <thead>
            <tr>
                <th>Cédula/Pasaporte</th>
                <th>Aspirante</th>
                <th>Datos de Contacto</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($solicitudes)): ?>
                <tr><td colspan="5" style="text-align:center; color:#999;">Ningún aspirante se ha registrado aún.</td></tr>
            <?php else: ?>
                <?php foreach ($solicitudes as $sol): 
                    $clase_badge = str_replace(' ', '_', $sol['estado']);
                ?>
                <tr>
                    <td><strong><?= limpiar($sol['cedula_pasaporte']) ?></strong></td>
                    <td><?= limpiar($sol['nombre'] . ' ' . $sol['apellido']) ?><br><small style="color:#666;"><?= limpiar($sol['nacionalidad']) ?> | <?= limpiar($sol['genero']) ?></small></td>
                    <td><?= limpiar($sol['correo_electronico']) ?><br><small><?= limpiar($sol['telefono']) ?></small></td>
                    <td><span class="badge-estado status-<?= $clase_badge ?>"><?= limpiar($sol['estado']) ?></span></td>
                    <td>
                        <form method="POST" action="dashboard_rh.php" style="display:inline-flex; gap:5px; align-items:center;">
                            <input type="hidden" name="aspirante_id" value="<?= (int)$sol['id'] ?>">
                            <select name="cambio_estado" class="cambio-estado-select" required>
                                <option value="no revisado" <?= $sol['estado'] === 'no revisado' ? 'selected' : '' ?>>No Revisado</option>
                                <option value="considerado" <?= $sol['estado'] === 'considerado' ? 'selected' : '' ?>>Considerado</option>
                                <option value="no considerado" <?= $sol['estado'] === 'no considerado' ? 'selected' : '' ?>>No Considerado</option>
                            </select>
                            <button type="submit" class="btn" style="padding:4px 8px; font-size:12px;">Cambiar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>