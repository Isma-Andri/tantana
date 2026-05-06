-- ============================================================
--  TANTANA — Schéma complet de la base de données
-- ============================================================
USE Tantana;

-- Roles (déjà existant, on ne recrée pas)
-- CREATE TABLE IF NOT EXISTS roles ...

-- Statuts de tâche
CREATE TABLE IF NOT EXISTS statuts (
  id_statut INT AUTO_INCREMENT PRIMARY KEY,
  libelle   VARCHAR(50) NOT NULL
);
INSERT IGNORE INTO statuts (id_statut, libelle) VALUES
  (1,'a_faire'),(2,'en_cours'),(3,'termine'),(4,'bloque');

-- Priorités
CREATE TABLE IF NOT EXISTS priorites (
  id_priorite INT AUTO_INCREMENT PRIMARY KEY,
  libelle     VARCHAR(50) NOT NULL
);
INSERT IGNORE INTO priorites (id_priorite, libelle) VALUES
  (1,'basse'),(2,'moyenne'),(3,'haute');

-- Projets
CREATE TABLE IF NOT EXISTS projets (
  id_projet    INT AUTO_INCREMENT PRIMARY KEY,
  nom          VARCHAR(150) NOT NULL,
  description  TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_debut   DATE,
  date_fin     DATE,
  date_limite  DATE,
  id_chef      INT NOT NULL,
  FOREIGN KEY (id_chef) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Participation utilisateur <-> projet
CREATE TABLE IF NOT EXISTS participations (
  id_participation INT AUTO_INCREMENT PRIMARY KEY,
  id_user          INT NOT NULL,
  id_projet        INT NOT NULL,
  date_ajout       DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_part (id_user, id_projet),
  FOREIGN KEY (id_user)   REFERENCES users(id_user)    ON DELETE CASCADE,
  FOREIGN KEY (id_projet) REFERENCES projets(id_projet) ON DELETE CASCADE
);

-- Tâches
CREATE TABLE IF NOT EXISTS taches (
  id_tache      INT AUTO_INCREMENT PRIMARY KEY,
  nom           VARCHAR(200) NOT NULL,
  description   TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_debut    DATE,
  date_fin      DATE,
  date_limite   DATE,
  id_projet     INT NOT NULL,
  id_statut     INT NOT NULL DEFAULT 1,
  id_priorite   INT NOT NULL DEFAULT 2,
  id_parent     INT DEFAULT NULL,
  id_createur   INT NOT NULL,
  FOREIGN KEY (id_projet)   REFERENCES projets(id_projet) ON DELETE CASCADE,
  FOREIGN KEY (id_statut)   REFERENCES statuts(id_statut),
  FOREIGN KEY (id_priorite) REFERENCES priorites(id_priorite),
  FOREIGN KEY (id_parent)   REFERENCES taches(id_tache)   ON DELETE SET NULL,
  FOREIGN KEY (id_createur) REFERENCES users(id_user)
);

-- Dépendances entre tâches
CREATE TABLE IF NOT EXISTS dependances_taches (
  id_tache      INT NOT NULL,
  id_dependance INT NOT NULL,
  PRIMARY KEY (id_tache, id_dependance),
  FOREIGN KEY (id_tache)      REFERENCES taches(id_tache) ON DELETE CASCADE,
  FOREIGN KEY (id_dependance) REFERENCES taches(id_tache) ON DELETE CASCADE
);

-- Affectation utilisateur <-> tâche
CREATE TABLE IF NOT EXISTS affectations (
  id_affectation INT AUTO_INCREMENT PRIMARY KEY,
  id_user        INT NOT NULL,
  id_tache       INT NOT NULL,
  date_affectation DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_aff (id_user, id_tache),
  FOREIGN KEY (id_user)  REFERENCES users(id_user)   ON DELETE CASCADE,
  FOREIGN KEY (id_tache) REFERENCES taches(id_tache) ON DELETE CASCADE
);

-- Commentaires
CREATE TABLE IF NOT EXISTS commentaires (
  id_commentaire   INT AUTO_INCREMENT PRIMARY KEY,
  contenu          TEXT NOT NULL,
  date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
  id_tache         INT NOT NULL,
  id_user          INT NOT NULL,
  FOREIGN KEY (id_tache) REFERENCES taches(id_tache) ON DELETE CASCADE,
  FOREIGN KEY (id_user)  REFERENCES users(id_user)   ON DELETE CASCADE
);

-- Fichiers
CREATE TABLE IF NOT EXISTS fichiers (
  id_fichier  INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(255) NOT NULL,
  chemin      VARCHAR(500) NOT NULL,
  taille      INT NOT NULL DEFAULT 0,
  date_ajout  DATETIME DEFAULT CURRENT_TIMESTAMP,
  id_tache    INT NOT NULL,
  id_user     INT NOT NULL,
  FOREIGN KEY (id_tache) REFERENCES taches(id_tache) ON DELETE CASCADE,
  FOREIGN KEY (id_user)  REFERENCES users(id_user)   ON DELETE CASCADE
);

-- Historique / Actions
CREATE TABLE IF NOT EXISTS actions (
  id_action    INT AUTO_INCREMENT PRIMARY KEY,
  description  TEXT NOT NULL,
  date_action  DATETIME DEFAULT CURRENT_TIMESTAMP,
  id_user      INT NOT NULL,
  id_projet    INT DEFAULT NULL,
  id_tache     INT DEFAULT NULL,
  FOREIGN KEY (id_user)   REFERENCES users(id_user)    ON DELETE CASCADE,
  FOREIGN KEY (id_projet) REFERENCES projets(id_projet) ON DELETE SET NULL,
  FOREIGN KEY (id_tache)  REFERENCES taches(id_tache)   ON DELETE SET NULL
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id_notif    INT AUTO_INCREMENT PRIMARY KEY,
  contenu     TEXT NOT NULL,
  est_lue     TINYINT(1) NOT NULL DEFAULT 0,
  date_notif  DATETIME DEFAULT CURRENT_TIMESTAMP,
  id_user     INT NOT NULL,
  id_action   INT DEFAULT NULL,
  FOREIGN KEY (id_user)   REFERENCES users(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_action) REFERENCES actions(id_action) ON DELETE SET NULL
);

-- Répertoire de fichiers uploadés
-- (créer le dossier uploads/ manuellement avec chmod 755)
