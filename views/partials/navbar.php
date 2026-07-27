<?php
// views/partials/navbar.php
if (empty($_SESSION['user'])) return;

$user     = $_SESSION['user'];
$initials = mb_strtoupper(mb_substr($user['prenom'], 0, 1) . mb_substr($user['nom'], 0, 1));
$isChef   = $user['role'] === 'Chef de projet';
?>

<nav class="bg-white border-b border-ink-100 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <a href="/projets" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-ink flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-jade" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <span class="font-display text-xl font-bold text-ink group-hover:text-jade transition-colors">Tantana</span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                <a href="/projets" class="px-4 py-2 rounded-lg text-sm font-medium text-ink-500 hover:text-ink hover:bg-ink-50 transition-colors">
                    Projets
                </a>
                <?php if ($isChef): ?>
                <a href="/projets/create" class="px-4 py-2 rounded-lg text-sm font-medium text-jade hover:bg-jade-light transition-colors">
                    + Nouveau projet
                </a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col items-end leading-tight">
                    <span class="text-sm font-semibold text-ink"><?= e($user['prenom'] . ' ' . $user['nom']) ?></span>
                    <span class="text-xs text-ink-500"><?= e($user['role']) ?></span>
                </div>
                <div class="w-9 h-9 rounded-full bg-ink flex items-center justify-center text-white text-xs font-bold">
                    <?= e($initials) ?>
                </div>
                <form action="/logout" method="POST">
                    <button type="submit" title="Se déconnecter"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-ink-500 hover:bg-rose-light hover:text-rose transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
