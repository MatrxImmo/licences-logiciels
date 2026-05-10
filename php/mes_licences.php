<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['client_id'])) {
    echo '<tr><td colspan="7">Veuillez vous connecter.</td></tr>';
    exit;
}

$client_id = (int) $_SESSION['client_id'];

$stmt = $pdo->prepare(
    'SELECT L.IdLicen, L.`CléLicen`, L.DatExpiLicen, L.StatuLicen,
            G.NomLogic, G.MaxActivationsLogic,
            (SELECT COUNT(*) FROM ACTIVATION A WHERE A.IdLicen = L.IdLicen) AS nb_activations
     FROM LICENCE L
     JOIN RECU R ON L.IdLicen = R.CodRec AND L.IdLogic = R.IdLogic
     JOIN LOGICIEL G ON L.IdLogic = G.IdLogic
     WHERE R.IdNumClient = ?
     ORDER BY L.IdLicen DESC'
);
$stmt->execute([$client_id]);
$licences = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($licences)) {
    echo '<tr><td colspan="7" style="text-align:center; padding:32px;">Aucune licence achetée pour le moment.</td></tr>';
    exit;
}

foreach ($licences as $licence) {
    $statusClass = ($licence['StatuLicen'] === 'Active') ? 'status-active' : 'status-expired';
    $cle = $licence['CléLicen'];
    echo '<tr>';
    echo '<td>' . htmlspecialchars($licence['NomLogic']) . '</td>';
    echo '<td>—</td>';
    echo '<td style="font-family:monospace; font-size:13px;">' . htmlspecialchars($cle) . '</td>';
    echo '<td class="' . $statusClass . '">' . htmlspecialchars($licence['StatuLicen']) . '</td>';
    echo '<td>' . date('d/m/Y', strtotime($licence['DatExpiLicen'])) . '</td>';
    echo '<td>' . (int) $licence['nb_activations'] . ' / ' . (int) $licence['MaxActivationsLogic'] . '</td>';
    echo '<td><a href="activation.html?cle=' . urlencode($cle) . '" class="btn btn-small btn-primary">Activer</a></td>';
    echo '</tr>';
}
