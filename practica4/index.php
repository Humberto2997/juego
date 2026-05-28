<?php
session_start();

$archivo_tareas = 'tareas.json';
$archivo_usuarios = 'usuarios.json';

// --- FUNCIONES DE PERSISTENCIA ---

function leerDatos($archivo) {
    if (!file_exists($archivo)) return [];
    $json = file_get_contents($archivo);
    return json_decode($json, true) ?? [];
}

function guardarDatos($datos, $archivo) {
    $json = json_encode($datos, JSON_PRETTY_PRINT);
    file_put_contents($archivo, $json);
}

function siguienteId($tareas) {
    if (empty($tareas)) return 1;
    return max(array_column($tareas, 'id')) + 1;
}

$mensaje = '';

// --- PROCESAR INICIO / CIERRE DE SESIÓN ---
if (isset($_POST['accion_sesion'])) {
    if ($_POST['accion_sesion'] === 'login') {
        $user_input = $_POST['usuario'];
        $pass_input = $_POST['clave'];
        
        $usuarios = leerDatos($archivo_usuarios);
        $autenticado = false;
        
        foreach ($usuarios as $u) {
            // SOLUCIÓN AL WARNING: Validamos que existan las llaves antes de comparar
            if (isset($u['usuario']) && isset($u['clave'])) {
                if ($u['usuario'] === $user_input && $u['clave'] === $pass_input) {
                    $_SESSION['usuario'] = $u['usuario'];
                    $_SESSION['nombre'] = $u['nombre'] ?? $u['usuario'];
                    $autenticado = true;
                    break;
                }
            }
        }
        
        if (!$autenticado) {
            $mensaje = "Usuario o contraseña incorrectos.";
        }
    } elseif ($_POST['accion_sesion'] === 'logout') {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}

// --- PROCESAR ACCIONES DE TAREAS (Solo si hay sesión activa) ---
if (isset($_SESSION['usuario']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $tareas = leerDatos($archivo_tareas);
    $accion = $_POST['accion'];
    $usuario_actual = $_SESSION['usuario'];

    if ($accion === 'agregar') {
        $tareas[] = [
            'id'      => siguienteId($tareas),
            'usuario' => $usuario_actual,
            'tarea'   => $_POST['tarea'],
            'estado'  => 'por hacer'
        ];
        guardarDatos($tareas, $archivo_tareas);
        $mensaje = "Tarea agregada con éxito.";

    } elseif ($accion === 'cambiar_estado') {
        $id = (int)$_POST['id'];
        foreach ($tareas as &$t) {
            if (isset($t['id']) && isset($t['usuario'])) {
                if ($t['id'] === $id && $t['usuario'] === $usuario_actual) {
                    $t['estado'] = ($t['estado'] === 'por hacer') ? 'hecha' : 'por hacer';
                    break;
                }
            }
        }
        guardarDatos($tareas, $archivo_tareas);
        $mensaje = "Estado de la tarea actualizado.";

    } elseif ($accion === 'eliminar') {
        $id = (int)$_POST['id'];
        $tareas = array_filter($tareas, function($t) use ($id, $usuario_actual) {
            if (isset($t['id']) && isset($t['usuario'])) {
                return !($t['id'] === $id && $t['usuario'] === $usuario_actual);
            }
            return true;
        });
        guardarDatos(array_values($tareas), $archivo_tareas);
        $mensaje = "Tarea eliminada correctamente.";
    }
}

// --- OBTENER TAREAS DEL USUARIO ACTUAL ---
$tareas_usuario = [];
if (isset($_SESSION['usuario'])) {
    $todas_las_tareas = leerDatos($archivo_tareas);
    foreach ($todas_las_tareas as $t) {
        // PROTECCIÓN ADICIONAL: Evita warnings si hay tareas antiguas corruptas
        if (isset($t['usuario']) && $t['usuario'] === $_SESSION['usuario']) {
            $tareas_usuario[] = $t;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Tareas - JSON</title>
    <style>
        .hecha { text-decoration: line-through; color: gray; }
        .badge-pendiente { color: orange; font-weight: bold; }
        .badge-completada { color: green; font-weight: bold; }
    </style>
</head>
<body>

<h1>Sistema de Gestión de Tareas (To-Do List)</h1>
<hr>

<?php if ($mensaje): ?>
    <p><b><?= $mensaje ?></b></p>
<?php endif; ?>

<?php if (!isset($_SESSION['usuario'])): ?>
    <h2>Iniciar Sesión</h2>
    <form method="POST">
        <input type="hidden" name="accion_sesion" value="login">
        <label>Usuario: <input type="text" name="usuario" required></label><br><br>
        <label>Contraseña: <input type="password" name="clave" required></label><br><br>
        <button type="submit">Entrar</button>
    </form>


<?php else: ?>
    <p>Bienvenido, <b><?= htmlspecialchars($_SESSION['nombre']) ?></b> (<?= htmlspecialchars($_SESSION['usuario']) ?>) | 
    <form method="POST" style="display:inline;">
        <input type="hidden" name="accion_sesion" value="logout">
        <button type="submit">Cerrar Sesión</button>
    </form></p>
    
    <hr>

    <h2>Agregar Nueva Tarea</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="agregar">
        <label>¿Qué tienes pendiente por hacer?: <input type="text" name="tarea" size="40" required></label>
        <button type="submit">Agregar</button>
    </form>

    <hr>

    <h2>Mis Tareas Pendientes</h2>
    <?php if (empty($tareas_usuario)): ?>
        <p>No tienes tareas pendientes. ¡Buen trabajo!</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Tarea</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($tareas_usuario as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td class="<?= $t['estado'] === 'hecha' ? 'hecha' : '' ?>">
                    <?= htmlspecialchars($t['tarea']) ?>
                </td>
                <td>
                    <span class="<?= $t['estado'] === 'hecha' ? 'badge-completada' : 'badge-pendiente' ?>">
                        <?= ucfirst($t['estado']) ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="cambiar_estado">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit">
                            <?= $t['estado'] === 'por hacer' ? 'Marcar como Hecha' : 'Reabrir' ?>
                        </button>
                    </form>

                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" style="color:red;">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

<?php endif; ?>

</body>
</html>