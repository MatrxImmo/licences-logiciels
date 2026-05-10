<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $password = $_POST['mot_de_passe'];
    $confirm = $_POST['confirm'];

    // Vérifier que les mots de passe correspondent
    if ($password !== $confirm) {
        header('Location: ../login.html?error=Les mots de passe ne correspondent pas');
        exit;
    }

    // Vérifier si l'email existe déjà (EmailClient = sql/structure.sql)
    $stmt = $pdo->prepare("SELECT IdNumClient FROM CLIENT WHERE EmailClient = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ../login.html?error=Cet email est déjà utilisé');
        exit;
    }

    // Hash du mot de passe en SHA256 (simple, comme demandé)
    $hashed = hash('sha256', $password);

    // Insertion : AdrClient reçoit le téléphone (pas de champ téléphone dans structure.sql)
    $stmt = $pdo->prepare(
        "INSERT INTO CLIENT (NomClient, PrenomClient, AdrClient, EmailClient, MotDePasseClient) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nom, $prenom, $telephone, $email, $hashed]);

    // Connexion automatique
    session_start();
    $_SESSION['client_id'] = $pdo->lastInsertId();
    $_SESSION['client_nom'] = $nom;
    $_SESSION['client_email'] = $email;

    header('Location: ../catalogue.html');
    exit;
}
?>