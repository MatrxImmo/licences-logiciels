-- ============================================================
-- Base de données : licences_database
-- Projet : Gestion de licences logicielles
-- MySQL 8.0.16+ recommandé (contraintes CHECK)
-- ============================================================

CREATE DATABASE IF NOT EXISTS licences_database
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE licences_database;

-- -----------------------------------------------------------
-- Table CLIENT : stocke les utilisateurs inscrits
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS CLIENT (
    IdNumClient INT AUTO_INCREMENT PRIMARY KEY,
    NomClient VARCHAR(100) NOT NULL,
    PrenomClient VARCHAR(100) NOT NULL,
    AdrClient VARCHAR(255) NOT NULL,
    EmailClient VARCHAR(255) NOT NULL UNIQUE,
    MotDePasseClient VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table LOGICIEL : tous les logiciels disponibles sur la plateforme
-- Règles alignées avec acheter.php, detail.php, activer_licence.php
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS LOGICIEL (
    IdLogic INT PRIMARY KEY,
    NomLogic VARCHAR(255) NOT NULL,
    VersionLogic VARCHAR(50) NOT NULL,
    PrixLogic DECIMAL(10,2) NOT NULL,
    DescriptionLogic TEXT,
    FonctionnalitesLogic TEXT,
    MaxActivationsLogic INT NOT NULL DEFAULT 3,
    CONSTRAINT chk_logiciel_prix CHECK (PrixLogic >= 0),
    CONSTRAINT chk_logiciel_max_activations CHECK (MaxActivationsLogic >= 3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table ACHAT : historise les achats réalisés par les clients
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS ACHETER (
    IdAchat INT PRIMARY KEY,
    DatAchat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MontAchat DECIMAL(10,2) NOT NULL,
    CONSTRAINT chk_achat_montant CHECK (MontAchat >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table LIGNE_ACHAT : détail des logiciels achetés
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS RECU (
    CodRec INT AUTO_INCREMENT,
    IdLogic INT NOT NULL,
    IdNumClient INT NOT NULL,
    PrixRec DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (CodRec, IdLogic),
    FOREIGN KEY (IdNumClient) REFERENCES CLIENT(IdNumClient),
    FOREIGN KEY (IdLogic) REFERENCES LOGICIEL(IdLogic),
    CONSTRAINT chk_ligne_achat_prix CHECK (PrixRec >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table LICENCE : générée automatiquement après achat
-- statut : valeurs utilisées dans mes_licences.php et activer_licence.php
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS LICENCE (
    IdLicen INT PRIMARY KEY,
    `CléLicen` VARCHAR(50) NOT NULL UNIQUE,
    IdLogic INT NOT NULL,
    DatExpiLicen DATETIME NOT NULL,
    StatuLicen VARCHAR(20) NOT NULL DEFAULT 'Active',
    FOREIGN KEY (IdLogic) REFERENCES LOGICIEL(IdLogic),
    CONSTRAINT chk_licence_statut CHECK (StatuLicen IN ('Active', 'Expirée', 'Suspendue', 'Désactivée'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table APPAREIL : référentiel des appareils pouvant être activés
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS APPAREIL (
    IdAppa INT PRIMARY KEY,
    NomAppa VARCHAR(255) NOT NULL,
    TypeAppa VARCHAR(100),
    NbreActiv INT AUTO_INCREMENT NOT NULL,
    UNIQUE KEY uk_appareil_nom (NomAppa),
    UNIQUE KEY uk_appareil_nbreactiv (NbreActiv)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table ACTIVATION : enregistre chaque activation par appareil
-- Un même nom d'appareil ne peut pas être activé deux fois pour la même licence
-- (règle dans activer_licence.php)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS ACTIVATION (
    NbreActiv INT AUTO_INCREMENT PRIMARY KEY,
    `CléActiv` VARCHAR(50) NOT NULL UNIQUE,
    IdLicen INT NOT NULL,
    IdAppa INT NOT NULL,
    DatActiv DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IdLicen) REFERENCES LICENCE(IdLicen),
    FOREIGN KEY (IdAppa) REFERENCES APPAREIL(IdAppa),
    UNIQUE KEY uk_activation_licence_appareil (IdLicen, IdAppa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Données fictives : 4 logiciels de démonstration
-- fonctionnalités : séparées par | (detail.php utilise explode('|', ...))
-- -----------------------------------------------------------
INSERT INTO LOGICIEL (IdLogic, NomLogic, VersionLogic, PrixLogic, DescriptionLogic, FonctionnalitesLogic, MaxActivationsLogic) VALUES
(1, 'CodeForge Pro', '3.2.1', 49.99, 'IDE de développement intelligent avec assistance IA intégrée.', 'Auto-complétion avancée|Débogueur visuel|Intégration Git|Thèmes personnalisables', 3),
(2, 'PixelMaster Studio', '2.0.0', 79.99, 'Suite complète de design graphique et retouche photo.', 'Calques et masques|Filtres IA|Export multi-format|Bibliothèque d''assets', 3),
(3, 'SecureVault', '1.5.0', 29.99, 'Gestionnaire de mots de passe et coffre-fort numérique.', 'Chiffrement AES-256|Générateur de mots de passe|Stockage sécurisé|Authentification 2FA', 3),
(4, 'DataFlow Analytics', '4.1.0', 99.99, 'Plateforme d''analyse de données et visualisation interactive.', 'Tableaux de bord dynamiques|Rapports automatisés|Connecteurs API|Machine Learning intégré', 5);
