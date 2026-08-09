<?php
// views/partials/navbar.php
if (empty($_SESSION['user'])) return;

$user     = $_SESSION['user'];
$initials = mb_strtoupper(mb_substr($user['prenom'], 0, 1) . mb_substr($user['nom'], 0, 1));
$isChef   = $user['role'] === 'Responsable de dossier';
$isAdmin  = $user['role'] === 'Administrateur';
?>

<nav class="bg-background border-b border-border sticky top-0 z-50">
    <div class="max-w-[90rem] 2xl:max-w-[98rem] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
        <div class="flex items-center justify-between h-16">

            <a href="/dossiers" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-md bg-primary flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-primary-foreground" stroke="currentColor" stroke-width="2.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <span class="font-semibold text-lg text-foreground group-hover:text-muted-foreground transition-colors">Tantana</span>
            </a>

            <div class="hidden md:flex items-center gap-2">
                <a href="/dossiers" class="px-3 py-1.5 rounded-md text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent transition-colors">
                    Dossiers
                </a>
                <?php if ($isChef || $isAdmin): ?>
                <a href="/dossiers/create" class="px-3 py-1.5 rounded-md text-sm font-medium text-primary-foreground bg-primary hover:opacity-90 transition-colors">
                    + Nouveau dossier
                </a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                <a href="/admin" class="px-3 py-1.5 rounded-md text-sm font-medium text-destructive bg-secondary border border-border hover:bg-accent transition-colors">
                    Console Admin
                </a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col items-end leading-tight">
                    <span class="text-sm font-semibold text-foreground"><?= e($user['prenom'] . ' ' . $user['nom']) ?></span>
                    <span class="text-xs text-muted-foreground"><?= e($user['role']) ?></span>
                </div>
                <div class="w-9 h-9 rounded-full bg-secondary border border-border text-secondary-foreground flex items-center justify-center text-xs font-bold">
                    <?= e($initials) ?>
                </div>

                <!-- Theme Toggle Button -->
                <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center rounded-md border border-border text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors" title="Changer de thème">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 hidden dark:block">
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 dark:hidden">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>

                <form action="/logout" method="POST">
                    <button type="submit" title="Se déconnecter"
                            class="w-9 h-9 flex items-center justify-center rounded-md text-muted-foreground border border-border hover:bg-destructive hover:text-destructive-foreground transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleTheme() {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
}
</script>
