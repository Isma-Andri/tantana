<?php
// views/projets/index.php
$pageTitle = 'Mes Projets';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';

$user   = $_SESSION['user'];
$isChef = $user['role'] === 'Chef de projet';

// Calcul statistiques
$total    = count($projets);
$enCours  = count(array_filter($projets, fn($p) => $p['statut_libelle'] === 'En cours'));
$termines  = count(array_filter($projets, fn($p) => $p['statut_libelle'] === 'Terminé'));

// Mapping couleurs de statut
$statutColors = [
    'En attente' => 'badge-gray',
    'En cours'   => 'badge-jade',
    'Terminé'    => 'badge-sun',
    'Annulé'     => 'badge-rose',
];

function formatDate(?string $date): string {
    if (!$date) return '—';
    return date('d/m/Y', strtotime($date));
}

function urgencyClass(?string $dateLimit): string {
    if (!$dateLimit) return '';
    $days = (int) ceil((strtotime($dateLimit) - time()) / 86400);
    if ($days < 0)  return 'text-rose font-semibold';
    if ($days <= 7) return 'text-sun font-semibold';
    return 'text-ink-500';
}
?>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <!-- En-tête du dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display text-3xl font-extrabold text-ink">Tableau de bord</h1>
            <p class="text-ink-500 text-sm mt-1">
                <?= $isChef ? 'Gérez et suivez vos projets.' : 'Vos projets en cours.' ?>
            </p>
        </div>
        <?php if ($isChef): ?>
        <a href="projets/create" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Nouveau projet
        </a>
        <?php endif; ?>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <?php
        $stats = [
            ['label' => 'Total projets',  'value' => $total,   'icon' => 'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z', 'color' => 'text-ink bg-ink-100'],
            ['label' => 'En cours',       'value' => $enCours, 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',                                                  'color' => 'text-jade bg-jade-light'],
            ['label' => 'Terminés',       'value' => $termines,'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',                            'color' => 'text-sun bg-sun-light'],
        ];
        foreach ($stats as $s): ?>
        <div class="bg-white rounded-2xl p-5 shadow-card flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center <?= $s['color'] ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $s['icon'] ?>"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-display font-extrabold text-ink"><?= $s['value'] ?></p>
                <p class="text-xs text-ink-500 font-medium"><?= $s['label'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Liste des projets -->
    <?php if (empty($projets)): ?>
    <!-- État vide -->
    <div class="bg-white rounded-2xl shadow-card p-16 text-center">
        <div class="w-16 h-16 rounded-full bg-ink-100 flex items-center justify-center mx-auto mb-4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-8 h-8 text-ink-200">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 0 1 2-2h6l2 2h6a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            </svg>
        </div>
        <h3 class="font-display font-bold text-xl text-ink mb-1">Aucun projet</h3>
        <p class="text-ink-500 text-sm mb-6">
            <?= $isChef ? 'Commencez par créer votre premier projet.' : 'Vous n\'avez été ajouté à aucun projet pour l\'instant.' ?>
        </p>
        <?php if ($isChef): ?>
        <a href="projets/create" class="btn-jade">Créer un projet</a>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Grille de cartes -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($projets as $p):
            $badgeClass = $statutColors[$p['statut_libelle']] ?? 'badge-gray';
            $isOwner    = ($p['cree_par'] == $user['id']);
        ?>
        <div class="bg-white rounded-2xl shadow-card hover:shadow-lift transition-shadow duration-300 flex flex-col overflow-hidden group">

            <!-- Accent couleur en haut -->
            <div class="h-1.5 bg-gradient-to-r from-jade to-jade/40 rounded-t-2xl"></div>

            <div class="p-5 flex flex-col flex-1">
                <!-- Header de la carte -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <h3 class="font-display font-bold text-lg text-ink leading-tight group-hover:text-jade transition-colors line-clamp-2">
                        <?= e($p['nom']) ?>
                    </h3>
                    <span class="badge <?= $badgeClass ?> flex-shrink-0">
                        <?= e($p['statut_libelle']) ?>
                    </span>
                </div>

                <!-- Description -->
                <p class="text-sm text-ink-500 leading-relaxed line-clamp-2 mb-4 flex-1">
                    <?= $p['description'] ? e($p['description']) : '<em>Pas de description</em>' ?>
                </p>

                <!-- Métadonnées -->
                <div class="space-y-2 mb-5">
                    <div class="flex items-center gap-2 text-xs text-ink-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 flex-shrink-0">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>Limite :
                            <span class="<?= urgencyClass($p['date_limite']) ?>">
                                <?= formatDate($p['date_limite']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-ink-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a3 3 0 0 0-5.356-1.857"/>
                        </svg>
                        <span><?= (int)$p['nb_membres'] ?> membre<?= $p['nb_membres'] > 1 ? 's' : '' ?></span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-4 border-t border-ink-100">
                    <a href="projets/show/<?= $p['id_projet'] ?>"
                       class="btn-ghost text-xs flex-1 justify-center py-2">
                        Voir le détail
                    </a>

                    <?php if ($isOwner && $isChef): ?>
                    <a href="projets/edit/<?= $p['id_projet'] ?>"
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-ink-500 hover:bg-ink-100 transition-colors"
                       title="Modifier">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>

                    <form action="projets/delete/<?= $p['id_projet'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer définitivement « <?= e(addslashes($p['nom'])) ?> » ?')">
                        <button type="submit"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-rose hover:bg-rose-light transition-colors"
                                title="Supprimer">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
