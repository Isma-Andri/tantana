<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();
$id   = (int)($_GET['id'] ?? 0);

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT t.*,p.id_chef FROM taches t JOIN projets p ON t.id_projet=p.id_projet WHERE t.id_tache=?");
    $stmt->execute([$id]);
    $t = $stmt->fetch();
    if (!$t || ($t['id_chef']!=$user['id'] && $t['id_createur']!=$user['id'])) {
        flashSet('error','Acces refuse.'); redirect('projets/index.php');
    }
    $pdo->prepare("DELETE FROM taches WHERE id_tache=?")->execute([$id]);
    logAction("A supprime la tache \"{$t['nom']}\"", $t['id_projet']);
    flashSet('success','Tache supprimee.');
    redirect('projets/view.php?id='.$t['id_projet']);
} catch (PDOException $e) {
    flashSet('error',$e->getMessage()); redirect('projets/index.php');
}
