<?php
$pageTitle = 'Tache - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { flashSet('error','Tache introuvable.'); redirect('projets/index.php'); }

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT t.*, p.nom AS projet_nom, p.id_chef,
               s.libelle AS statut_lib, pr.libelle AS priorite_lib,
               u.nom AS createur_nom
        FROM taches t
        JOIN projets p   ON t.id_projet=p.id_projet
        JOIN statuts s   ON t.id_statut=s.id_statut
        JOIN priorites pr ON t.id_priorite=pr.id_priorite
        JOIN users u      ON t.id_createur=u.id_user
        WHERE t.id_tache=?
    ");
    $stmt->execute([$id]);
    $tache = $stmt->fetch();
    if (!$tache) { flashSet('error','Tache introuvable.'); redirect('projets/index.php'); }
    if (!canAccessProjet($tache['id_projet'], $user['id'])) { flashSet('error','Acces refuse.'); redirect('projets/index.php'); }

    $pageTitle = htmlspecialchars($tache['nom']) . ' - Tantana';

    // Assignes
    $stmt = $pdo->prepare("SELECT u.id_user,u.nom FROM affectations a JOIN users u ON a.id_user=u.id_user WHERE a.id_tache=?");
    $stmt->execute([$id]);
    $assignes = $stmt->fetchAll();

    // Commentaires
    $stmt = $pdo->prepare("SELECT c.*,u.nom AS auteur FROM commentaires c JOIN users u ON c.id_user=u.id_user WHERE c.id_tache=? ORDER BY c.date_publication ASC");
    $stmt->execute([$id]);
    $commentaires = $stmt->fetchAll();

    // Fichiers
    $stmt = $pdo->prepare("SELECT f.*,u.nom AS ajouteur FROM fichiers f JOIN users u ON f.id_user=u.id_user WHERE f.id_tache=? ORDER BY f.date_ajout DESC");
    $stmt->execute([$id]);
    $fichiers = $stmt->fetchAll();

    // Sous-taches
    $stmt = $pdo->prepare("SELECT t.*,s.libelle AS statut_lib FROM taches t JOIN statuts s ON t.id_statut=s.id_statut WHERE t.id_parent=? ORDER BY t.date_creation");
    $stmt->execute([$id]);
    $sous_taches = $stmt->fetchAll();

    $statuts = $pdo->query("SELECT * FROM statuts")->fetchAll();
    $dbErr   = null;
} catch (PDOException $e) {
    error_log('[taches/view] ' . $e->getMessage());
    $dbErr = $e->getMessage();
    $tache = $assignes = $commentaires = $fichiers = $sous_taches = $statuts = [];
}

$isChef    = !empty($tache) && $tache['id_chef'] == $user['id'];
$isCreator = !empty($tache) && $tache['id_createur'] == $user['id'];
$canEdit   = $isChef || $isCreator;

// Traitement statut rapide
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_statut'])) {
    try {
        $ns = (int)$_POST['id_statut'];
        $pdo->prepare("UPDATE taches SET id_statut=? WHERE id_tache=?")->execute([$ns, $id]);
        logAction("A change le statut de \"{$tache['nom']}\"", $tache['id_projet'], $id);
        flashSet('success','Statut mis a jour.');
    } catch (PDOException $e) { flashSet('error',$e->getMessage()); }
    redirect('taches/view.php?id='.$id);
}

// Traitement commentaire
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_comment'])) {
    $contenu = trim($_POST['contenu'] ?? '');
    if ($contenu) {
        try {
            $pdo->prepare("INSERT INTO commentaires (contenu,id_tache,id_user) VALUES (?,?,?)")
                ->execute([$contenu, $id, $user['id']]);
            $act = logAction("A commente la tache \"{$tache['nom']}\"", $tache['id_projet'], $id);
            // Notifier assignes
            foreach ($assignes as $a) {
                if ($a['id_user'] != $user['id']) {
                    notifier($a['id_user'], "{$user['nom']} a commente la tache \"{$tache['nom']}\".", $act);
                }
            }
            flashSet('success','Commentaire ajoute.');
        } catch (PDOException $e) { flashSet('error',$e->getMessage()); }
    }
    redirect('taches/view.php?id='.$id);
}

