<?php
session_start();
require_once 'connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cle = $_POST['cle'] ?? '';
    $nom_appareil = trim($_POST['nom_appareil'] ?? '');

    if ($cle === '' || $nom_appareil === '') {
        $response['message'] = 'Veuillez remplir tous les champs.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM LICENCE WHERE `CléLicen` = ?');
    $stmt->execute([$cle]);
    $licence = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$licence) {
        $response['message'] = 'Clé de licence invalide.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($licence['StatuLicen'] !== 'Active') {
        $response['message'] = 'Cette licence n\'est pas active.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (strtotime($licence['DatExpiLicen']) < time()) {
        $response['message'] = 'Cette licence a expiré.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $idLicen = (int) $licence['IdLicen'];
    $idLogic = (int) $licence['IdLogic'];

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ACTIVATION WHERE IdLicen = ?');
    $stmt->execute([$idLicen]);
    $nb_activations = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT MaxActivationsLogic FROM LOGICIEL WHERE IdLogic = ?');
    $stmt->execute([$idLogic]);
    $max_activations = (int) $stmt->fetchColumn();

    if ($nb_activations >= $max_activations) {
        $response['message'] = 'Nombre maximum d\'activations atteint (' . $max_activations . ').';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('SELECT IdAppa FROM APPAREIL WHERE NomAppa = ?');
    $stmt->execute([$nom_appareil]);
    $idAppa = $stmt->fetchColumn();

    if ($idAppa === false) {
        $nextAppa = (int) $pdo->query('SELECT COALESCE(MAX(IdAppa), 0) + 1 FROM APPAREIL')->fetchColumn();
        $ins = $pdo->prepare('INSERT INTO APPAREIL (IdAppa, NomAppa) VALUES (?, ?)');
        $ins->execute([$nextAppa, $nom_appareil]);
        $idAppa = $nextAppa;
    } else {
        $idAppa = (int) $idAppa;
    }

    $stmt = $pdo->prepare('SELECT NbreActiv FROM ACTIVATION WHERE IdLicen = ? AND IdAppa = ?');
    $stmt->execute([$idLicen, $idAppa]);
    if ($stmt->fetch()) {
        $response['message'] = 'Cet appareil est déjà activé pour cette licence.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $cleActiv = strtoupper(
        substr(md5(uniqid((string) mt_rand(), true)), 0, 10) . '-' .
        substr(md5(uniqid((string) mt_rand(), true)), 0, 10)
    );

    $stmt = $pdo->prepare('INSERT INTO ACTIVATION (`CléActiv`, IdLicen, IdAppa) VALUES (?, ?, ?)');
    $stmt->execute([$cleActiv, $idLicen, $idAppa]);

    $response['success'] = true;
    $response['message'] = 'Licence activée avec succès sur « ' . $nom_appareil . ' » !';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$response['message'] = 'Requête invalide.';
echo json_encode($response, JSON_UNESCAPED_UNICODE);
