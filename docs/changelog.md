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
