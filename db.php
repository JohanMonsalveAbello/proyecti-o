<?php
// ─────────────────────────────────────────────────────────────
//  CONFIGURACIÓN DE BASE DE DATOS
//  Edita estos valores según tu servidor
// ─────────────────────────────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'surtipaez');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('<h2 style="font-family:sans-serif;color:red;padding:20px;">
         Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '<br><br>
         Asegúrate de haber ejecutado <strong>setup.sql</strong> y configurado correctamente <strong>db.php</strong>
         </h2>');
}
?>