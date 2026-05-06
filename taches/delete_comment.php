<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user  = getCurrentUser();
$id    = (int)($_GET['id'] ?? 0);
$tache = (int)($_GET['tache'] ?? 0);

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM commentaires WHERE id_commentaire=?");
    $stmt->execute([$id]);
    $c = $stmt->fetch();
    if (!$c || $c['id_user'] != $user['id']) {
        flashSet('error','Acces refuse.');
    } else {
        $pdo->prepare("DELETE FROM commentaires WHERE id_commentaire=?")->execute([$id]);
        flashSet('success','Commentaire supprime.');
    }
} catch (PDOException $e) {
    flashSet('error', $e->getMessage());
}
redirect('taches/view.php?id=' . $tache);
