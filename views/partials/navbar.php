<?php
// views/partials/navbar.php
if (empty($_SESSION['user'])) return;

$user     = $_SESSION['user'];
$initials = mb_strtoupper(mb_substr($user['prenom'], 0, 1) . mb_substr($user['nom'], 0, 1));
$isChef   = $user['role'] === 'Responsable de dossier';
$isAdmin  = $user['role'] === 'Administrateur';
?>

<nav class="bg-white border-b border-ink-100 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <a href="/dossiers" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-[#064e3b] flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-white" stroke="currentColor" stroke-width="2.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <span class="font-display text-xl font-bold text-slate-900 group-hover:text-[#064e3b] transition-colors">Tantana</span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                <a href="/dossiers" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    Dossiers
                </a>
                <?php if ($isChef || $isAdmin): ?>
                <a href="/dossiers/create" class="px-4 py-2 rounded-lg text-sm font-medium text-[#064e3b] bg-emerald-50 hover:bg-emerald-100 transition-colors">
                    + Nouveau dossier
                </a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                <a href="/admin" class="px-4 py-2 rounded-lg text-sm font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors">
                    Console Admin
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
