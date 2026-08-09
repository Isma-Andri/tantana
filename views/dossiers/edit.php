<?php
// views/dossiers/edit.php
$pageTitle = 'Modifier — ' . $dossier['nom'];
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';
?>

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8 flex-wrap">
        <a href="/dossiers" class="hover:text-ink transition-colors">Dossiers</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <a href="/dossiers/show/<?= $dossier['id_dossier'] ?>" class="hover:text-ink transition-colors"><?= e($dossier['nom']) ?></a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium">Modifier</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="bg-primary px-8 py-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-primary-foreground">Modifier le dossier</h1>
                <p class="text-primary-foreground/70 text-sm mt-1">Modifiez les informations du dossier de politique.</p>
            </div>
            <img src="/img/signed_treaty.jpg" alt="Traité" class="w-16 h-16 rounded-xl object-cover border-2 border-primary-foreground/20 shadow-sm flex-shrink-0 hidden sm:block">
        </div>

        <form action="/dossiers/edit/<?= $dossier['id_dossier'] ?>" method="POST" class="p-8" novalidate>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Colonne principale : Nom, Description, Collaborateurs -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5" for="nom">
                            Nom du dossier <span class="text-rose">*</span>
                        </label>
                        <input type="text" id="nom" name="nom" class="t-input"
                               value="<?= e($_POST['nom'] ?? $dossier['nom']) ?>"
                               maxlength="255" required autofocus>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5" for="description">Description</label>
                        <textarea id="description" name="description" rows="5" class="t-input resize-none"
                                  maxlength="2000"><?= e($_POST['description'] ?? $dossier['description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">Collaborateurs</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-3 border border-ink-100 rounded-xl bg-ink-50">
                            <?php if (empty($users)): ?>
                                <p class="text-xs text-ink-500 italic col-span-2">Aucun utilisateur disponible.</p>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <?php if ((int)$u['id_user'] === (int)$dossier['cree_par']) continue; ?>
                                    <label class="flex items-center gap-2.5 p-2 bg-white rounded-lg border border-ink-100 hover:border-jade cursor-pointer transition-colors">
                                        <input type="checkbox" name="collaborateurs[]" value="<?= $u['id_user'] ?>" class="rounded border-ink-300 text-jade focus:ring-jade"
                                            <?= in_array((int)$u['id_user'], $currentMemberIds) ? 'checked' : '' ?>>
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

                <!-- Colonne latérale : Statuts, Dates, Options et Boutons d'action -->
                <div class="space-y-6 lg:border-l lg:border-ink-100 lg:pl-8">
                    <div class="space-y-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-ink-500">Statuts & Workflow</h3>
                        
                        <div>
                            <label class="block text-sm font-semibold text-ink mb-1.5" for="id_statut">Statut</label>
                            <select name="id_statut" id="id_statut" class="t-input" required>
                                <?php foreach ($statuts as $s): ?>
                                    <option value="<?= $s['id_statut'] ?>" <?= ($s['id_statut'] == ($_POST['id_statut'] ?? $dossier['id_statut'])) ? 'selected' : '' ?>>
                                        <?= e($s['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-ink mb-1.5" for="id_workflow">
                                Workflow d'approbation
                            </label>
                            <select name="id_workflow" id="id_workflow" class="t-input" required>
                                <?php foreach ($workflows as $w): ?>
                                    <option value="<?= $w['id_workflow'] ?>" <?= ($w['id_workflow'] == ($_POST['id_workflow'] ?? $dossier['id_workflow'])) ? 'selected' : '' ?>>
                                        <?= e($w['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-ink-100">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-ink-500">Planification & Droits</h3>
                        
                        <?php
                        $dateFields = ['date_debut' => 'Date de début', 'date_fin' => 'Date de fin', 'date_limite' => 'Date limite'];
                        foreach ($dateFields as $name => $label): ?>
                        <div>
                            <label class="block text-sm font-semibold text-ink mb-1.5" for="<?= $name ?>"><?= $label ?></label>
                            <input type="date" id="<?= $name ?>" name="<?= $name ?>" class="t-input"
                                   value="<?= e($_POST[$name] ?? $dossier[$name] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pt-4 border-t border-ink-100">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="droit_depot" name="droit_depot" value="1" class="rounded border-ink-300 text-jade focus:ring-jade mt-1"
                                <?= (int)($_POST['droit_depot'] ?? $dossier['droit_depot'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <label class="text-xs font-semibold text-ink-600 cursor-pointer leading-tight" for="droit_depot">
                                Autoriser les collaborateurs à joindre des fichiers de travail
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-ink-100 flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Enregistrer
                        </button>
                        <a href="/dossiers/show/<?= $dossier['id_dossier'] ?>" class="btn-ghost w-full justify-center">Annuler</a>

                        <div class="pt-4 border-t border-ink-100">
                            <button type="button" 
                                    onclick="if(confirm('Supprimer définitivement ce dossier ?')) { document.getElementById('delete-dossier-form').submit(); }"
                                    class="btn-danger w-full justify-center text-xs py-2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                                Supprimer le dossier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <form id="delete-dossier-form" action="/dossiers/delete/<?= $dossier['id_dossier'] ?>" method="POST" class="hidden"></form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
