<?php
require_once 'connect.php';

$stmt = $pdo->query('SELECT * FROM LOGICIEL ORDER BY NomLogic');
$logiciels = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($logiciels as $logiciel) {
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

    echo '<div class="software-card" onclick="window.location.href=\'detail.html?id=' . (int) $logiciel['IdLogic'] . '\'">';
    echo '<div class="card-icon">' . $icon . '</div>';
    echo '<h3>' . htmlspecialchars($logiciel['NomLogic']) . '</h3>';
    echo '<div class="card-version">v' . htmlspecialchars($logiciel['VersionLogic']) . '</div>';
    echo '<div class="card-type">Jusqu\'à ' . (int) $logiciel['MaxActivationsLogic'] . ' appareil(s)</div>';
    echo '<div class="card-price">' . number_format((float) $logiciel['PrixLogic'], 2, ',', ' ') . ' €</div>';
    echo '<div class="card-desc">' . htmlspecialchars($logiciel['DescriptionLogic'] ?? '') . '</div>';
    echo '</div>';
}
