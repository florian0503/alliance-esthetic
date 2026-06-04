-- Création de la table contact_message (formulaire de contact du site)
-- À exécuter sur la base alliance_esthetic

CREATE TABLE IF NOT EXISTS contact_message (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL,
    motif VARCHAR(50) NOT NULL,
    message LONGTEXT DEFAULT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'nouveau',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Enregistre aussi la migration dans l'historique Doctrine pour éviter les conflits futurs
INSERT IGNORE INTO doctrine_migration_versions (version, executed_at, execution_time)
VALUES ('DoctrineMigrations\\Version20260418100000', NOW(), 0);
