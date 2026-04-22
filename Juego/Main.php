<?php
// IMPORTANTE: Las clases DEBEN cargarse antes de iniciar la sesión
require_once 'Heroe.php';
require_once 'Enemigo.php';

session_start();

// 1. INICIALIZACIÓN (Solo si la sesión está vacía)
if (!isset($_SESSION['heroe']) || isset($_GET['reset'])) {
    if(isset($_GET['reset'])) { session_destroy(); session_start(); }
    
    $_SESSION['heroe'] = new Heroe("Héroe Guerrero", "personajes/heroe.jpg");
    $_SESSION['pociones_vida'] = 10; 
    $_SESSION['pociones_mana'] = 5;
    $_SESSION['enemigos'] = [
        new Enemigo("Espectro", "personajes/slime.png", "magia"),
        new Enemigo("Orco", "personajes/duendochad.jpg", "fisico"),
        new Enemigo("Androide", "personajes/cellE.webp", "normal")
    ];
    $_SESSION['log_heroe'] = "¡Comienza la batalla!";
    $_SESSION['log_enemigo'] = "Esperando movimiento...";
}

// Asignamos a variables locales para facilitar el código
$jugador = $_SESSION['heroe'];
$enemigos = $_SESSION['enemigos'];

//
// Estado del juego
$enemigosVivos = array_filter($enemigos, function($e) { return $e->vida > 0; });
$juegoTerminado = ($jugador->vida <= 0) || empty($enemigosVivos);

