-- ============================================================
-- tantana_new.sql
-- Script de création de la base de données Tantana
-- Basé sur le MCD Merise fourni
-- ============================================================

CREATE DATABASE IF NOT EXISTS tantana_new
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tantana_new;

-- ------------------------------------------------------------
-- Table : roles
-- Référentiel des rôles utilisateurs
-- ------------------------------------------------------------
CREATE TABLE roles (
    id_role   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données initiales des rôles
INSERT INTO roles (libelle) VALUES
    ('Membre'),
    ('Chef de projet');

-- ------------------------------------------------------------
-- Table : users
-- Utilisateurs de l'application
-- ------------------------------------------------------------
CREATE TABLE users (
    id_user    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    prenom     VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,         -- Hash bcrypt
    id_role    INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (id_role)
        REFERENCES roles(id_role) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : statut
-- Statuts possibles d'un projet ou d'une tâche
-- ------------------------------------------------------------
CREATE TABLE statut (
    id_statut  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle    VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO statut (libelle) VALUES
    ('En attente'),
    ('En cours'),
    ('Terminé'),
    ('Annulé');

-- ------------------------------------------------------------
-- Table : projets
-- Projets créés et gérés dans l'application
-- ------------------------------------------------------------
CREATE TABLE projets (
    id_projet    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(255) NOT NULL,
    description  TEXT,
    date_creation DATE NOT NULL DEFAULT (CURRENT_DATE),
    date_debut   DATE,
    date_fin     DATE,
    date_limite  DATE,
    id_statut    INT UNSIGNED NOT NULL DEFAULT 1,
    cree_par     INT UNSIGNED NOT NULL,              -- Chef de projet créateur
    CONSTRAINT fk_projet_statut FOREIGN KEY (id_statut)
        REFERENCES statut(id_statut) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_projet_createur FOREIGN KEY (cree_par)
        REFERENCES users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : participer  (association users ↔ projets)
-- Un user peut participer à 0,N projets
-- Un projet peut avoir 0,N participants
-- ------------------------------------------------------------
CREATE TABLE participer (
    id_user           INT UNSIGNED NOT NULL,
    id_projet         INT UNSIGNED NOT NULL,
    date_participation DATE NOT NULL DEFAULT (CURRENT_DATE),
    role_dans_projet  VARCHAR(100),
    PRIMARY KEY (id_user, id_projet),
    CONSTRAINT fk_part_user   FOREIGN KEY (id_user)   REFERENCES users(id_user)   ON DELETE CASCADE,
    CONSTRAINT fk_part_projet FOREIGN KEY (id_projet) REFERENCES projets(id_projet) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : priorite
-- Niveaux de priorité des tâches
-- ------------------------------------------------------------
CREATE TABLE priorite (
    id_priorite INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle     VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO priorite (libelle) VALUES
    ('Basse'),
    ('Moyenne'),
    ('Haute'),
    ('Critique');

-- ------------------------------------------------------------
-- Table : tache
-- Tâches liées aux projets
-- ------------------------------------------------------------
CREATE TABLE tache (
    id_tache     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(255) NOT NULL,
    description  TEXT,
    date_debut   DATE,
    date_fin     DATE,
    date_creation DATE NOT NULL DEFAULT (CURRENT_DATE),
    date_limite  DATE,
    id_statut    INT UNSIGNED NOT NULL DEFAULT 1,
    id_priorite  INT UNSIGNED NOT NULL DEFAULT 2,
    id_projet    INT UNSIGNED NOT NULL,
    CONSTRAINT fk_tache_statut   FOREIGN KEY (id_statut)  REFERENCES statut(id_statut)   ON UPDATE CASCADE,
    CONSTRAINT fk_tache_priorite FOREIGN KEY (id_priorite) REFERENCES priorite(id_priorite) ON UPDATE CASCADE,
    CONSTRAINT fk_tache_projet   FOREIGN KEY (id_projet)  REFERENCES projets(id_projet)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : affecter  (association users ↔ taches)
-- ------------------------------------------------------------
CREATE TABLE affecter (
    id_user        INT UNSIGNED NOT NULL,
    id_tache       INT UNSIGNED NOT NULL,
    date_affectation DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id_user, id_tache),
    CONSTRAINT fk_aff_user  FOREIGN KEY (id_user)  REFERENCES users(id_user)  ON DELETE CASCADE,
    CONSTRAINT fk_aff_tache FOREIGN KEY (id_tache) REFERENCES tache(id_tache) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : fichier
-- Fichiers attachés aux tâches ou projets
-- ------------------------------------------------------------
CREATE TABLE fichier (
    id_fichier  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(255) NOT NULL,
    chemin      VARCHAR(500) NOT NULL,
    date_ajout  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    taille      INT UNSIGNED COMMENT 'Taille en octets',
    ajoute_par  INT UNSIGNED NOT NULL,
    CONSTRAINT fk_fich_user FOREIGN KEY (ajoute_par) REFERENCES users(id_user) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : contenir  (association fichier ↔ tache)
-- ------------------------------------------------------------
CREATE TABLE contenir (
    id_fichier INT UNSIGNED NOT NULL,
    id_tache   INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_fichier, id_tache),
    CONSTRAINT fk_cont_fich  FOREIGN KEY (id_fichier) REFERENCES fichier(id_fichier) ON DELETE CASCADE,
    CONSTRAINT fk_cont_tache FOREIGN KEY (id_tache)   REFERENCES tache(id_tache)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table : notification
-- ------------------------------------------------------------
CREATE TABLE notification (
    id_notif       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contenue       TEXT NOT NULL,
    est_lue        TINYINT(1) NOT NULL DEFAULT 0,
    date_reception TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user        INT UNSIGNED NOT NULL,
    CONSTRAINT fk_notif_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================
