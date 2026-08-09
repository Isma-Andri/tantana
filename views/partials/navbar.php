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

            <a href="/dossiers" class="group py-2 flex items-center">
                <span class="font-display tracking-[0.25em] uppercase font-black text-foreground text-xl transition-colors group-hover:text-jade">T<span class="font-light text-muted-foreground/75">A</span>N<span class="font-light text-muted-foreground/75">T</span>A<span class="font-light text-muted-foreground/75">N</span>A</span>
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
