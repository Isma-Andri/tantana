<?php
// config/database.php

define('DB_HOST',    'localhost');
define('DB_NAME',    'tantana_new');
define('DB_USER',    'root');
define('DB_PASS',    'Isma69_');
define('DB_CHARSET', 'utf8mb4');

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('[Tantana DB] ' . $e->getMessage());
            die('Connexion à la base de données impossible.');
        }
    }

    return $pdo;
}
