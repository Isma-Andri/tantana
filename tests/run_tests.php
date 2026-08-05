<?php
// tests/run_tests.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Dossier.php';

// Petit helper pour colorer le terminal
function info(string $msg) { echo "\033[34m[INFO]\033[0m $msg\n"; }
function success(string $msg) { echo "\033[32m[PASS]\033[0m $msg\n"; }
function fail(string $msg) { echo "\033[31m[FAIL]\033[0m $msg\n"; exit(1); }

try {
    info("Initialisation de la connexion et début de la transaction...");
    $pdo = getPDO();
    $pdo->beginTransaction(); // Démarre une transaction pour ne pas polluer la DB

    // 1. Test de la connexion
    if ($pdo instanceof PDO) {
        success("Connexion à la base de données réussie.");
    } else {
        fail("Impossible de récupérer l'instance PDO.");
    }

    $userModel = new User();
    $dossierModel = new Dossier();

    // 2. Test création utilisateur
    info("Test de la création d'utilisateurs...");
    $testEmail1 = 'test.user1@gov.mg';
    $testEmail2 = 'test.user2@gov.mg';

    // Supprimer d'éventuels restes (au cas où une transaction précédente a crashé)
    $pdo->exec("DELETE FROM users WHERE email IN ('$testEmail1', '$testEmail2')");

    $userId1 = $userModel->create('NomTest1', 'PrenomTest1', $testEmail1, 'password123', 2); // Responsable
    $userId2 = $userModel->create('NomTest2', 'PrenomTest2', $testEmail2, 'password123', 1); // Collaborateur

    if ($userId1 && $userId2) {
        success("Création de deux utilisateurs de test réussie (ID: $userId1, ID: $userId2).");
    } else {
        fail("Échec de la création des utilisateurs de test.");
    }

    // 3. Test de l'authentification
    info("Test de l'authentification...");
    $authUser = $userModel->authenticate($testEmail1, 'password123');
    if ($authUser && (int)$authUser['id_user'] === $userId1) {
        success("Authentification réussie avec le bon mot de passe.");
    } else {
        fail("L'authentification a échoué avec des identifiants valides.");
    }

    $badAuth = $userModel->authenticate($testEmail1, 'wrongpassword');
    if ($badAuth === false) {
        success("Authentification correctement rejetée avec un mauvais mot de passe.");
    } else {
        fail("L'authentification a accepté un mauvais mot de passe !");
    }

    // 4. Test d'unicité de l'email
    info("Test de l'unicité de l'email...");
    $duplicateUser = $userModel->create('NomTest3', 'PrenomTest3', $testEmail1, 'password123', 1);
    if ($duplicateUser === false) {
        success("Le système rejette correctement la création d'un e-mail déjà existant.");
    } else {
        fail("Le système a autorisé la création d'un e-mail doublon !");
    }

    // 5. Test de création de dossier
    info("Test de la création de dossier...");
    $dossierId = $dossierModel->create([
        'nom' => 'Dossier de Test Automatisé',
        'description' => 'Description de test',
        'date_debut' => '2026-08-01',
        'date_fin' => '2026-08-10',
        'date_limite' => '2026-08-15',
        'id_workflow' => 1,
        'cree_par' => $userId1
    ]);

    if ($dossierId > 0) {
        success("Création du dossier réussie (ID: $dossierId).");
    } else {
        fail("Échec de la création du dossier.");
    }

    // 6. Test d'accès (Sécurité IDOR / BOLA)
    info("Test des règles de sécurité (Contrôle d'accès / IDOR)...");
    
    // Le créateur (Responsable) doit avoir accès
    $hasAccessCreator = $dossierModel->hasAccess($dossierId, $userId1, 'Responsable de dossier');
    if ($hasAccessCreator) {
        success("Le créateur a bien accès au dossier.");
    } else {
        fail("Le créateur se voit refuser l'accès à son propre dossier !");
    }

    // Un autre utilisateur (Collaborateur) ne doit pas avoir accès
    $hasAccessOther = $dossierModel->hasAccess($dossierId, $userId2, 'Collaborateur');
    if (!$hasAccessOther) {
        success("Accès correctement bloqué pour un tiers non invité (Sécurité IDOR OK).");
    } else {
        fail("Vulnérabilité IDOR détectée : un tiers non invité a accès au dossier !");
    }

    // 7. Test de partage
    info("Test du partage de dossier...");
    
    // Partager le dossier avec l'utilisateur 2
    $pdo->exec("INSERT INTO partage_dossier (id_dossier, id_user, niveau_acces) VALUES ($dossierId, $userId2, 'Lecture')");
    
    // L'utilisateur 2 doit maintenant avoir accès
    $hasAccessShared = $dossierModel->hasAccess($dossierId, $userId2, 'Collaborateur');
    if ($hasAccessShared) {
        success("L'accès est bien autorisé après partage du dossier.");
    } else {
        fail("Le partage n'a pas donné accès au dossier à l'utilisateur cible.");
    }

    // Annuler la transaction (aucun changement persistant en base)
    info("Restauration de la base de données (Rollback)...");
    $pdo->rollBack();
    success("Base de données restaurée. Aucun enregistrement de test persistant.");

    echo "\n\033[32;1m🎉 TOUS LES TESTS ONT RÉUSSI AVEC SUCCÈS !\033[0m\n";

} catch (Exception $e) {
    // En cas d'erreur inattendue, annuler la transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fail("Exception capturée durant les tests : " . $e->getMessage());
}
