<?php
$host = 'localhost';
$dbname = 'Tantana';
$user = 'root';
$pass = 'Isma69_';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch(PDOException $e) {
    die("Erreur lors de la connexion: " . $e->getMessage());
}
?>
