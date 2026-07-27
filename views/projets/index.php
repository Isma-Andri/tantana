<?php
// views/projets/index.php
$pageTitle = 'Mes Projets';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';

$user    = $_SESSION['user'];
$isChef  = $user['role'] === 'Responsable de dossier';
$total   = count($projets);
$enCours = count(array_filter($projets, fn($p) => $p['statut_libelle'] === 'En cours'));
$termines = count(array_filter($projets, fn($p) => $p['statut_libelle'] === 'Terminé'));

$statutColors = [
    'En attente' => 'badge-gray',
    'En cours'   => 'badge-jade',
    'Terminé'    => 'badge-sun',
    'Annulé'     => 'badge-rose',
];

function formatDate(?string $date): string
{
    return $date ? date('d/m/Y', strtotime($date)) : '—';
}

function urgencyClass(?string $dateLimit): string
{
    if (!$dateLimit) return 'text-ink-500';
    $days = (int) ceil((strtotime($dateLimit) - time()) / 86400);
    if ($days < 0)  return 'text-rose font-semibold';
    if ($days <= 7) return 'text-sun font-semibold';
    return 'text-ink-500';
}
?>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <div class="bg-white rounded-2xl shadow-card p-6 mb-8 flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-100 hover-lift">
        <div class="space-y-2 max-w-xl">
            <span class="badge badge-jade">Agenda d'État</span>
            <h1 class="font-display text-2xl font-bold text-slate-900">Tableau de Bord Diplomatique</h1>
            <p class="text-sm text-slate-500 leading-relaxed">
                <?= $isChef ? 'Gérez et suivez l\'évolution des traités, résolutions et dossiers de politique interministériels.' : 'Consultez les dossiers de politique et actions auxquels vous collaborez.' ?>
            </p>
            <?php if ($isChef): ?>
            <div class="pt-2">
                <a href="/projets/create" class="btn-primary text-xs py-2 px-4">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5"><path d="M12 5v14M5 12h14"/></svg>
                    Nouveau dossier
                </a>
            </div>
            <?php endif; ?>
        </div>
        <img src="/img/diplomatic_summit.jpg" alt="Sommet Diplomatique" class="w-full md:w-72 h-36 rounded-xl object-cover border border-slate-200/60 shadow-sm flex-shrink-0">
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <?php
        $stats = [
            ['Total dossiers', $total,    'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z', 'text-ink bg-ink-100'],
            ['En cours',      $enCours,  'M13 10V3L4 14h7v7l9-11h-7z',                                                'text-jade bg-jade-light'],
            ['Terminés',      $termines, 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',                         'text-sun bg-sun-light'],
        ];
        foreach ($stats as [$label, $value, $icon, $color]): ?>
        <div class="bg-white rounded-2xl p-5 shadow-card flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center <?= $color ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-display font-extrabold text-ink"><?= $value ?></p>
                <p class="text-xs text-ink-500 font-medium"><?= $label ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Liste des projets -->
    <?php if (empty($projets)): ?>
    <div class="bg-white rounded-2xl shadow-card p-12 text-center max-w-lg mx-auto">
        <img src="/img/diplomatic_desk.jpg" alt="Aucun dossier" class="w-36 h-36 rounded-2xl border-2 border-slate-100 shadow-sm mx-auto mb-6 object-cover float-slow">
        <h3 class="font-display font-bold text-xl text-slate-900 mb-1">Aucun dossier de politique</h3>
        <p class="text-ink-500 text-sm mb-6">
            <?= $isChef ? 'Commencez par créer votre premier dossier de politique.' : 'Vous n\'avez été ajouté à aucun dossier pour l\'instant.' ?>
        </p>
        <?php if ($isChef): ?>
        <a href="/projets/create" class="btn-jade">Créer un dossier</a>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($projets as $p):
            $badgeClass = $statutColors[$p['statut_libelle']] ?? 'badge-gray';
            $isOwner    = ((int) $p['cree_par'] === (int) $user['id']);
        ?>
        <div class="bg-white rounded-2xl shadow-card hover-lift transition-all duration-300 flex flex-col overflow-hidden group">
            <div class="h-1.5 bg-[#064e3b] rounded-t-2xl"></div>
            <div class="p-5 flex flex-col flex-1">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <h3 class="font-display font-bold text-lg text-ink leading-tight group-hover:text-jade transition-colors line-clamp-2">
                        <?= e($p['nom']) ?>
                    </h3>
                    <span class="badge <?= $badgeClass ?> flex-shrink-0"><?= e($p['statut_libelle']) ?></span>
                </div>

                <p class="text-sm text-ink-500 leading-relaxed line-clamp-2 mb-4 flex-1">
                    <?= $p['description'] ? e($p['description']) : '<em>Pas de description</em>' ?>
                </p>

                <div class="space-y-2 mb-5">
                    <div class="flex items-center gap-2 text-xs text-ink-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 flex-shrink-0">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>Limite : <span class="<?= urgencyClass($p['date_limite']) ?>"><?= formatDate($p['date_limite']) ?></span></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-ink-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a3 3 0 0 0-5.356-1.857"/>
                        </svg>
                        <span><?= (int) $p['nb_membres'] ?> collaborateur<?= $p['nb_membres'] > 1 ? 's' : '' ?></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-4 border-t border-ink-100">
                    <a href="/projets/show/<?= $p['id_projet'] ?>" class="btn-ghost text-xs flex-1 justify-center py-2">
                        Voir le détail
                    </a>
                    <?php if ($isOwner && $isChef): ?>
                    <a href="/projets/edit/<?= $p['id_projet'] ?>"
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-ink-500 hover:bg-ink-100 transition-colors" title="Modifier">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                    <form action="/projets/delete/<?= $p['id_projet'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer « <?= e(addslashes($p['nom'])) ?> » ?')">
                        <button type="submit"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-rose hover:bg-rose-light transition-colors" title="Supprimer">
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
