<?php
require_once 'connect.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM LOGICIEL WHERE IdLogic = ?');
$stmt->execute([$id]);
$logiciel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$logiciel) {
    echo '<p class="error">Logiciel introuvable.</p>';
    exit;
}

$icon = '📦';
if (strpos($logiciel['NomLogic'], 'Code') !== false) {
    $icon = '💻';
} elseif (strpos($logiciel['NomLogic'], 'Pixel') !== false) {
    $icon = '🎨';
} elseif (strpos($logiciel['NomLogic'], 'Secure') !== false) {
    $icon = '🔒';
} elseif (strpos($logiciel['NomLogic'], 'Data') !== false) {
    $icon = '📊';
}

echo '<div class="detail-header">';
echo '<div class="detail-icon">' . $icon . '</div>';
echo '<h2>' . htmlspecialchars($logiciel['NomLogic']) . '</h2>';
echo '</div>';

echo '<div class="detail-meta">';
echo '<span>Version ' . htmlspecialchars($logiciel['VersionLogic']) . '</span>';
echo '<span>' . (int) $logiciel['MaxActivationsLogic'] . ' activation(s) max</span>';
echo '</div>';

echo '<div class="detail-price">' . number_format((float) $logiciel['PrixLogic'], 2, ',', ' ') . ' €</div>';
echo '<div class="detail-desc">' . nl2br(htmlspecialchars($logiciel['DescriptionLogic'] ?? '')) . '</div>';

$rawFeatures = $logiciel['FonctionnalitesLogic'] ?? '';
$features = [];
if ($rawFeatures !== '') {
    foreach (explode('|', $rawFeatures) as $f) {
        $f = trim($f);
        if ($f !== '') {
            $features[] = $f;
        }
    }
}
echo '<ul class="detail-features">';
foreach ($features as $f) {
    echo '<li>' . htmlspecialchars(trim($f)) . '</li>';
}
echo '</ul>';

echo '<a href="paiement.html?id=' . (int) $logiciel['IdLogic'] . '" class="btn btn-primary btn-full">Acheter cette licence</a>';
