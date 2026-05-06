<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
requireRole('chef_projet');
$user = getCurrentUser();

$id = (int)($_GET['id'] ?? 0);
if (!$id || !isChefProjet($id, $user['id'])) { flashSet('error','Acces refuse.'); redirect('projets/index.php'); }

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT nom FROM projets WHERE id_projet=?");
    $stmt->execute([$id]);
    $p    = $stmt->fetch();
    if (!$p) { flashSet('error','Projet introuvable.'); redirect('projets/index.php'); }

    $pdo->prepare("DELETE FROM projets WHERE id_projet=?")->execute([$id]);
    logAction("A supprime le projet \"{$p['nom']}\"");
    flashSet('success', "Projet \"{$p['nom']}\" supprime.");
} catch (PDOException $e) {
    error_log('[projets/delete] ' . $e->getMessage());
    flashSet('error', 'Erreur : ' . $e->getMessage());
}

redirect('projets/index.php');