// 2. PROCESAR ACCIONES
if (isset($_GET['accion']) && !$juegoTerminado) {
    $accion = $_GET['accion'];

    
    // --- TU TURNO ---
    if (($accion == 'fisico' || $accion == 'magia') && $jugador->vida > 0) {
        // Buscar un enemigo que no esté muerto
        if ($jugador->energia <= 0 ){
            $_SESSION['log_heroe'] = "<strong>TU TURNO</strong><br>No tienea Mana<br>Usa una pocion para el Mana";
        }else{    

            $vivosParaAtacar = array_filter($enemigos, function($e) { return $e->vida > 0; });  
            
            if (!empty($vivosParaAtacar)) {
                $indice = array_rand($vivosParaAtacar);
                $objetivo = $enemigos[$indice];                 
                $daño = $jugador->calcularAtaqueRandom($accion, $objetivo);
                $objetivo->vida -= $daño;

                if ($accion == 'magia'){
                    $reg = 10;
                    $jugador->energia = min(50, $jugador->energia - $reg);
                    if ($jugador->energia <= 0 ){
                        
                    }
                }
                $_SESSION['log_heroe'] = "<strong>TU TURNO</strong><br>Usaste ataque: $accion<br>Enemigo: {$objetivo->nombre}<br>Daño realizado: $daño <br> Gasto de mana: $reg mp";
            }
        }    
    }
    elseif ($accion == 'curar_vida') {
       if ($_SESSION['pociones_vida'] > 0) {
        
        // 2. Restar una poción
        $_SESSION['pociones_vida']--; 
        $pocion_actual = $_SESSION['pociones_vida'];

        // 3. Calcular la curación
        $cura = rand(20, 25);
        $jugador->vida = min(70, $jugador->vida + $cura);

        // 4. Registrar el éxito en el log
        $_SESSION['log_heroe'] = "<strong>TU TURNO</strong><br>Usaste: Poción de Vida<br>Curación: $cura HP";
        
    } else {
        // 5. Si no tiene pociones, mostrar mensaje de error
        $_SESSION['log_heroe'] = "<strong>TU TURNO</strong><br>¡No te quedan pociones!";
        }
    }
  elseif ($accion == 'curar_mana') {
    if ($_SESSION['pociones_mana'] > 0) {
        $_SESSION['pociones_mana']--;
        $restantes = $_SESSION['pociones_mana'];
        
        $reg = rand(15, 20);
        $jugador->energia = min(50, $jugador->energia + $reg);
        
        $_SESSION['log_heroe'] = "<strong>TU TURNO</strong><br>Usaste: Poción de Maná<br>Regeneración: $reg MP";
    } else {
        $_SESSION['log_heroe'] = "<strong>TU TURNO</strong><br>¡No tienes pociones de maná!";
    }
}

    // --- TURNO DEL ENEMIGO ---
    $vivosParaContraataque = array_filter($enemigos, function($e) { return $e->vida > 0; });
    if (!empty($vivosParaContraataque) && $jugador->vida > 0) {
        $atacante = $vivosParaContraataque[array_rand($vivosParaContraataque)];
        $tipoEne = (rand(1,2) == 1) ? 'fisico' : 'magia';
        $dañoEne = $atacante->calcularAtaqueRandom($tipoEne, $jugador);
        $jugador->vida -= $dañoEne;
        
        $_SESSION['log_enemigo'] = "<strong>TURNO DE ENEMIGO: {$atacante->nombre}</strong><br>Te atacó con: $tipoEne<br>Daño recibido: $dañoEne";
    }

    // Guardamos los cambios de vuelta a la sesión antes de recargar
    $_SESSION['heroe'] = $jugador;
    $_SESSION['enemigos'] = $enemigos;

    header("Location: Main.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>RPG - Funcional</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: sans-serif; margin: 0; }
        .pantalla { height: 55vh; display: flex; justify-content: center; align-items: center; gap: 50px; padding-top: 20px;}
        .personaje-box { text-align: center; border: 1px solid #444; padding: 15px; background: #222; border-radius: 10px; width: 150px; }
        .personaje-box img { width: 100%; height: 120px; object-fit: cover; border-radius: 5px; background: #000;}
        
        .bar-container { background: #444; height: 12px; width: 100%; border-radius: 6px; margin: 8px 0; overflow: hidden; border: 1px solid #000; }
        .hp-fill { background: #ff4d4d; height: 100%; transition: width 0.4s; }
        .mp-fill { background: #3498db; height: 100%; transition: width 0.4s; }
        
        .interfaz { position: fixed; bottom: 0; width: 100%; height: 230px; background: #0a0a0a; border-top: 4px solid #f1c40f; display: flex; padding: 20px; box-sizing: border-box; }
        .menu { flex: 1; border-right: 1px solid #333; display: flex; flex-direction: column; }
        .consola { flex: 2; display: flex; gap: 20px; padding-left: 20px; }
        .log-box { flex: 1; padding: 15px; background: #111; border: 1px solid #333; font-size: 0.95em; color: #0f0; line-height: 1.4; }
        
        .btn { display: inline-block; padding: 10px; margin: 3px; background: #333; color: white; text-decoration: none; border: 1px solid #777; font-weight: bold; text-align: center; font-size: 13px; }
        .btn:hover { background: #f1c40f; color: black; }
    </style>
</head>
<body>

<?php if ($juegoTerminado): ?>
    <?php $gano = $jugador->vida > 0; ?>
    <div style="position:fixed; inset:0; background:rgba(0,0,0,0.92); display:flex; flex-direction:column; justify-content:center; align-items:center; z-index:999;">
        <h1 style="font-size:64px; color:<?= $gano ? '#f1c40f' : '#ff4d4d' ?>; margin:0;">
            <?= $gano ? '¡VICTORIA!' : 'DERROTA' ?>
        </h1>
        <p style="color:white; font-size:20px; margin:20px 0;">
            <?= $gano ? 'Has derrotado a todos los enemigos.' : 'Has caído en batalla.' ?>
        </p>
        <a href="?reset=1" class="btn" style="padding:15px 40px; font-size:18px; background:#f1c40f; color:black; text-decoration:none;">
            JUGAR DE NUEVO
        </a>
    </div>
<?php endif; ?>

    <div class="pantalla">
        <div class="personaje-box" style="<?= $jugador->vida <= 0 ? 'opacity:0.2; filter: grayscale(1);' : '' ?>">
            <img src="<?= $jugador->imagen ?>" onerror="this.src='https://via.placeholder.com/150?text=Heroe'">
            <strong><?= $jugador->nombre ?></strong>
            <div class="bar-container"><div class="hp-fill" style="width: <?= max(0, ($jugador->vida/70)*100) ?>%"></div></div>
            <small>HP: <?= max(0, $jugador->vida) ?> / 70</small>
            <div class="bar-container"><div class="mp-fill" style="width: <?= ($jugador->energia/50)*100 ?>%"></div></div>
            <small>MP: <?= $jugador->energia ?> / 50</small>
        </div>

        <h1 style="color:#444">VS</h1>

        <div style="display: flex; gap: 15px;">
            <?php foreach ($enemigos as $e): ?>
            <div class="personaje-box" style="<?= $e->vida <= 0 ? 'opacity:0.2; filter: grayscale(1);' : '' ?>">
                <img src="<?= $e->imagen ?>" onerror="this.src='https://via.placeholder.com/150?text=Enemigo'">
                <strong><?= $e->nombre ?></strong>
                <div class="bar-container"><div class="hp-fill" style="width: <?= max(0, ($e->vida/50)*100) ?>%"></div></div>
                <small>HP: <?= max(0, $e->vida) ?> / 50</small><br>
                <small style="color: #f1c40f">Debilidad: <?= $e->tipoDebilidad ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="interfaz">
        <div class="menu">
            <strong>ATAQUE</strong>
            <div style="display: flex;">
                <a href="?accion=fisico" class="btn" style="flex:1">FÍSICO</a>
                <a href="?accion=magia" class="btn" style="flex:1">MAGIA</a>
            </div>
            <strong>OBJETOS</strong>
            <div style="display: flex;">
                <a href="?accion=curar_vida" class="btn" style="flex:1">POCIÓN VIDA (<?= $_SESSION['pociones_vida'] ?>)</a>
                <a href="?accion=curar_mana" class="btn" style="flex:1">POCIÓN MANÁ (<?= $_SESSION['pociones_mana'] ?>)</a>
            </div>
            <a href="?reset=1" style="color:#666; font-size:11px; margin-top:10px; text-align:center;">Reiniciar Batalla</a>
        </div>
        
        <div class="consola">
            <div class="log-box"><?= $_SESSION['log_heroe'] ?></div>
            <div class="log-box" style="color: #ff4d4d; border-color: #522;"><?= $_SESSION['log_enemigo'] ?></div>
        </div>
    </div>

</body>
</html>
