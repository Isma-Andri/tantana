# Journal des Modifications (Changelog)

Ce document recense en détails toutes les modifications apportées au code source et à l'architecture, jour par jour, pour le développement du MVP.

## Jour 1 : Mise à jour du schéma de base de données
- **Fichier impacté** : `tantana_new.sql`
- **Détails** :
  - Création de la table `statut_workflow` avec les états : "Brouillon", "En révision", "Signé".
  - Ajout de la colonne `id_workflow` (FOREIGN KEY) à la table `projets` (Dossiers) pointant vers `statut_workflow` avec la valeur par défaut `1` (Brouillon).
  - Création de la table `dossier_fichier` pour établir une relation de type "many-to-many" entre les fichiers téléchargés (table `fichier`) et les dossiers de politique (table `projets`). Cela permet d'avoir des pièces jointes directement sur le dossier, indépendamment des actions/tâches.
  - Création de la table `partage_dossier` pour gérer l'accès granulaire aux dossiers de politique (colonnes : `id_projet`, `id_user`, `niveau_acces`, `date_partage`).

## Jour 2 : Refactorisation et création des modèles PHP
- **Fichier impacté** : `models/Projet.php`
  - **Détails** : Modification des requêtes SQL dans `create()`, `getAllForUser()`, `findById()`, et `update()` pour inclure et gérer le champ `id_workflow`. Les méthodes de sélection récupèrent désormais `workflow_libelle` en effectuant une jointure (`JOIN statut_workflow`).
- **Nouveau fichier** : `models/Workflow.php`
  - **Détails** : Classe dédiée pour récupérer les différents statuts d'approbation depuis la base de données.
- **Nouveau fichier** : `models/PartageDossier.php`
  - **Détails** : Modèle implémentant la logique d'accès granulaire : méthodes pour ajouter (`addPartage`), supprimer (`removePartage`) et récupérer (`getPartagesByDossier`, `getPartagesByUser`) des accès sécurisés.
- **Nouveau fichier** : `models/Fichier.php`
  - **Détails** : Modèle gérant l'upload virtuel, la suppression et les relations de fichiers. Inclus les méthodes `linkToDossier()` et `getFichiersByDossier()` pour récupérer directement tous les fichiers PDF/PPT attachés à un dossier spécifique, ainsi que `linkToAction()` pour préserver la rétrocompatibilité avec les tâches.

## Jour 3 : Finition du MVP (Workflow UI, Fichiers, Partage, Export et Nomenclature)
- **Fichier impacté** : `controllers/ProjetController.php`
  - **Détails** : Ajout de la gestion complète de `id_workflow` lors de la mise à jour (`update`). Création des méthodes `upload()` pour gérer l'ajout de pièces jointes réelles dans `/public/uploads`, `share()` pour la logique d'ajout de partenaires de lecture/modification, et `exportPdf()` pour l'impression des dossiers.
- **Fichiers impactés** : `views/projets/edit.php`, `show.php`, `index.php`, `create.php`, `partials/navbar.php`
  - **Détails** : Refonte du vocabulaire (remplacement systématique de "Projet" par "Dossier de Politique"). Dans `show.php`, ajout d'interfaces pour : uploader des fichiers joints, inviter des partenaires (partage granulaire) et exporter en PDF. Dans `edit.php`, ajout du menu déroulant permettant au chef de projet de faire évoluer le statut du workflow (ex: Brouillon -> En révision).
- **Fichier impacté** : `public/index.php`
  - **Détails** : Ajout des routes POST pour `/projets/upload/:id`, `/projets/share/:id`, et route GET `/projets/export/:id`.
- **Nouveau fichier** : `views/projets/pdf.php`
  - **Détails** : Création d'une vue d'impression optimisée pour la génération PDF (via `window.print()` HTML/CSS).
- **Architecture système** : Création du dossier `public/uploads` avec permissions d'écriture pour l'accueil des pièces jointes de l'application.

## Raffinement Final : Lexique, Identité Visuelle et Crédits
- **Base de données & Modèles** :
  - Mise à jour de la table `roles` en base de données : `'Chef de projet'` est renommé en `'Responsable de dossier'` et `'Membre'` en `'Collaborateur'`.
  - Alignement de la nomenclature dans `models/Projet.php` et `controllers/ProjetController.php`.
