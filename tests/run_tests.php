<?php
// tests/run_tests.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/DbSessionsHandler.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Dossier.php';
require_once __DIR__ . '/../models/Action.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/PartageDossier.php';
require_once __DIR__ . '/../models/Fichier.php';
require_once __DIR__ . '/../models/Workflow.php';

// Helpers de terminal
function info(string $msg) { echo "\033[34m[INFO]\033[0m $msg\n"; }
function success(string $msg) { echo "\033[32m[PASS]\033[0m $msg\n"; }
function fail(string $msg) { echo "\033[31m[FAIL]\033[0m $msg\n"; exit(1); }

$testsRun = 0;
$testsPassed = 0;

function assertTest(bool $condition, string $message) {
    global $testsRun, $testsPassed;
    $testsRun++;
    if ($condition) {
        $testsPassed++;
        success($message);
    } else {
        fail("Assertion échouée : $message");
    }
}

try {
    info("Initialisation de la connexion et début de la transaction...");
    $pdo = getPDO();
    $pdo->beginTransaction();

    // ==========================================
    // 1. TEST CONNEXION BDD
    // ==========================================
    info("--- Test 1 : Connexion BDD ---");
    assertTest($pdo instanceof PDO, "La connexion à la base de données est fonctionnelle.");

    // Initialisation des instances
    $userModel = new User();
    $dossierModel = new Dossier();
    $actionModel = new Action();
    $commentaireModel = new Commentaire();
    $logModel = new ActivityLog();
    $partageModel = new PartageDossier();
    $fichierModel = new Fichier();
    $workflowModel = new Workflow();
    $sessionHandler = new DbSessionsHandler($pdo);

    // Données temporaires pour les tests
    $emailResponsable = 'test.responsable@gov.mg';
    $emailCollaborateur = 'test.collaborateur@gov.mg';
    $emailAdministrateur = 'test.admin@gov.mg';
    $emailTiers = 'test.tiers@gov.mg';

    // Nettoyage préventif
    $pdo->exec("DELETE FROM users WHERE email IN ('$emailResponsable', '$emailCollaborateur', '$emailAdministrateur', '$emailTiers')");

    // ==========================================
    // 2. TESTS - MODELE USER
    // ==========================================
    info("--- Test 2 : Modèle User ---");
    
    // Création des utilisateurs
    $idResponsable = $userModel->create('Responsable', 'Jean', $emailResponsable, 'password123', 2);
    $idCollaborateur = $userModel->create('Collaborateur', 'Marie', $emailCollaborateur, 'password123', 1);
    $idAdmin = $userModel->create('Admin', 'Ismaël', $emailAdministrateur, 'password123', 3);
    $idTiers = $userModel->create('Tiers', 'Marc', $emailTiers, 'password123', 1);

    assertTest($idResponsable > 0, "Création d'un utilisateur Responsable de dossier (ID: $idResponsable).");
    assertTest($idCollaborateur > 0, "Création d'un utilisateur Collaborateur (ID: $idCollaborateur).");
    assertTest($idAdmin > 0, "Création d'un utilisateur Administrateur (ID: $idAdmin).");
    assertTest($idTiers > 0, "Création d'un utilisateur Tiers (ID: $idTiers).");

    // Authentification
    $authSuccess = $userModel->authenticate($emailResponsable, 'password123');
    assertTest(is_array($authSuccess) && (int)$authSuccess['id_user'] === $idResponsable, "Authentification réussie avec mot de passe correct.");

    $authFail = $userModel->authenticate($emailResponsable, 'mauvaispass');
    assertTest($authFail === false, "Authentification correctement rejetée pour mot de passe invalide.");

    $authNonExistent = $userModel->authenticate('inconnu@gov.mg', 'password123');
    assertTest($authNonExistent === false, "Authentification rejetée pour un email non inscrit.");

    // Unicité de l'email
    $duplicate = $userModel->create('Doublon', 'Test', $emailResponsable, 'password123', 1);
    assertTest($duplicate === false, "Le système empêche la création d'un doublon d'adresse email.");

    // Recherche d'utilisateur
    $userByEmail = $userModel->findByEmail($emailResponsable);
    assertTest($userByEmail !== null && (int)$userByEmail['id_user'] === $idResponsable, "Recherche d'utilisateur par email réussie.");

    $userById = $userModel->findById($idCollaborateur);
    assertTest($userById !== null && $userById['email'] === $emailCollaborateur, "Recherche d'utilisateur par ID réussie.");

    // Récupération des rôles
    $roles = $userModel->getRoles();
    assertTest(count($roles) >= 3, "Récupération des rôles système réussie.");

    // Liste de tous les utilisateurs
    $allUsers = $userModel->getAllUsers();
    assertTest(count($allUsers) >= 4, "Récupération de la liste complète des utilisateurs.");

    // Mise à jour de rôle
    $updateRoleOk = $userModel->updateRole($idTiers, 2); // Devient Responsable
    $updatedTiers = $userModel->findById($idTiers);
    assertTest($updateRoleOk && (int)$updatedTiers['id_role'] === 2, "Mise à jour du rôle utilisateur réussie.");

    // Statistiques globales
    $stats = $userModel->getSystemStats();
    assertTest(isset($stats['users'], $stats['dossiers'], $stats['fichiers'], $stats['actions']), "Calcul des statistiques du système réussi.");

    // Suppression d'utilisateur
    $deleteUserOk = $userModel->deleteUser($idTiers);
    $deletedTiers = $userModel->findById($idTiers);
    assertTest($deleteUserOk && $deletedTiers === null, "Suppression d'utilisateur réussie.");

    // ==========================================
    // 3. TESTS - MODELE DOSSIER & WORKFLOW
    // ==========================================
    info("--- Test 3 : Modèle Dossier ---");

    $dossierId = $dossierModel->create([
        'nom' => 'Dossier Diplomatique Alpha',
        'description' => 'Description test',
        'date_debut' => '2026-08-01',
        'date_fin' => '2026-08-10',
        'date_limite' => '2026-08-15',
        'id_workflow' => 1, // Brouillon
        'cree_par' => $idResponsable
    ]);
    assertTest($dossierId > 0, "Création de dossier réussie (ID: $dossierId).");

    // Recherche de dossier par ID
    $dossier = $dossierModel->findById($dossierId);
    assertTest($dossier !== null && $dossier['nom'] === 'Dossier Diplomatique Alpha', "Recherche de dossier par ID réussie.");

    // Ajout et récupération de membre
    $addMemberOk = $dossierModel->addMember($dossierId, $idCollaborateur, 'Rédacteur');
    assertTest($addMemberOk, "Ajout d'un membre collaborateur au dossier.");
    
    $members = $dossierModel->getMembers($dossierId);
    $hasCollaborateur = false;
    foreach ($members as $m) {
        if ((int)$m['id_user'] === $idCollaborateur) {
            $hasCollaborateur = true;
            assertTest($m['role_dans_dossier'] === 'Rédacteur', "Vérification du rôle spécifique du membre dans le dossier.");
        }
    }
    assertTest($hasCollaborateur, "Récupération de la liste des membres du dossier réussie.");

    // Droits d'accès et règles de sécurité (Anti-IDOR)
    $accessCreator = $dossierModel->hasAccess($dossierId, $idResponsable, 'Responsable de dossier');
    assertTest($accessCreator, "Le créateur a bien accès au dossier.");

    $accessCollaborateur = $dossierModel->hasAccess($dossierId, $idCollaborateur, 'Collaborateur');
    assertTest($accessCollaborateur, "Le collaborateur membre a bien accès au dossier.");

    $accessAdmin = $dossierModel->hasAccess($dossierId, $idAdmin, 'Administrateur');
    assertTest($accessAdmin, "L'administrateur a bien accès à tous les dossiers.");

    // Tiers non invité (IDOR bloqué)
    $idNonInvite = $userModel->create('NonInvite', 'Paul', 'non.invite@gov.mg', 'password123', 1);
    $accessNonInvite = $dossierModel->hasAccess($dossierId, $idNonInvite, 'Collaborateur');
    assertTest($accessNonInvite === false, "L'accès est bloqué pour un tiers non invité.");

    // Récupération des dossiers par utilisateur (Visibilité)
    $dossiersResponsable = $dossierModel->getAllForUser($idResponsable, 'Responsable de dossier');
    assertTest(count($dossiersResponsable) >= 1, "Le responsable voit ses dossiers.");

    $dossiersCollaborateur = $dossierModel->getAllForUser($idCollaborateur, 'Collaborateur');
    assertTest(count($dossiersCollaborateur) >= 1, "Le collaborateur voit les dossiers où il participe.");

    $dossiersAdmin = $dossierModel->getAllForUser($idAdmin, 'Administrateur');
    assertTest(count($dossiersAdmin) >= 1, "L'administrateur voit tous les dossiers.");

    // Statuts de dossiers
    $statutsDossier = $dossierModel->getStatuts();
    assertTest(count($statutsDossier) >= 4, "Récupération des statuts de dossier réussie.");

    // Mise à jour de dossier
    $updateDossierOk = $dossierModel->update($dossierId, [
        'nom' => 'Dossier Alpha Modifié',
        'description' => 'Nouvelle description',
        'date_debut' => '2026-08-02',
        'date_fin' => '2026-08-12',
        'date_limite' => '2026-08-20',
        'id_statut' => 2, // En cours
        'id_workflow' => 2 // En révision
    ], $idResponsable, false);

    $updatedDossier = $dossierModel->findById($dossierId);
    assertTest($updateDossierOk && $updatedDossier['nom'] === 'Dossier Alpha Modifié' && (int)$updatedDossier['id_statut'] === 2 && (int)$updatedDossier['id_workflow'] === 2, "Modification de dossier réussie par le responsable.");

    // Test auto-complete when workflow is Signé (3)
    $updateDossierSignOk = $dossierModel->update($dossierId, [
        'nom' => 'Dossier Alpha Modifié',
        'description' => 'Nouvelle description',
        'date_debut' => '2026-08-02',
        'date_fin' => '2026-08-12',
        'date_limite' => '2026-08-20',
        'id_statut' => 2, // input En cours
        'id_workflow' => 3 // input Signé
    ], $idResponsable, false);
    $signedDossier = $dossierModel->findById($dossierId);
    assertTest($updateDossierSignOk && (int)$signedDossier['id_statut'] === 3 && (int)$signedDossier['id_workflow'] === 3, "Un dossier signé est automatiquement marqué comme Terminé.");

    // Restaurer le workflow à 2 pour le reste des tests
    $dossierModel->update($dossierId, [
        'nom' => 'Dossier Alpha Modifié',
        'description' => 'Nouvelle description',
        'date_debut' => '2026-08-02',
        'date_fin' => '2026-08-12',
        'date_limite' => '2026-08-20',
        'id_statut' => 2,
        'id_workflow' => 2
    ], $idResponsable, true);

    // Synchronisation des collaborateurs
    $dossierModel->syncMembers($dossierId, [$idCollaborateur, $idAdmin], $idResponsable);
    $membersAfterSync = $dossierModel->getMembers($dossierId);
    $memberIdsAfterSync = array_map(fn($m) => (int)$m['id_user'], $membersAfterSync);
    assertTest(
        in_array($idCollaborateur, $memberIdsAfterSync) &&
        in_array($idAdmin, $memberIdsAfterSync) &&
        in_array($idResponsable, $memberIdsAfterSync) &&
        count($membersAfterSync) === 3,
        "Synchronisation des collaborateurs (ajout et maintien du responsable) réussie."
    );

    $dossierModel->syncMembers($dossierId, [$idCollaborateur], $idResponsable);
    $membersAfterSync2 = $dossierModel->getMembers($dossierId);
    $memberIdsAfterSync2 = array_map(fn($m) => (int)$m['id_user'], $membersAfterSync2);
    assertTest(
        in_array($idCollaborateur, $memberIdsAfterSync2) &&
        !in_array($idAdmin, $memberIdsAfterSync2) &&
        in_array($idResponsable, $memberIdsAfterSync2) &&
        count($membersAfterSync2) === 2,
        "Mise à jour des collaborateurs (suppression) réussie."
    );

    // Workflow d'approbation
    $workflows = $workflowModel->getAllStatuts();
    assertTest(count($workflows) >= 3, "Récupération de tous les statuts de workflow réussie.");
    
    $workflowsAlias = $workflowModel->getAll();
    assertTest(count($workflowsAlias) === count($workflows), "Appel de la méthode d'alias Workflow::getAll() réussi.");

    $workflowBrouillon = $workflowModel->getStatutById(1);
    assertTest($workflowBrouillon !== null && $workflowBrouillon['libelle'] === 'Brouillon', "Récupération d'un statut de workflow par ID réussie.");

    // ==========================================
    // 4. TESTS - PARTAGE DE DOSSIER
    // ==========================================
    info("--- Test 4 : Partage de Dossier ---");

    $addPartageOk = $partageModel->addPartage($dossierId, $idNonInvite, 'Lecture');
    assertTest($addPartageOk, "Partage de dossier avec un tiers réussi.");

    $partagesDossier = $partageModel->getPartagesByDossier($dossierId);
    assertTest(count($partagesDossier) >= 1 && $partagesDossier[0]['email'] === 'non.invite@gov.mg', "Récupération des partages par dossier réussie.");

    $partagesUser = $partageModel->getPartagesByUser($idNonInvite);
    assertTest(count($partagesUser) >= 1, "Récupération des partages par utilisateur réussie.");

    // Vérification de l'accès via le partage
    $accessPartage = $dossierModel->hasAccess($dossierId, $idNonInvite, 'Collaborateur');
    assertTest($accessPartage, "L'utilisateur externe a maintenant accès suite au partage.");

    $removePartageOk = $partageModel->removePartage($dossierId, $idNonInvite);
    $accessApresRetrait = $dossierModel->hasAccess($dossierId, $idNonInvite, 'Collaborateur');
    assertTest($removePartageOk && $accessApresRetrait === false, "Retrait du partage et révocation d'accès réussis.");

    // ==========================================
    // 5. TESTS - ACTIONS (TACHES)
    // ==========================================
    info("--- Test 5 : Actions du dossier ---");

    $actionId = $actionModel->create([
        'nom' => 'Rédiger accord de partenariat',
        'description' => 'Tâche de rédaction principale',
        'date_debut' => '2026-08-03',
        'date_fin' => '2026-08-08',
        'date_limite' => '2026-08-09',
        'id_statut' => 1,
        'id_priorite' => 3, // Haute
        'id_dossier' => $dossierId
    ]);
    assertTest($actionId > 0, "Création d'une action réussie (ID: $actionId).");

    // Liste des actions du dossier
    $actions = $actionModel->getByDossier($dossierId);
    assertTest(count($actions) >= 1 && $actions[0]['nom'] === 'Rédiger accord de partenariat', "Récupération des actions par dossier réussie.");

    // Assignation d'une action
    $assignOk = $actionModel->assign($actionId, $idCollaborateur);
    assertTest($assignOk, "Assignation d'une action à un collaborateur réussie.");

    // Mise à jour de statut d'action
    $updateActionStatutOk = $actionModel->updateStatut($actionId, 2); // En cours
    $actionsApresModif = $actionModel->getByDossier($dossierId);
    assertTest($updateActionStatutOk && (int)$actionsApresModif[0]['id_statut'] === 2, "Mise à jour du statut d'une action réussie.");

    // Mise à jour complète de l'action
    $updateFullOk = $actionModel->updateFull($actionId, [
        'nom' => 'Rédiger accord de partenariat - Modifié',
        'description' => 'Description modifiée',
        'date_debut' => '2026-08-04',
        'date_fin' => '2026-08-09',
        'date_limite' => '2026-08-10',
        'id_statut' => 3, // Terminé
        'id_priorite' => 4, // Critique
        'assign_to' => $idAdmin
    ]);
    $actionsApresUpdateFull = $actionModel->getByDossier($dossierId);
    $actUpdated = $actionsApresUpdateFull[0];
    assertTest(
        $updateFullOk && 
        $actUpdated['nom'] === 'Rédiger accord de partenariat - Modifié' && 
        $actUpdated['description'] === 'Description modifiée' && 
        (int)$actUpdated['id_statut'] === 3 && 
        (int)$actUpdated['id_priorite'] === 4 && 
        (int)$actUpdated['id_user'] === $idAdmin,
        "Mise à jour complète d'une action (changement des attributs et de l'assignataire) réussie."
    );

    // Suppression d'une action
    $tempActionId = $actionModel->create([
        'nom' => 'Action temporaire',
        'id_dossier' => $dossierId
    ]);
    $deleteActionOk = $actionModel->delete($tempActionId);
    $actionsApresDelete = $actionModel->getByDossier($dossierId);
    $hasTempAction = false;
    foreach ($actionsApresDelete as $act) {
        if ((int)$act['id_action'] === $tempActionId) {
            $hasTempAction = true;
        }
    }
    assertTest($deleteActionOk && !$hasTempAction, "Suppression d'une action réussie.");

    // Priorités et statuts d'action
    $priorites = $actionModel->getPriorites();
    assertTest(count($priorites) >= 4, "Récupération des priorités d'action réussie.");

    $statutsAction = $actionModel->getStatuts();
    assertTest(count($statutsAction) >= 4, "Récupération des statuts d'action réussie.");

    // ==========================================
    // 6. TESTS - COMMENTAIRES
    // ==========================================
    info("--- Test 6 : Commentaires ---");

    $commentId = $commentaireModel->add($dossierId, $idCollaborateur, "Note importante : premier draft soumis.");
    assertTest($commentId > 0, "Ajout d'un commentaire sur un dossier réussi (ID: $commentId).");

    $commentaires = $commentaireModel->getByDossier($dossierId);
    assertTest(count($commentaires) >= 1 && $commentaires[0]['contenu'] === "Note importante : premier draft soumis.", "Récupération des commentaires par dossier réussie.");

    // ==========================================
    // 7. TESTS - JOURNAL D'ACTIVITE (AUDIT LOG)
    // ==========================================
    info("--- Test 7 : Journal d'activité ---");

    $logModel->log($dossierId, $idResponsable, "A soumis le dossier pour relecture.");
    
    $logsDossier = $logModel->getByDossier($dossierId);
    assertTest(count($logsDossier) >= 1 && $logsDossier[0]['action'] === "A soumis le dossier pour relecture.", "Journalisation d'une action par dossier réussie.");

    $allLogs = $logModel->getAllLogs(10);
    assertTest(count($allLogs) >= 1, "Récupération de l'audit log global réussie.");

    // ==========================================
    // 8. TESTS - PIECES JOINTES (FICHIERS)
    // ==========================================
    info("--- Test 8 : Fichiers / Pièces Jointes ---");

    $fileId = $fichierModel->uploadFichier([
        'nom' => 'accord_bilateral.pdf',
        'chemin' => '/uploads/123456789_accord_bilateral.pdf',
        'taille' => 102400
    ], $idResponsable);
    assertTest($fileId > 0, "Création d'un enregistrement de fichier réussi (ID: $fileId).");

    $fetchedFile = $fichierModel->findById($fileId);
    assertTest($fetchedFile !== null && (int)$fetchedFile['ajoute_par'] === $idResponsable, "Recherche de pièce jointe par ID réussie.");

    // Liaison au dossier
    $linkDossierOk = $fichierModel->linkToDossier($fileId, $dossierId);
    assertTest($linkDossierOk, "Liaison du fichier au dossier réussie.");

    // Liaison à l'action
    $linkActionOk = $fichierModel->linkToAction($fileId, $actionId);
    assertTest($linkActionOk, "Liaison du fichier à l'action réussie.");

    // Récupération des fichiers par dossier
    $fichiersDossier = $fichierModel->getFichiersByDossier($dossierId);
    assertTest(count($fichiersDossier) >= 1 && $fichiersDossier[0]['nom'] === 'accord_bilateral.pdf', "Récupération des fichiers du dossier réussie.");

    // Suppression de fichier
    $deleteFileOk = $fichierModel->delete($fileId);
    $fichiersDossierApresSuppr = $fichierModel->getFichiersByDossier($dossierId);
    assertTest($deleteFileOk && count($fichiersDossierApresSuppr) === 0, "Suppression de fichier réussie.");

    // ==========================================
    // 9. TESTS - GESTIONNAIRE DE SESSIONS (DbSessionsHandler)
    // ==========================================
    info("--- Test 9 : Gestionnaire de sessions ---");

    $sessionId = "test_session_xyz123";
    $sessionData = "user|a:5:{s:2:\"id\";i:1;s:3:\"nom\";s:4:\"Test\";s:6:\"prenom\";s:4:\"User\";s:5:\"email\";s:15:\"test@gov.mg\";s:4:\"role\";s:13:\"Collaborateur\";}";

    // Simulation de session active
    $_SESSION['user'] = ['id' => $idCollaborateur];

    $sessionOpenOk = $sessionHandler->open("", "PHPSESSID");
    assertTest($sessionOpenOk, "Méthode open() du session handler réussie.");

    $sessionWriteOk = $sessionHandler->write($sessionId, $sessionData);
    assertTest($sessionWriteOk, "Méthode write() de session en base de données réussie.");

    $sessionReadData = $sessionHandler->read($sessionId);
    assertTest($sessionReadData === $sessionData, "Méthode read() de session depuis la base de données réussie.");

    // Nettoyage / Gc
    $gcOk = $sessionHandler->gc(0); // Supprime toutes les sessions inactives
    assertTest($gcOk !== false, "Méthode gc() (garbage collector) de sessions réussie.");

    $sessionDestroyOk = $sessionHandler->destroy($sessionId);
    $readApresDestroy = $sessionHandler->read($sessionId);
    assertTest($sessionDestroyOk && $readApresDestroy === '', "Méthode destroy() de session réussie.");

    $sessionOpenOk = $sessionHandler->open("", "PHPSESSID");
    $sessionWriteOk = $sessionHandler->write($sessionId, $sessionData);
    $sessionCloseOk = $sessionHandler->close();
    assertTest($sessionCloseOk, "Méthode close() du session handler réussie.");

    // ==========================================
    // 10. NETTOYAGE & ROLLBACK
    // ==========================================
    info("Restauration de la base de données (Rollback)...");
    $pdo->rollBack();
    success("Restauration de la BDD réussie (Rollback). Aucun effet de bord permanent.");

    echo "\n\033[32;1m🎉 TOUS LES TESTS ONT RÉUSSI ($testsPassed/$testsRun assertions passées) !\033[0m\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fail("Exception interceptée pendant l'exécution des tests : " . $e->getMessage());
}
