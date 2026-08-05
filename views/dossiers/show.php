<?php
// views/dossiers/show.php
$pageTitle = $dossier['nom'];
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';

$isOwner = ((int) $dossier['cree_par'] === (int) $user['id']);
$isAdmin = $user['role'] === 'Administrateur';
$isChef  = $user['role'] === 'Responsable de dossier' || $isAdmin;

$statutColors = [
    'En attente' => 'badge-gray',
    'En cours'   => 'badge-jade',
    'Terminé'    => 'badge-sun',
    'Annulé'     => 'badge-rose',
];

function fmtDate(?string $d): string
{
    return $d ? date('d/m/Y', strtotime($d)) : '—';
}

// Progression temporelle en pourcentage
$progress = 0;
if ($dossier['date_debut'] && $dossier['date_limite']) {
    $total = strtotime($dossier['date_limite']) - strtotime($dossier['date_debut']);
    if ($total > 0) {
        $progress = max(0, min(100, (int) round((time() - strtotime($dossier['date_debut'])) / $total * 100)));
    }
}
?>

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8">
        <a href="/dossiers" class="hover:text-ink transition-colors">Dossiers</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium"><?= e($dossier['nom']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl shadow-card overflow-hidden hover-lift">
                <div class="h-2 bg-[#064e3b]"></div>
                <div class="p-7">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <h1 class="font-display text-2xl font-extrabold text-ink leading-tight"><?= e($dossier['nom']) ?></h1>
                        <span class="badge <?= $statutColors[$dossier['statut_libelle']] ?? 'badge-gray' ?> flex-shrink-0">
                            <?= e($dossier['statut_libelle']) ?>
                        </span>
                    </div>

                    <?php if ($dossier['description']): ?>
                    <p class="text-ink-500 text-sm leading-relaxed mb-6"><?= nl2br(e($dossier['description'])) ?></p>
                    <?php else: ?>
                    <p class="text-ink-200 text-sm italic mb-6">Aucune description.</p>
                    <?php endif; ?>

                    <?php if ($progress > 0): ?>
                    <div class="mb-6">
                        <div class="flex items-center justify-between text-xs text-ink-500 mb-1.5">
                            <span>Progression temporelle</span>
                            <span class="font-semibold"><?= $progress ?>%</span>
                        </div>
                        <div class="h-2 bg-ink-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-jade to-jade/60 rounded-full" style="width:<?= $progress ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <?php
                        $dates = [
                            ['Création',   $dossier['date_creation']],
                            ['Début',      $dossier['date_debut']],
                            ['Fin prévue', $dossier['date_fin']],
                            ['Deadline',   $dossier['date_limite']],
                        ];
                        foreach ($dates as [$label, $val]): ?>
                        <div class="bg-ink-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-ink-500 font-medium"><?= $label ?></p>
                            <p class="text-sm font-bold text-ink mt-1"><?= fmtDate($val) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Actions (Tâches du dossier) -->
            <div class="bg-white rounded-2xl shadow-card p-7 hover-lift">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-display text-lg font-bold text-ink">Actions du Dossier</h2>
                    <span class="badge badge-gray"><?= count($actions) ?></span>
                </div>

                <?php if (empty($actions)): ?>
                    <p class="text-sm text-ink-500 italic mb-4">Aucune action définie pour ce dossier.</p>
                <?php else: ?>
                    <ul class="space-y-3 mb-6">
                        <?php foreach ($actions as $act): ?>
                            <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-ink-50 rounded-xl border border-ink-100">
                                <div>
                                    <p class="font-semibold text-sm text-ink"><?= e($act['nom']) ?></p>
                                    <?php if (!empty($act['description'])): ?>
                                        <p class="text-xs text-ink-500 mt-1"><?= e($act['description']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($act['assigne_nom'])): ?>
                                        <p class="text-xs text-jade font-medium mt-1">Assigné à : <?= e($act['assigne_prenom'] . ' ' . $act['assigne_nom']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <form action="/dossiers/update-action/<?= $dossier['id_dossier'] ?>" method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="id_tache" value="<?= $act['id_tache'] ?>">
                                    <select name="id_statut" onchange="this.form.submit()" class="text-xs t-input py-1 px-2" <?= (int)($dossier['id_workflow'] ?? 0) === 3 ? 'disabled' : '' ?>>
                                        <option value="1" <?= $act['id_statut'] == 1 ? 'selected' : '' ?>>En attente</option>
                                        <option value="2" <?= $act['id_statut'] == 2 ? 'selected' : '' ?>>En cours</option>
                                        <option value="3" <?= $act['id_statut'] == 3 ? 'selected' : '' ?>>Terminé</option>
                                        <option value="4" <?= $act['id_statut'] == 4 ? 'selected' : '' ?>>Annulé</option>
                                    </select>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($isChef && (int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                    <form action="/dossiers/add-action/<?= $dossier['id_dossier'] ?>" method="POST" class="space-y-3 pt-4 border-t border-ink-100">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-500">Ajouter une action</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="nom" class="t-input text-sm" placeholder="Nom de l'action" required>
                            <select name="assign_to" class="t-input text-sm">
                                <option value="">Assigner à...</option>
                                <?php foreach ($membres as $m): ?>
                                    <option value="<?= $m['id_user'] ?>"><?= e($m['prenom'] . ' ' . $m['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary text-xs py-2 px-4">+ Ajouter l'action</button>
                    </form>
                <?php elseif ((int)($dossier['id_workflow'] ?? 0) === 3): ?>
                    <div class="pt-4 border-t border-ink-100">
                        <p class="text-xs text-ink-500 italic">Le dossier est signé. Le plan d'action est gelé.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Espace Commentaires -->
            <div class="bg-white rounded-2xl shadow-card p-7 hover-lift">
                <h2 class="font-display text-lg font-bold text-ink mb-5">Discussions & Notes</h2>

                <?php if ((int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                <form action="/dossiers/comment/<?= $dossier['id_dossier'] ?>" method="POST" class="mb-6 space-y-3">
                    <textarea name="contenu" rows="3" class="t-input text-sm resize-none" placeholder="Ajouter une note ou une observation officielle..." required></textarea>
                    <button type="submit" class="btn-jade text-xs py-2 px-4">Publier le commentaire</button>
                </form>
                <?php else: ?>
                <div class="mb-6 p-4 bg-ink-50 rounded-xl border border-ink-100 text-sm text-ink-500 italic">
                    Le dossier est signé. Les discussions officielles sont archivées et verrouillées.
                </div>
                <?php endif; ?>

                <?php if (empty($commentaires)): ?>
                    <p class="text-sm text-ink-500 italic">Aucun commentaire pour le moment.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($commentaires as $com): ?>
                            <div class="p-4 bg-ink-50 rounded-xl border border-ink-100 text-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-ink"><?= e($com['prenom'] . ' ' . $com['nom']) ?></span>
                                    <span class="text-xs text-ink-400"><?= date('d/m/Y H:i', strtotime($com['date_commentaire'])) ?></span>
                                </div>
                                <p class="text-ink-600 leading-relaxed"><?= nl2br(e($com['contenu'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isOwner && $isChef): ?>
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Actions Administratives</h2>
                <div class="flex flex-wrap gap-3">
                    <?php if ((int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                    <a href="/dossiers/edit/<?= $dossier['id_dossier'] ?>" class="btn-primary text-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Modifier le dossier
                    </a>
                    <?php endif; ?>
                    <form action="/dossiers/delete/<?= $dossier['id_dossier'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer ce dossier définitivement ?')">
                        <button type="submit" class="btn-danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-2xl shadow-card p-5 hover-lift">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Créateur</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-ink flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?= mb_strtoupper(mb_substr($dossier['createur_prenom'], 0, 1) . mb_substr($dossier['createur_nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink"><?= e($dossier['createur_prenom'] . ' ' . $dossier['createur_nom']) ?></p>
                        <p class="text-xs text-jade">Responsable de dossier</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-card p-5 hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-ink uppercase tracking-wider">Collaborateurs</h2>
                    <span class="badge badge-gray"><?= count($membres) ?></span>
                </div>

                <?php if (empty($membres)): ?>
                <p class="text-xs text-ink-500 italic">Aucun collaborateur.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($membres as $m): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-jade-light flex items-center justify-center text-jade text-xs font-bold flex-shrink-0">
                            <?= mb_strtoupper(mb_substr($m['prenom'], 0, 1) . mb_substr($m['nom'], 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink truncate"><?= e($m['prenom'] . ' ' . $m['nom']) ?></p>
                            <p class="text-xs text-ink-500"><?= e($m['role_dans_dossier'] ?? 'Collaborateur') ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Fichiers joints -->
            <div class="bg-white rounded-2xl shadow-card p-5 mt-6 hover-lift">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Pièces jointes</h2>
                
                <?php if (empty($fichiers)): ?>
                    <p class="text-sm text-ink-500 mb-4">Aucun fichier joint.</p>
                <?php else: ?>
                    <ul class="space-y-3 mb-4">
                        <?php foreach ($fichiers as $f): ?>
                            <li class="flex items-center justify-between text-sm p-3 bg-ink-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-jade"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    <a href="<?= e($f['chemin']) ?>" target="_blank" class="font-medium text-ink hover:underline">
                                        <?= e($f['nom']) ?>
                                    </a>
                                </div>
                                <span class="text-xs text-ink-400"><?= e($f['auteur_prenom']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($isChef && (int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                    <form action="/dossiers/upload/<?= $dossier['id_dossier'] ?>" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 mt-2">
                        <input type="file" name="fichier" class="text-sm text-ink-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-jade-50 file:text-jade hover:file:bg-jade-100" required>
                        <button type="submit" class="btn-primary text-xs py-2 px-3">Ajouter</button>
                    </form>
                <?php elseif ((int)($dossier['id_workflow'] ?? 0) === 3): ?>
                    <p class="text-xs text-ink-500 italic mt-2">Dossier archivé. Ajout de pièces jointes désactivé.</p>
                <?php endif; ?>
            </div>

            <!-- Partage Sécurisé -->
            <?php if ($isChef): ?>
            <div class="bg-white rounded-2xl shadow-card p-5 mt-6 hover-lift">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Partage sécurisé</h2>
                
                <?php if (!empty($partages)): ?>
                    <ul class="space-y-3 mb-4">
                        <?php foreach ($partages as $p): ?>
                            <li class="flex items-center justify-between text-sm p-3 border border-ink-100 rounded-lg">
                                <div>
                                    <p class="font-medium text-ink"><?= e($p['prenom'] . ' ' . $p['nom']) ?></p>
                                    <p class="text-xs text-ink-500"><?= e($p['email']) ?></p>
                                </div>
                                <span class="px-2 py-1 bg-ink-100 text-ink-600 rounded text-xs font-semibold"><?= e($p['niveau_acces']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ((int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                <form action="/dossiers/share/<?= $dossier['id_dossier'] ?>" method="POST" class="space-y-3 mt-4 pt-4 border-t border-ink-100">
                    <div>
                        <label class="block text-xs font-medium text-ink-600 mb-1">Email du partenaire</label>
                        <input type="email" name="email" class="t-input text-sm" placeholder="email@gov.mg" required>
                    </div>
                    <div class="flex items-center gap-2">
                        <select name="niveau_acces" class="t-input text-sm flex-1">
                            <option value="Lecture">Lecture</option>
                            <option value="Modification">Modification</option>
                        </select>
                        <button type="submit" class="btn-jade text-sm py-2">Partager</button>
                    </div>
                </form>
                <?php else: ?>
                <p class="text-xs text-ink-500 italic mt-4 pt-4 border-t border-ink-100">Le dossier est signé. Les autorisations d'accès ne peuvent plus être modifiées.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Fil d'activité (Audit Log) -->
            <div class="bg-white rounded-2xl shadow-card p-5 mt-6 hover-lift">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Fil d'Activité</h2>
                <?php if (empty($activityLogs)): ?>
                    <p class="text-xs text-ink-500 italic">Aucune activité enregistrée.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($activityLogs as $log): ?>
                            <li class="text-xs border-l-2 border-jade pl-3 py-1">
                                <p class="font-medium text-ink"><?= e($log['prenom'] . ' ' . $log['nom']) ?></p>
                                <p class="text-ink-500 mt-0.5"><?= e($log['action']) ?></p>
                                <p class="text-ink-400 text-[10px] mt-0.5"><?= date('d/m/Y H:i', strtotime($log['date_action'])) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <a href="/dossiers" class="btn-ghost w-full justify-center mt-6">Retour aux dossiers</a>
        </div>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