- **Interface Utilisateur (Vues)** :
  - Remplacement total des mentions "Projets/Membres" restants par "Dossiers de politique / Collaborateurs" dans `register.php`, `login.php`, `navbar.php`, `index.php`, et `show.php`.
  - Intégration globale de la signature de développement dans le footer : **"Développé par Ismaël Andrimalala"**.
- **Design Minimaliste** :
  - Suppression complète des effets de Canvas animés et dégradés colorés (AI slops) sur la page d'accueil.
  - Refonte de la page d'accueil (`home.php`) avec une esthétique épurée (fond blanc chaud `#fcfbf9`, typographie gris ardoise, contours discrets, détails vert forêt `#064e3b`).
  - Génération et intégration d'une image d'illustration officielle : **Sceau Officiel de l'État** (`/public/img/diplomatic_seal.jpg`).
  - Remplacement de tout résidu d'émojis par des glyphes SVG clairs et professionnels.

## Extensions de Fonctionnalités & Typographie

- **Nouveaux Modèles & Tables SQL** :
  - `models/Commentaire.php` & table `commentaire` : Espace de discussions et notes contextuelles par dossier.
  - `models/Action.php` : Gestion des actions/tâches associées à chaque dossier, avec assignation et mise à jour dynamique du statut.
  - `models/ActivityLog.php` & table `activity_log` : Horodatage automatique et traçabilité de toutes les actions (création, commentaire, action, mise à jour).
- **Mise à Jour de la Vue Détail (`show.php`)** :
  - Ajout des blocs UI pour les **Actions du Dossier**, les **Discussions & Notes**, et le **Fil d'Activité (Audit Log)**.
- **Nouvelles Fonts & Fond Mosaïque discrets** :
  - Adoption des polices **Source Serif 4** (titres/display) et **Inter** (corps de texte) via Google Fonts.
  - Ajout d'un motif de fond mosaïque géométrique minimaliste en points discrets CSS (`radial-gradient`), sans dégradés colorés.

## Refonte de la Connexion & Nouvelles Illustrations

- **Redesign de la Connexion (`login.php`)** :
  - Passage d'une disposition divisée à une présentation centrée de haut prestige avec carte à bordure supérieure vert forêt, typographie institutionnelle, et badge d'espace réservé.
  - Intégration de l'illustration vectorielle d'un **Traité diplomatique avec sceau de cire rouge** (`signed_treaty.jpg`).
- **Nouveaux Visuels sur les Autres Pages** :
  - **Tableau de Bord (`index.php`)** : Ajout d'une bannière illustrée **Sommet Diplomatique** (`diplomatic_summit.jpg`).
  - **Création & Édition (`create.php`, `edit.php`)** : Intégration de vignettes illustrées de traités signés dans les en-têtes de formulaires.

## Console d'Administration d'État & Compte Administrateur

- **Compte Admin Dédié** :
  - Création du compte administrateur **Ismaël Andrimalala** (`ismael@gov.mg` / `password123`) avec le nouveau rôle **Administrateur** (id_role = 3).
- **Nouveau Contrôleur & Vue Admin (`AdminController.php` & `views/admin/index.php`)** :
  - **Gestion des Utilisateurs** : Visualisation de tous les comptes enregistrés, changement de rôle en direct (Collaborateur / Responsable de dossier / Administrateur) et suppression de compte.
  - **Tableau de Bord Statistiques** : Métriques globales du système (total d'utilisateurs, de dossiers d'État, de pièces jointes et d'actions).
  - **Audit Log Global** : Supervision de l'historique complet d'activité sur l'ensemble des dossiers de la plateforme.
- **Accès Universel Administrateur** :
  - Mise à jour du modèle `Projet.php` et du contrôleur `ProjetController.php` pour accorder au rôle `Administrateur` le droit de supervision, modification et suppression universelle sur tous les dossiers.
- **Barre de Navigation (`navbar.php`)** :
  - Bouton **"Console Admin"** accessible en haut à droite uniquement pour les utilisateurs ayant le rôle `Administrateur`.
