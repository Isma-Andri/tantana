<?php
define('DB_HOST',    'localhost');
define('DB_NAME',    'Tantana');
define('DB_USER',    'root');
define('DB_PASS',    'Isma69_');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn     = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[Tantana][getDB] ' . $e->getMessage());
            $msg = 'Connexion a la base de donnees impossible.';
            // Affiche le detail uniquement en mode dev (desactiver en prod)
            if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
                $msg .= ' Detail : ' . $e->getMessage();
            }
            // Affichage d'une page d'erreur propre
            http_response_code(500);
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
            <title>Erreur - Tantana</title>
            <style>body{font-family:sans-serif;background:#0b0f1a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
            .box{background:#16213e;border:1px solid #1e2d4a;border-radius:12px;padding:40px;max-width:480px;text-align:center}
            h2{color:#ef4444;margin-bottom:12px}p{color:#94a3b8;font-size:.95rem}
            a{color:#4f8ef7;text-decoration:none}</style></head><body>
            <div class="box"><h2>Erreur de connexion</h2>
            <p>' . htmlspecialchars($msg) . '</p>
            <p style="margin-top:16px"><a href="javascript:history.back()">Retour</a></p>
            </div></body></html>';
            exit;
        }
    }
    return $pdo;
}
