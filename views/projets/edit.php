<?php
// views/projets/edit.php
$pageTitle = 'Modifier — ' . $projet['nom'];
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';
?>

<main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8 flex-wrap">
        <a href="/projets" class="hover:text-ink transition-colors">Dossiers</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <a href="/projets/show/<?= $projet['id_projet'] ?>" class="hover:text-ink transition-colors"><?= e($projet['nom']) ?></a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium">Modifier</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="bg-[#064e3b] px-8 py-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-white">Modifier le dossier</h1>
                <p class="text-white/70 text-sm mt-1">Modifiez les informations du dossier de politique.</p>
            </div>
            <img src="/img/signed_treaty.jpg" alt="Traité" class="w-16 h-16 rounded-xl object-cover border-2 border-white/20 shadow-sm flex-shrink-0 hidden sm:block">
        </div>

        <form action="/projets/edit/<?= $projet['id_projet'] ?>" method="POST" class="p-8 space-y-6" novalidate>
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="nom">
                    Nom du dossier <span class="text-rose">*</span>
                </label>
                <input type="text" id="nom" name="nom" class="t-input"
                       value="<?= e($_POST['nom'] ?? $projet['nom']) ?>"
                       maxlength="255" required autofocus>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="t-input resize-none"
                          maxlength="2000"><?= e($_POST['description'] ?? $projet['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="id_statut">Statut</label>
                    <select name="id_statut" id="id_statut" class="t-input" required>
                        <?php foreach ($statuts as $s): ?>
                            <option value="<?= $s['id_statut'] ?>" <?= ($s['id_statut'] == ($_POST['id_statut'] ?? $projet['id_statut'])) ? 'selected' : '' ?>>
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
                            <option value="<?= $w['id_workflow'] ?>" <?= ($w['id_workflow'] == ($_POST['id_workflow'] ?? $projet['id_workflow'])) ? 'selected' : '' ?>>
                                <?= e($w['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php
                $dateFields = ['date_debut' => 'Date de début', 'date_fin' => 'Date de fin', 'date_limite' => 'Date limite'];
                foreach ($dateFields as $name => $label): ?>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="<?= $name ?>"><?= $label ?></label>
                    <input type="date" id="<?= $name ?>" name="<?= $name ?>" class="t-input"
                           value="<?= e($_POST[$name] ?? $projet[$name] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer
                </button>
                <a href="/projets/show/<?= $projet['id_projet'] ?>" class="btn-ghost">Annuler</a>

                <div class="ml-auto">
                    <form action="/projets/delete/<?= $projet['id_projet'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer définitivement ce dossier ?')">
                        <button type="submit" class="btn-danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Supprimer le dossier
                        </button>
                    </form>
                </div>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
