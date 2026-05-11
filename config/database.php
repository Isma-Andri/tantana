<?php
// config/database.php
// Connexion PDO à la base de données tantana_new

define('DB_HOST', 'localhost');
define('DB_NAME', 'tantana_new');
define('DB_USER', 'root');       // À modifier selon votre environnement
define('DB_PASS', 'Isma69_');           // À modifier selon votre environnement
define('DB_CHARSET', 'utf8mb4');

/**
 * Retourne une instance PDO (singleton simple).
 * Lève une exception en cas d'échec de connexion.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En production, ne pas exposer le message d'erreur brut
            error_log('[Tantana DB] ' . $e->getMessage());
            die(json_encode(['error' => 'Connexion à la base de données impossible.']));
        }
    }

    return $pdo;
}
