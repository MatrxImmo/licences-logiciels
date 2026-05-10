<?php
// ============================================================
// connect.php — Connexion à la base de données MySQL
// ============================================================

$host = 'localhost';     // Serveur WAMP
$dbname = 'licences_database';  // Nom de la base
$username = 'root';      // Utilisateur par défaut WAMP
$password = '';          // Mot de passe vide par défaut

try {
    // Connexion avec PDO (sécurisé et simple)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Affiche les erreurs SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'erreur, on arrête tout et on affiche le message
    die("Erreur de connexion à la base : " . $e->getMessage());
}
?>