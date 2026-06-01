<?php
// views/projets/show.php
$pageTitle = $projet['nom'];
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';

$isOwner = ((int) $projet['cree_par'] === (int) $user['id']);
$isChef  = $user['role'] === 'Chef de projet';

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
if ($projet['date_debut'] && $projet['date_limite']) {
    $total = strtotime($projet['date_limite']) - strtotime($projet['date_debut']);
    if ($total > 0) {
        $progress = max(0, min(100, (int) round((time() - strtotime($projet['date_debut'])) / $total * 100)));
    }
}
?>

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8">
        <a href="projets" class="hover:text-ink transition-colors">Projets</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium"><?= e($projet['nom']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="h-2 bg-gradient-to-r from-jade via-jade/60 to-jade/20"></div>
                <div class="p-7">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <h1 class="font-display text-2xl font-extrabold text-ink leading-tight"><?= e($projet['nom']) ?></h1>
                        <span class="badge <?= $statutColors[$projet['statut_libelle']] ?? 'badge-gray' ?> flex-shrink-0">
                            <?= e($projet['statut_libelle']) ?>
                        </span>
                    </div>

                    <?php if ($projet['description']): ?>
                    <p class="text-ink-500 text-sm leading-relaxed mb-6"><?= nl2br(e($projet['description'])) ?></p>
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
                            ['Création',   $projet['date_creation']],
                            ['Début',      $projet['date_debut']],
                            ['Fin prévue', $projet['date_fin']],
                            ['Deadline',   $projet['date_limite']],
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

            <?php if ($isOwner && $isChef): ?>
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Actions</h2>
                <div class="flex flex-wrap gap-3">
                    <a href="projets/edit/<?= $projet['id_projet'] ?>" class="btn-primary text-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Modifier le projet
                    </a>
                    <form action="projets/delete/<?= $projet['id_projet'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer ce projet définitivement ?')">
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

            <div class="bg-white rounded-2xl shadow-card p-5">
                <h2 class="text-sm font-semibold text-ink mb-4 uppercase tracking-wider">Créateur</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-ink flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?= mb_strtoupper(mb_substr($projet['createur_prenom'], 0, 1) . mb_substr($projet['createur_nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink"><?= e($projet['createur_prenom'] . ' ' . $projet['createur_nom']) ?></p>
                        <p class="text-xs text-jade">Chef de projet</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-ink uppercase tracking-wider">Membres</h2>
                    <span class="badge badge-gray"><?= count($membres) ?></span>
                </div>

                <?php if (empty($membres)): ?>
                <p class="text-xs text-ink-500 italic">Aucun membre.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($membres as $m): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-jade-light flex items-center justify-center text-jade text-xs font-bold flex-shrink-0">
                            <?= mb_strtoupper(mb_substr($m['prenom'], 0, 1) . mb_substr($m['nom'], 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink truncate"><?= e($m['prenom'] . ' ' . $m['nom']) ?></p>
                            <p class="text-xs text-ink-500"><?= e($m['role_dans_projet'] ?? 'Membre') ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <a href="projets" class="btn-ghost w-full justify-center">Retour aux projets</a>
        </div>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
