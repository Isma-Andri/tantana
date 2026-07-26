# Modifications Requises pour le MVP

## 1. Modifications au Niveau de la Base de Données (SQL)
- Renommer conceptuellement "Projets" en "Dossiers de politique".
- Ajouter une table `statut_workflow` (Brouillon, En révision, Signé) et la lier à la table `projets`.
- Créer une table `dossier_fichier` pour lier les pièces jointes directement aux dossiers.
- Créer une table `partage_dossier` pour gérer l'accès granulaire (Lecture, Modification) par utilisateur/partenaire.

## 2. Modifications au Niveau du Backend (PHP)
- Modèles : Mettre à jour `Projet.php` pour gérer le statut du workflow, les fichiers associés et les partages.
- Contrôleurs : Ajouter la logique d'upload de fichiers (PDF, PPT) dans `ProjetController.php`.
- Contrôleurs : Implémenter un moteur de transition d'états pour le workflow d'approbation.
- Utilitaires : Intégrer une librairie de génération de PDF (ex. FPDF ou Dompdf) pour l'export des dossiers complets.
- Sécurité : Renforcer les vérifications d'autorisation en se basant sur les droits définis dans `partage_dossier`.

## 3. Modifications au Niveau de l'Interface Utilisateur (Vues HTML/PHP)
- Remplacer les termes "Projet" par "Dossier" et "Tâche" par "Action" dans toute l'interface.
- Ajouter une section "Pièces jointes" dans la vue de détail d'un dossier avec un formulaire d'upload.
- Intégrer des boutons de workflow (ex. "Soumettre pour révision", "Signer l'accord") selon le rôle et le statut actuel.
- Ajouter un bouton d'action "Exporter en PDF" pour générer un rapport complet du dossier de politique.
