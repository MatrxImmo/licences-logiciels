<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['mot_de_passe'] ?? '';

    // Requête sécurisée avec requête préparée (colonnes = sql/structure.sql)
    $stmt = $pdo->prepare("SELECT * FROM CLIENT WHERE EmailClient = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && hash('sha256', $password) === $user['MotDePasseClient']) {
        $_SESSION['client_id'] = $user['IdNumClient'];
        $_SESSION['client_nom'] = $user['NomClient'];
        $_SESSION['client_email'] = $user['EmailClient'];
        header('Location: ../catalogue.html');
        exit;
    } else {
        header('Location: ../login.html?error=Identifiants incorrects');
        exit;
    }
}
?>