// Traitement upload fichier
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upload_file'])) {
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error']===UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        $origName  = basename($_FILES['fichier']['name']);
        $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed   = ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','gif','zip','txt','csv'];
        if (!in_array($ext, $allowed)) {
            flashSet('error','Type de fichier non autorise.');
        } elseif ($_FILES['fichier']['size'] > 10*1024*1024) {
            flashSet('error','Fichier trop grand (10Mo max).');
        } else {
            $newName = uniqid('f_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/','_',$origName);
            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadDir.$newName)) {
                try {
                    $pdo->prepare("INSERT INTO fichiers (nom,chemin,taille,id_tache,id_user) VALUES (?,?,?,?,?)")
                        ->execute([$origName, 'uploads/'.$newName, $_FILES['fichier']['size'], $id, $user['id']]);
                    logAction("A ajoute un fichier a \"{$tache['nom']}\"", $tache['id_projet'], $id);
                    flashSet('success','Fichier uploade.');
                } catch (PDOException $e) { flashSet('error',$e->getMessage()); }
            } else {
                flashSet('error','Erreur lors de l\'upload. Verifiez les permissions du dossier uploads/.');
            }
        }
    } else {
        flashSet('error','Erreur upload : code '.$_FILES['fichier']['error']);
    }
    redirect('taches/view.php?id='.$id);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <!-- Breadcrumb + titre -->
    <div style="margin-bottom:24px;">
      <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:8px;">
        <a href="../projets/index.php" style="color:var(--text-muted);">Projets</a>
        &rsaquo; <a href="../projets/view.php?id=<?= $tache['id_projet'] ?>" style="color:var(--text-muted);"><?= htmlspecialchars($tache['projet_nom']) ?></a>
        &rsaquo; <?= htmlspecialchars($tache['nom']) ?>
      </div>
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
          <h1 style="font-family:var(--font-head);font-size:1.65rem;font-weight:600;margin-bottom:8px;"><?= htmlspecialchars($tache['nom']) ?></h1>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge <?= classBadgeStatut($tache['statut_lib']) ?>"><?= labelStatut($tache['statut_lib']) ?></span>
            <span class="badge <?= classBadgePriorite($tache['priorite_lib']) ?>"><?= labelPriorite($tache['priorite_lib']) ?></span>
          </div>
        </div>
        <?php if ($canEdit): ?>
          <div style="display:flex;gap:8px;">
            <a href="edit.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Modifier</a>
            <a href="delete.php?id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette tache ?')">Supprimer</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid-2" style="align-items:start;gap:20px;">
      <!-- COLONNE GAUCHE -->
      <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Infos -->
        <div class="card">
          <div class="card-header"><span class="card-title">Details</span></div>
          <div style="display:flex;flex-direction:column;gap:10px;font-size:.875rem;">
            <?php if ($tache['description']): ?>
              <div><div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Description</div>
              <div style="line-height:1.6;"><?= nl2br(htmlspecialchars($tache['description'])) ?></div></div>
            <?php endif; ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <div><div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Createur</div><?= htmlspecialchars($tache['createur_nom']) ?></div>
              <div><div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Date creation</div><?= fmtDate($tache['date_creation']) ?></div>
              <?php if ($tache['date_debut']): ?>
              <div><div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Debut</div><?= fmtDate($tache['date_debut']) ?></div><?php endif; ?>
              <?php if ($tache['date_fin']): ?>
              <div><div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Fin</div><?= fmtDate($tache['date_fin']) ?></div><?php endif; ?>
              <?php if ($tache['date_limite']): ?>
              <?php $j=daysUntil($tache['date_limite']); ?>
              <div><div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Limite</div>
              <span style="color:<?= $j!==null&&$j<3?'var(--red)':'inherit' ?>;"><?= fmtDate($tache['date_limite']) ?><?= $j!==null?' ('.(($j<0)?'retard '.abs($j).'j':($j===0?'aujourd\'hui':$j.'j')).')':'' ?></span></div>
              <?php endif; ?>
            </div>
            <div>
              <div style="color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Assignes</div>
              <?php if (empty($assignes)): ?><span style="color:var(--text-muted);">Personne</span>
              <?php else: ?>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <?php foreach ($assignes as $a): ?>
                    <div style="display:flex;align-items:center;gap:5px;background:var(--bg2);padding:3px 10px 3px 6px;border-radius:99px;border:1px solid var(--border);">
                      <div class="avatar" style="width:20px;height:20px;font-size:.62rem;"><?= strtoupper(substr($a['nom'],0,1)) ?></div>
                      <span style="font-size:.8rem;"><?= htmlspecialchars($a['nom']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Changer statut -->
        <div class="card">
          <div class="card-header"><span class="card-title">Changer le statut</span></div>
          <form method="POST" style="display:flex;gap:8px;align-items:center;">
            <select name="id_statut" class="form-control" style="flex:1;">
              <?php foreach ($statuts as $s): ?>
                <option value="<?= $s['id_statut'] ?>" <?= $tache['id_statut']==$s['id_statut']?'selected':'' ?>><?= labelStatut($s['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
            <button name="update_statut" type="submit" class="btn btn-primary btn-sm">Appliquer</button>
          </form>
        </div>

        <!-- Sous-taches -->
        <?php if (!empty($sous_taches)): ?>
        <div class="card">
          <div class="card-header">
            <span class="card-title">Sous-taches (<?= count($sous_taches) ?>)</span>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($sous_taches as $st): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--bg2);border-radius:var(--radius-sm);">
                <a href="view.php?id=<?= $st['id_tache'] ?>" style="font-size:.875rem;color:var(--text);"><?= htmlspecialchars($st['nom']) ?></a>
                <span class="badge <?= classBadgeStatut($st['statut_lib']) ?>"><?= labelStatut($st['statut_lib']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Fichiers -->
        <div class="card">
          <div class="card-header"><span class="card-title">Fichiers (<?= count($fichiers) ?>)</span></div>
          <?php if (!empty($fichiers)): ?>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
              <?php foreach ($fichiers as $f): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--bg2);border-radius:var(--radius-sm);border:1px solid var(--border);">
                  <div>
                    <div style="font-size:.875rem;font-weight:500;"><?= htmlspecialchars($f['nom']) ?></div>
                    <div style="font-size:.75rem;color:var(--text-muted);"><?= fmtTaille($f['taille']) ?> &bull; <?= htmlspecialchars($f['ajouteur']) ?> &bull; <?= fmtDate($f['date_ajout']) ?></div>
                  </div>
                  <a href="../<?= htmlspecialchars($f['chemin']) ?>" download class="btn btn-outline btn-sm">Telecharger</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <form method="POST" enctype="multipart/form-data">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <input type="file" name="fichier" class="form-control" style="flex:1;" required>
              <button name="upload_file" type="submit" class="btn btn-primary btn-sm">Envoyer</button>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:5px;">PDF, Word, Excel, images, ZIP — 10Mo max</div>
          </form>
        </div>

      </div>

      <!-- COLONNE DROITE — COMMENTAIRES -->
      <div class="card" style="display:flex;flex-direction:column;gap:0;">
        <div class="card-header"><span class="card-title">Commentaires (<?= count($commentaires) ?>)</span></div>

        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;max-height:480px;overflow-y:auto;">
          <?php if (empty($commentaires)): ?>
            <p style="color:var(--text-muted);font-size:.875rem;">Aucun commentaire.</p>
          <?php else: ?>
            <?php foreach ($commentaires as $c): ?>
              <div style="display:flex;gap:10px;">
                <div class="avatar" style="width:30px;height:30px;font-size:.75rem;flex-shrink:0;"><?= strtoupper(substr($c['auteur'],0,1)) ?></div>
                <div style="flex:1;">
                  <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                    <strong style="font-size:.875rem;"><?= htmlspecialchars($c['auteur']) ?></strong>
                    <span style="font-size:.75rem;color:var(--text-muted);"><?= fmtDatetime($c['date_publication']) ?></span>
                    <?php if ($c['id_user']==$user['id']): ?>
                      <a href="delete_comment.php?id=<?= $c['id_commentaire'] ?>&tache=<?= $id ?>" onclick="return confirm('Supprimer ?')" style="font-size:.75rem;color:var(--red);margin-left:auto;">Suppr.</a>
                    <?php endif; ?>
                  </div>
                  <div style="background:var(--bg2);padding:10px 14px;border-radius:var(--radius-sm);border:1px solid var(--border);font-size:.875rem;line-height:1.55;">
                    <?= nl2br(htmlspecialchars($c['contenu'])) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <form method="POST" style="border-top:1px solid var(--border);padding-top:16px;">
          <div class="form-group">
            <textarea name="contenu" class="form-control" rows="3" placeholder="Ecrire un commentaire..." required style="resize:vertical;"></textarea>
          </div>
          <button name="add_comment" type="submit" class="btn btn-primary btn-sm">Publier</button>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
