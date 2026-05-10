-- ============================================================
-- Migration : aligner une base licences_database EXISTANTE
-- avec le schéma attendu par le projet (PHP + structure.sql).
--
-- Prérequis : MySQL 8.0.16+ (contraintes CHECK).
-- À exécuter une seule fois après sauvegarde de la base.
--
-- Si une étape échoue (contrainte déjà présente, données invalides),
-- lisez le message MySQL et corrigez avant de relancer.
-- ============================================================

USE licences_database;

-- -----------------------------------------------------------
-- Tables manquantes (ajout minimal, sans modifier l'existant)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS APPAREIL (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_appareil VARCHAR(255) NOT NULL UNIQUE,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ACHAT (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (client_id) REFERENCES CLIENT(id),
    CONSTRAINT chk_achat_montant CHECK (montant >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS LIGNE_ACHAT (
    achat_id INT NOT NULL,
    logiciel_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (achat_id, logiciel_id),
    FOREIGN KEY (achat_id) REFERENCES ACHAT(id),
    FOREIGN KEY (logiciel_id) REFERENCES LOGICIEL(id),
    CONSTRAINT chk_ligne_achat_quantite CHECK (quantite >= 1),
    CONSTRAINT chk_ligne_achat_prix CHECK (prix_unitaire >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Normaliser les statuts vides (avant NOT NULL + CHECK)
UPDATE LICENCE SET statut = 'Active' WHERE statut IS NULL OR TRIM(statut) = '';

-- --- Doublons bloquants pour l'unicité (licence_id, nom_appareil) ---
-- Décommentez pour lister les doublons éventuels avant ALTER :
-- SELECT licence_id, nom_appareil, COUNT(*) AS n FROM ACTIVATION GROUP BY licence_id, nom_appareil HAVING n > 1;

-- Unicité : même règle que activer_licence.php (appareil déjà activé)
ALTER TABLE ACTIVATION
    ADD UNIQUE KEY uk_activation_licence_appareil (licence_id, nom_appareil);

-- Prix et plafond d'activations (acheter.php / activer_licence.php)
ALTER TABLE LOGICIEL
    ADD CONSTRAINT chk_logiciel_prix CHECK (prix >= 0);

ALTER TABLE LOGICIEL
    ADD CONSTRAINT chk_logiciel_max_activations CHECK (max_activations >= 1);

-- Statuts de licence (mes_licences.php, activer_licence.php)
-- Si des lignes ont un statut hors liste, normalisez-les avant cette ligne.
ALTER TABLE LICENCE
    MODIFY COLUMN statut VARCHAR(20) NOT NULL DEFAULT 'Active';

ALTER TABLE LICENCE
    ADD CONSTRAINT chk_licence_statut CHECK (statut IN ('Active', 'Expirée', 'Suspendue', 'Désactivée'));
