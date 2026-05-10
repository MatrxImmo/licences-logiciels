<?php
// ============================================================
// init_db.php — Exécute sql/structure.sql (schéma canonique)
// Les INSERT de démo ne sont rejoués que si LOGICIEL est vide.
// ============================================================

$sqlFile = __DIR__ . '/../sql/structure.sql';

/**
 * Retire les lignes de commentaires SQL commençant par --.
 */
function init_db_strip_line_comments(string $sql): string
{
    $out = '';
    foreach (explode("\n", $sql) as $line) {
        if (trim($line) === '' || strncmp(trim($line), '--', 2) === 0) {
            continue;
        }
        $out .= $line . "\n";
    }
    return $out;
}

/**
 * Découpe grossièrement sur ';' (structure.sql n'a pas de ';' dans les chaînes).
 *
 * @return list<string>
 */
function init_db_split_statements(string $sql): array
{
    $parts = explode(';', $sql);
    $stmts = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '') {
            $stmts[] = $p;
        }
    }
    return $stmts;
}

try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $raw = file_get_contents($sqlFile);
    if ($raw === false) {
        throw new RuntimeException('Fichier introuvable : ' . $sqlFile);
    }

    $clean = init_db_strip_line_comments($raw);
    $all = init_db_split_statements($clean);
    $ddl = [];
    $inserts = [];
    foreach ($all as $stmt) {
        if (preg_match('/^\s*INSERT\s+/i', $stmt)) {
            $inserts[] = $stmt;
        } else {
            $ddl[] = $stmt;
        }
    }

    foreach ($ddl as $stmt) {
        $pdo->exec($stmt);
    }

    $pdo->exec('USE licences_database');
    $count = (int) $pdo->query('SELECT COUNT(*) FROM LOGICIEL')->fetchColumn();
    if ($count === 0) {
        foreach ($inserts as $stmt) {
            $pdo->exec($stmt);
        }
    }

    echo "Base initialisée avec succès ! <a href='../login.html'>Aller à la connexion</a>";
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
} catch (Throwable $e) {
    die('Erreur : ' . $e->getMessage());
}
