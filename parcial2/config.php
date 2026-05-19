<?php
// config.php — Configuración del sistema de Recursos Humanos

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_NAME', 'rh_aspirantes');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Protección contra filtrado de entorno: No se muestra $e->getMessage()
            die('<div style="font-family:sans-serif;background:#fff5f5;color:#c0392b;padding:2rem;border:1px solid #ebccd1;border-radius:4px;margin:2rem;text-align:center;">
                <strong>❌ Error de conexión:</strong> Ocurrió un problema al conectar con el servidor de datos. Por favor, contacte al administrador.</div>');
        }
    }
    return $pdo;
}

function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false, 
            'httponly' => true,  // Mitigación XSS en cookies de sesión
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function verificarSesion(): void {
    iniciarSesion();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

function limpiar(?string $valor): string {
    if ($valor === null) return '';
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8'); // Mitigación XSS general
}