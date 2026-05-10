<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['client_id'])) {
    header('Location: ../login.html');
    exit;
}

$client_id = (int) $_SESSION['client_id'];
$logiciel_id = (int) ($_POST['logiciel_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM LOGICIEL WHERE IdLogic = ?');
$stmt->execute([$logiciel_id]);
$logiciel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$logiciel) {
    die('Logiciel introuvable.');
}

$prix = (float) $logiciel['PrixLogic'];

$nextAchat = (int) $pdo->query('SELECT COALESCE(MAX(IdAchat), 0) + 1 FROM ACHETER')->fetchColumn();

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO ACHETER (IdAchat, MontAchat) VALUES (?, ?)');
    $stmt->execute([$nextAchat, $prix]);

    $stmt = $pdo->prepare('INSERT INTO RECU (IdLogic, IdNumClient, PrixRec) VALUES (?, ?, ?)');
    $stmt->execute([$logiciel_id, $client_id, $prix]);
    $codRec = (int) $pdo->lastInsertId();

    $cle = strtoupper(
        substr(md5(uniqid((string) mt_rand(), true)), 0, 8) . '-' .
        substr(md5(uniqid((string) mt_rand(), true)), 0, 4) . '-' .
        substr(md5(uniqid((string) mt_rand(), true)), 0, 4) . '-' .
        substr(md5(uniqid((string) mt_rand(), true)), 0, 12)
    );
    $expiration = date('Y-m-d H:i:s', strtotime('+1 year'));

    $stmt = $pdo->prepare(
        'INSERT INTO LICENCE (IdLicen, `CléLicen`, IdLogic, DatExpiLicen, StatuLicen) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$codRec, $cle, $logiciel_id, $expiration, 'Active']);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    die('Erreur lors de l\'achat : ' . $e->getMessage());
}

header(
    'Location: ../succes.html?key=' . urlencode($cle) .
    '&logiciel=' . urlencode($logiciel['NomLogic']) .
    '&expiration=' . urlencode($expiration) .
    '&activations=' . (int) $logiciel['MaxActivationsLogic']
);
exit;
