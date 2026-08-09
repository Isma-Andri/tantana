-- migrate_validation.sql
-- Ajoute le système de demandes de validation au schéma tantana_new

USE tantana_new;

CREATE TABLE IF NOT EXISTS demande_validation (
    id_demande    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_dossier    INT UNSIGNED NOT NULL,
    id_demandeur  INT UNSIGNED NOT NULL,
    type_demande  ENUM('upload_fichier', 'modif_action', 'changement_workflow') NOT NULL,
    statut        ENUM('en_attente', 'approuvee', 'rejetee') NOT NULL DEFAULT 'en_attente',
    -- Données sérialisées de la demande (JSON)
    payload       JSON NOT NULL,
    -- Métadonnées
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at   TIMESTAMP NULL,
    id_resolveur  INT UNSIGNED NULL,
    commentaire   TEXT NULL,
    CONSTRAINT fk_dv_dossier    FOREIGN KEY (id_dossier)   REFERENCES dossiers(id_dossier) ON DELETE CASCADE,
    CONSTRAINT fk_dv_demandeur  FOREIGN KEY (id_demandeur) REFERENCES users(id_user)       ON DELETE CASCADE,
    CONSTRAINT fk_dv_resolveur  FOREIGN KEY (id_resolveur) REFERENCES users(id_user)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
