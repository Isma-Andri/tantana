<?php
// views/dossiers/create.php
$pageTitle = 'Nouveau dossier';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';
?>

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8">
        <a href="/dossiers" class="hover:text-ink transition-colors">Dossiers</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium">Nouveau dossier</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="bg-primary px-8 py-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-primary-foreground">Nouveau dossier de politique</h1>
                <p class="text-primary-foreground/70 text-sm mt-1">Définissez les informations essentielles de votre dossier.</p>
            </div>
            <img src="/img/signed_treaty.jpg" alt="Traité" class="w-16 h-16 rounded-xl object-cover border-2 border-primary-foreground/20 shadow-sm flex-shrink-0 hidden sm:block">
        </div>

        <form action="/dossiers/create" method="POST" class="p-8" novalidate>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Colonne principale : Nom, Description, Collaborateurs -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5" for="nom">
                            Nom du dossier <span class="text-rose">*</span>
                        </label>
                        <input type="text" id="nom" name="nom" class="t-input"
                               placeholder="Ex : Refonte du site web"
                               value="<?= e($_POST['nom'] ?? '') ?>"
                               maxlength="255" required autofocus>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5" for="description">Description</label>
                        <textarea id="description" name="description" rows="5" class="t-input resize-none"
                                  placeholder="Décrivez les objectifs et le contexte du dossier…"
                                  maxlength="2000"><?= e($_POST['description'] ?? '') ?></textarea>
                        <p class="text-xs text-ink-500 mt-1">Optionnel, max 2000 caractères</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">Collaborateurs</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-3 border border-ink-100 rounded-xl bg-ink-50">
                            <?php if (empty($users)): ?>
                                <p class="text-xs text-ink-500 italic col-span-2">Aucun utilisateur disponible.</p>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <?php if ((int)$u['id_user'] === (int)$_SESSION['user']['id']) continue; ?>
                                    <label class="flex items-center gap-2.5 p-2 bg-white rounded-lg border border-ink-100 hover:border-jade cursor-pointer transition-colors">
                                        <input type="checkbox" name="collaborateurs[]" value="<?= $u['id_user'] ?>" class="rounded border-ink-300 text-jade focus:ring-jade"
                                            <?= in_array($u['id_user'], $_POST['collaborateurs'] ?? []) ? 'checked' : '' ?>>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-ink truncate"><?= e($u['prenom'] . ' ' . $u['nom']) ?></p>
                                            <p class="text-[10px] text-ink-500 truncate"><?= e($u['email']) ?></p>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-ink-500 mt-1">Optionnel. Sélectionnez les personnes qui participeront à ce dossier.</p>
                    </div>
                </div>

                <!-- Colonne latérale : Dates, Options et Boutons d'action -->
                <div class="space-y-6 lg:border-l lg:border-ink-100 lg:pl-8">
                    <div class="space-y-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-ink-500">Planification & Droits</h3>
                        
                        <?php
                        $dateFields = [
                            ['date_debut',  'Date de début', ''],
                            ['date_fin',    'Date de fin',   ''],
                            ['date_limite', 'Date limite',   '(deadline)'],
                        ];
                        foreach ($dateFields as [$name, $label, $hint]): ?>
                        <div>
                            <label class="block text-sm font-semibold text-ink mb-1.5" for="<?= $name ?>">
                                <?= $label ?>
                                <?php if ($hint): ?><span class="text-xs font-normal text-rose"><?= $hint ?></span><?php endif; ?>
                            </label>
                            <input type="date" id="<?= $name ?>" name="<?= $name ?>" class="t-input"
                                   value="<?= e($_POST[$name] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pt-4 border-t border-ink-100">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="droit_depot" name="droit_depot" value="1" class="rounded border-ink-300 text-jade focus:ring-jade mt-1" checked>
                            <label class="text-xs font-semibold text-ink-600 cursor-pointer leading-tight" for="droit_depot">
                                Autoriser les collaborateurs à joindre des fichiers de travail
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-ink-100 flex flex-col gap-3">
                        <button type="submit" class="btn-jade w-full justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 5v14M5 12h14"/></svg>
                            Créer le dossier
                        </button>
                        <a href="/dossiers" class="btn-ghost w-full justify-center">Annuler</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
