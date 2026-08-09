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

$priorityColors = [
    'Basse'    => 'bg-slate-100 text-slate-700 border border-slate-200',
    'Moyenne'  => 'bg-blue-50 text-blue-700 border border-blue-200',
    'Haute'    => 'bg-amber-50 text-amber-700 border border-amber-200',
    'Critique' => 'bg-rose-50 text-rose-700 border border-rose-200',
];

if (!function_exists('fmtDate')) {
    function fmtDate(?string $d): string
    {
        return $d ? date('d/m/Y', strtotime($d)) : '—';
    }
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

<main class="max-w-[90rem] 2xl:max-w-[96rem] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8">
        <a href="/dossiers" class="hover:text-ink transition-colors">Dossiers</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium"><?= e($dossier['nom']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl shadow-card overflow-hidden hover-lift">
                <div class="h-2 bg-primary"></div>
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

            <!-- Demande de changement de workflow (visible Collaborateur uniquement, dossier non signé) -->
            <?php if (!$isChef && (int)($dossier['id_workflow'] ?? 0) !== 3): ?>
            <div class="bg-white rounded-2xl shadow-card p-5 hover-lift border border-ink-100">
                <h2 class="text-sm font-semibold text-ink mb-3 uppercase tracking-wider">Demander un avancement</h2>
                <?php
                    $workflows = (new Workflow())->getAllStatuts();
                    $wfActuel  = (int)($dossier['id_workflow'] ?? 1);
                ?>
                <div class="space-y-3">
                    <p class="text-xs text-ink-500">
                        Workflow actuel :
                        <strong class="text-ink"><?= e($dossier['workflow_libelle'] ?? '—') ?></strong>
                    </p>
                    <p class="text-xs text-ink-400 italic">Soumettez une demande pour que le responsable fasse avancer le dossier.</p>
                    <button type="button"
                            onclick="openWorkflowRequestModal()"
                            class="btn-primary text-xs py-2 px-4 w-full justify-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><polyline points="9 18 15 12 9 6"/></svg>
                        Demander un changement de workflow
                    </button>
                </div>
            </div>
            <?php endif; ?>

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
                            <li class="flex flex-col gap-3 p-4 bg-ink-50 rounded-xl border border-ink-100">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1.5 flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-bold text-sm text-ink truncate"><?= e($act['nom']) ?></p>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $priorityColors[$act['priorite_libelle']] ?? 'bg-slate-100 text-slate-700' ?>">
                                                <?= e($act['priorite_libelle']) ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($act['description'])): ?>
                                            <p class="text-xs text-ink-500 leading-relaxed"><?= nl2br(e($act['description'])) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($act['assigne_nom'])): ?>
                                            <p class="text-xs text-jade font-semibold">Assigné à : <?= e($act['assigne_prenom'] . ' ' . $act['assigne_nom']) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($act['date_debut'] || $act['date_fin'] || $act['date_limite']): ?>
                                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-ink-500 pt-1">
                                                <?php if ($act['date_debut']): ?>
                                                    <span>Début : <strong><?= fmtDate($act['date_debut']) ?></strong></span>
                                                <?php endif; ?>
                                                <?php if ($act['date_fin']): ?>
                                                    <span>Fin : <strong><?= fmtDate($act['date_fin']) ?></strong></span>
                                                <?php endif; ?>
                                                <?php if ($act['date_limite']): ?>
                                                    <span class="text-rose">Limite : <strong><?= fmtDate($act['date_limite']) ?></strong></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <?php if ((int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                                            <button type="button" 
                                                    onclick='openEditActionModal(<?= json_encode($act, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                                    class="p-1.5 text-ink-500 hover:text-jade hover:bg-jade-light rounded-lg transition-colors"
                                                    title="Modifier l'action">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </button>
                                        <?php endif; ?>

                                        <form action="/dossiers/update-action/<?= $dossier['id_dossier'] ?>" method="POST" class="flex items-center">
                                            <input type="hidden" name="id_action" value="<?= $act['id_action'] ?>">
                                            <select name="id_statut" onchange="this.form.submit()" class="text-xs t-input py-1 px-2 pr-8" <?= (int)($dossier['id_workflow'] ?? 0) === 3 ? 'disabled' : '' ?>>
                                                <?php foreach ($statutsAction as $sa): ?>
                                                    <option value="<?= $sa['id_statut'] ?>" <?= $act['id_statut'] == $sa['id_statut'] ? 'selected' : '' ?>><?= e($sa['libelle']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($isChef && (int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                    <form action="/dossiers/add-action/<?= $dossier['id_dossier'] ?>" method="POST" class="space-y-4 pt-6 border-t border-ink-100">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-500">Ajouter une action</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-ink-600 mb-1">Nom de l'action <span class="text-rose">*</span></label>
                                <input type="text" name="nom" class="t-input text-sm" placeholder="Nom de l'action" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-600 mb-1">Assigner à</label>
                                <select name="assign_to" class="t-input text-sm">
                                    <option value="">Assigner à...</option>
                                    <?php foreach ($membres as $m): ?>
                                        <option value="<?= $m['id_user'] ?>"><?= e($m['prenom'] . ' ' . $m['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-ink-600 mb-1">Description</label>
                            <textarea name="description" rows="2" class="t-input text-sm resize-none" placeholder="Description de la tâche..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-ink-600 mb-1">Priorité</label>
                                <select name="id_priorite" class="t-input text-sm">
                                    <?php foreach ($priorites as $p): ?>
                                        <option value="<?= $p['id_priorite'] ?>" <?= $p['id_priorite'] == 2 ? 'selected' : '' ?>>
                                            <?= e($p['libelle']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-600 mb-1">Date début</label>
                                <input type="date" name="date_debut" class="t-input text-sm py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-600 mb-1">Date fin</label>
                                <input type="date" name="date_fin" class="t-input text-sm py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-600 mb-1">Date limite</label>
                                <input type="date" name="date_limite" class="t-input text-sm py-1.5">
                            </div>
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

            <div class="bg-white rounded-2xl shadow-card p-5">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Outils & Actions</h2>
                <div class="flex flex-wrap gap-3">
                    <?php if ($isOwner && $isChef && (int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                    <a href="/dossiers/edit/<?= $dossier['id_dossier'] ?>" class="btn-primary text-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Modifier le dossier
                    </a>
                    <?php endif; ?>
                    
                    <a href="/dossiers/export/<?= $dossier['id_dossier'] ?>" target="_blank" class="btn-ghost text-sm flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-rose">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Exporter en PDF
                    </a>

                    <?php if ($isOwner && $isChef): ?>
                    <form action="/dossiers/delete/<?= $dossier['id_dossier'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer ce dossier définitivement ?')">
                        <button type="submit" class="btn-danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
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
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-jade flex-shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    <a href="<?= e($f['chemin']) ?>" target="_blank" class="font-medium text-ink hover:underline truncate">
                                        <?= e($f['nom']) ?>
                                    </a>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <span class="text-xs text-ink-400"><?= e($f['auteur_prenom'] . ' ' . $f['auteur_nom']) ?></span>
                                    
                                    <?php 
                                    $canDeleteFile = ((int)($dossier['id_workflow'] ?? 0) !== 3) && (
                                        $isAdmin || 
                                        $isOwner || 
                                        ((int)$f['ajoute_par'] === (int)$user['id'])
                                    );
                                    if ($canDeleteFile): ?>
                                        <form action="/dossiers/delete-file/<?= $dossier['id_dossier'] ?>" method="POST"
                                              onsubmit="return confirm('Supprimer cette pièce jointe ?')" class="inline">
                                            <input type="hidden" name="id_fichier" value="<?= $f['id_fichier'] ?>">
                                            <button type="submit" class="text-ink-400 hover:text-rose p-1 rounded transition-colors" title="Supprimer">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (((int)($dossier['id_workflow'] ?? 0) !== 3) && ($isChef || (int)($dossier['droit_depot'] ?? 1) === 1)): ?>
                    <form action="/dossiers/upload/<?= $dossier['id_dossier'] ?>" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 mt-2">
                        <input type="file" name="fichier" class="text-sm text-ink-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-jade-50 file:text-jade hover:file:bg-jade-100" required>
                        <button type="submit" class="btn-primary text-xs py-2 px-3">Ajouter</button>
                    </form>
                <?php elseif ((int)($dossier['id_workflow'] ?? 0) === 3): ?>
                    <p class="text-xs text-ink-500 italic mt-2">Dossier archivé. Ajout de pièces jointes désactivé.</p>
                <?php else: ?>
                    <p class="text-xs text-ink-500 italic mt-2">Le dépôt de pièces jointes a été désactivé par le responsable.</p>
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
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="px-2 py-1 bg-ink-100 text-ink-600 rounded text-xs font-semibold"><?= e($p['niveau_acces']) ?></span>
                                    <?php if (($isOwner || $isAdmin) && (int)($dossier['id_workflow'] ?? 0) !== 3): ?>
                                    <form action="/dossiers/unshare/<?= $dossier['id_dossier'] ?>" method="POST"
                                          onsubmit="return confirm('Révoquer l\'accès de <?= e($p['prenom'] . ' ' . $p['nom']) ?> ?')"
                                          class="inline">
                                        <input type="hidden" name="id_user" value="<?= $p['id_user'] ?>">
                                        <button type="submit"
                                                class="text-ink-400 hover:text-rose p-1 rounded transition-colors"
                                                title="Révoquer l'accès">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
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

            <!-- Demandes en attente de validation (visible créateur/admin) -->
            <?php if (!empty($demandesEnAttente)): ?>
            <div class="bg-white rounded-2xl shadow-card p-5 mt-6 hover-lift border-l-4 border-sun">
                <div class="flex items-center gap-2 mb-4">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-sun flex-shrink-0">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <h2 class="text-sm font-semibold text-ink uppercase tracking-wider">Demandes en attente</h2>
                    <span class="ml-auto badge badge-sun"><?= count($demandesEnAttente) ?></span>
                </div>

                <ul class="space-y-4">
                <?php foreach ($demandesEnAttente as $dmd): ?>
                    <?php
                        $typeLabels = [
                            'upload_fichier'      => 'Upload de fichier',
                            'modif_action'        => 'Modification d\'action',
                            'changement_workflow' => 'Changement de workflow',
                        ];
                        $typeLabel = $typeLabels[$dmd['type_demande']] ?? $dmd['type_demande'];
                        $payload   = $dmd['payload'];
                    ?>
                    <li class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-700"><?= e($typeLabel) ?></span>
                                <p class="text-sm font-semibold text-ink mt-0.5">
                                    <?= e($dmd['prenom'] . ' ' . $dmd['nom']) ?>
                                    <span class="font-normal text-ink-500">demande :</span>
                                </p>

                                <?php if ($dmd['type_demande'] === 'upload_fichier'): ?>
                                    <p class="text-xs text-ink-600 mt-1">Fichier : <strong><?= e($payload['nom'] ?? '—') ?></strong></p>
                                    <p class="text-xs text-ink-400"><?= number_format(($payload['taille'] ?? 0) / 1024, 1) ?> Ko</p>

                                <?php elseif ($dmd['type_demande'] === 'modif_action'): ?>
                                    <p class="text-xs text-ink-600 mt-1">Action : <strong><?= e($payload['nom'] ?? '—') ?></strong></p>
                                    <?php if (!empty($payload['description'])): ?>
                                        <p class="text-xs text-ink-500 mt-0.5"><?= e($payload['description']) ?></p>
                                    <?php endif; ?>

                                <?php elseif ($dmd['type_demande'] === 'changement_workflow'): ?>
                                    <p class="text-xs text-ink-600 mt-1">
                                        Workflow souhaité : <strong><?= e($payload['workflow_libelle'] ?? '—') ?></strong>
                                    </p>
                                <?php endif; ?>

                                <p class="text-[10px] text-ink-400 mt-1"><?= date('d/m/Y H:i', strtotime($dmd['created_at'])) ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-amber-200">
                            <!-- Approuver -->
                            <form action="/dossiers/approuver-demande/<?= $dossier['id_dossier'] ?>" method="POST" class="inline">
                                <input type="hidden" name="id_demande" value="<?= $dmd['id_demande'] ?>">
                                <button type="submit" class="btn-jade text-xs py-1.5 px-3">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3 h-3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Approuver
                                </button>
                            </form>

                            <!-- Rejeter -->
                            <button type="button"
                                    onclick="openRejectModal(<?= $dmd['id_demande'] ?>)"
                                    class="btn-danger text-xs py-1.5 px-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3 h-3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Rejeter
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
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

<!-- Modal de modification d'action -->
<dialog id="editActionDialog" class="rounded-2xl shadow-lift border border-ink-100 max-w-lg w-full p-0 bg-white backdrop:bg-slate-900/50 page-in">
    <div class="bg-primary px-6 py-4 flex items-center justify-between text-primary-foreground">
        <h3 class="font-display text-lg font-bold">Modifier l'action</h3>
        <button type="button" onclick="closeEditActionModal()" class="text-white/80 hover:text-white">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    <form id="editActionForm" action="/dossiers/update-action/<?= $dossier['id_dossier'] ?>" method="POST" class="p-6 space-y-4">
        <input type="hidden" name="id_action" id="edit_id_action">
        
        <div>
            <label class="block text-xs font-semibold text-ink-600 mb-1">Nom de l'action <span class="text-rose">*</span></label>
            <input type="text" name="nom" id="edit_nom" class="t-input text-sm" required>
        </div>

        <div>
            <label class="block text-xs font-semibold text-ink-600 mb-1">Description</label>
            <textarea name="description" id="edit_description" rows="2" class="t-input text-sm resize-none"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-ink-600 mb-1">Assigner à</label>
                <select name="assign_to" id="edit_assign_to" class="t-input text-sm">
                    <option value="">Non assigné</option>
                    <?php foreach ($membres as $m): ?>
                        <option value="<?= $m['id_user'] ?>"><?= e($m['prenom'] . ' ' . $m['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-ink-600 mb-1">Priorité</label>
                <select name="id_priorite" id="edit_id_priorite" class="t-input text-sm">
                    <?php foreach ($priorites as $p): ?>
                        <option value="<?= $p['id_priorite'] ?>"><?= e($p['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-ink-600 mb-1">Date début</label>
                <input type="date" name="date_debut" id="edit_date_debut" class="t-input text-sm py-1.5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-ink-600 mb-1">Date fin</label>
                <input type="date" name="date_fin" id="edit_date_fin" class="t-input text-sm py-1.5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-ink-600 mb-1">Date limite</label>
                <input type="date" name="date_limite" id="edit_date_limite" class="t-input text-sm py-1.5">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-ink-600 mb-1">Statut</label>
            <select name="id_statut" id="edit_id_statut" class="t-input text-sm">
                <?php foreach ($statutsAction as $sa): ?>
                    <option value="<?= $sa['id_statut'] ?>"><?= e($sa['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-ink-100">
            <button type="button" onclick="confirmDeleteAction()" class="btn-danger text-xs py-2 px-4 mr-auto">Supprimer l'action</button>
            <button type="button" onclick="closeEditActionModal()" class="btn-ghost text-xs py-2 px-4">Annuler</button>
            <button type="submit" class="btn-primary text-xs py-2 px-4">Enregistrer les modifications</button>
        </div>
    </form>
</dialog>

<script>
const editDialog = document.getElementById('editActionDialog');

function openEditActionModal(action) {
    document.getElementById('edit_id_action').value = action.id_action;
    document.getElementById('edit_nom').value = action.nom;
    document.getElementById('edit_description').value = action.description || '';
    document.getElementById('edit_assign_to').value = action.id_user || '';
    document.getElementById('edit_id_priorite').value = action.id_priorite;
    document.getElementById('edit_date_debut').value = action.date_debut || '';
    document.getElementById('edit_date_fin').value = action.date_fin || '';
    document.getElementById('edit_date_limite').value = action.date_limite || '';
    document.getElementById('edit_id_statut').value = action.id_statut;
    
    editDialog.showModal();
}

function closeEditActionModal() {
    editDialog.close();
}

function confirmDeleteAction() {
    if (confirm('Supprimer cette action définitivement ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/dossiers/delete-action/<?= $dossier['id_dossier'] ?>';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_action';
        input.value = document.getElementById('edit_id_action').value;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function openRejectModal(idDemande) {
    document.getElementById('reject_id_demande').value = idDemande;
    document.getElementById('rejectDialog').showModal();
}

function openWorkflowRequestModal() {
    document.getElementById('workflowRequestDialog').showModal();
}

</script>

<!-- Modal de rejet avec commentaire -->
<dialog id="rejectDialog" class="rounded-2xl shadow-lift border border-ink-100 max-w-md w-full p-0 bg-white backdrop:bg-slate-900/50">
    <div class="bg-rose px-6 py-4 flex items-center justify-between text-white">
        <h3 class="font-display text-base font-bold">Rejeter la demande</h3>
        <button type="button" onclick="document.getElementById('rejectDialog').close()" class="text-white/80 hover:text-white">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <form id="rejectForm" action="/dossiers/rejeter-demande/<?= $dossier['id_dossier'] ?>" method="POST" class="p-6 space-y-4">
        <input type="hidden" name="id_demande" id="reject_id_demande">
        <div>
            <label class="block text-xs font-semibold text-ink-600 mb-1">Motif du rejet <span class="text-ink-400 font-normal">(optionnel)</span></label>
            <textarea name="commentaire" rows="3" class="t-input text-sm resize-none" placeholder="Expliquez la raison du rejet au collaborateur…"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-ink-100">
            <button type="button" onclick="document.getElementById('rejectDialog').close()" class="btn-ghost text-xs py-2 px-4">Annuler</button>
            <button type="submit" class="btn-danger text-xs py-2 px-4">Confirmer le rejet</button>
        </div>
    </form>
</dialog>

<!-- Modal de demande de changement de workflow (collaborateur) -->
<dialog id="workflowRequestDialog" class="rounded-2xl shadow-lift border border-ink-100 max-w-md w-full p-0 bg-white backdrop:bg-slate-900/50">
    <div class="bg-primary px-6 py-4 flex items-center justify-between text-primary-foreground">
        <h3 class="font-display text-base font-bold">Demander un changement de workflow</h3>
        <button type="button" onclick="document.getElementById('workflowRequestDialog').close()" class="text-white/80 hover:text-white">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <form action="/dossiers/demande-workflow/<?= $dossier['id_dossier'] ?>" method="POST" class="p-6 space-y-4">
        <p class="text-xs text-ink-500">Le responsable devra approuver ce changement avant qu'il soit effectif.</p>
        <?php
            $allWorkflows = (new Workflow())->getAllStatuts();
            $wfActuelId   = (int)($dossier['id_workflow'] ?? 1);
        ?>
        <div>
            <label class="block text-xs font-semibold text-ink-600 mb-1">Étape souhaitée <span class="text-rose">*</span></label>
            <select name="id_workflow" class="t-input text-sm" required>
                <?php foreach ($allWorkflows as $wf): ?>
                    <?php if ((int)$wf['id_workflow'] === $wfActuelId) continue; ?>
                    <option value="<?= $wf['id_workflow'] ?>"><?= e($wf['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-ink-100">
            <button type="button" onclick="document.getElementById('workflowRequestDialog').close()" class="btn-ghost text-xs py-2 px-4">Annuler</button>
            <button type="submit" class="btn-primary text-xs py-2 px-4">Envoyer la demande</button>
        </div>
    </form>
</dialog>

<?php require __DIR__ . '/../partials/footer.php'; ?>
