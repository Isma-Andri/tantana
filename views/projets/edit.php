<?php
// views/projets/edit.php
$pageTitle = 'Modifier — ' . $projet['nom'];
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';
?>

<main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8 flex-wrap">
        <a href="projets" class="hover:text-ink transition-colors">Projets</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3">
            <path d="M9 18l6-6-6-6"/>
        </svg>
        <a href="projets/show/<?= $projet['id_projet'] ?>" class="hover:text-ink transition-colors">
            <?= e($projet['nom']) ?>
        </a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3">
            <path d="M9 18l6-6-6-6"/>
        </svg>
        <span class="text-ink font-medium">Modifier</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">

        <!-- En-tête -->
        <div class="bg-gradient-to-r from-ink to-ink/80 px-8 py-6">
            <h1 class="font-display text-2xl font-extrabold text-white">Modifier le projet</h1>
            <p class="text-white/60 text-sm mt-1">Modifiez les informations du projet.</p>
        </div>

        <form action="projets/edit/<?= $projet['id_projet'] ?>" method="POST" class="p-8 space-y-6" novalidate>

            <!-- Nom -->
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="nom">
                    Nom du projet <span class="text-rose">*</span>
                </label>
                <input type="text" id="nom" name="nom" class="t-input"
                       value="<?= e($_POST['nom'] ?? $projet['nom']) ?>"
                       maxlength="255" required autofocus>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="t-input resize-none"
                          maxlength="2000"><?= e($_POST['description'] ?? $projet['description'] ?? '') ?></textarea>
            </div>

            <!-- Statut -->
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="id_statut">Statut</label>
                <select id="id_statut" name="id_statut" class="t-input">
                    <?php foreach ($statuts as $s): ?>
                    <option value="<?= $s['id_statut'] ?>"
                        <?= ($s['id_statut'] == ($_POST['id_statut'] ?? $projet['id_statut'])) ? 'selected' : '' ?>>
                        <?= e($s['libelle']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="date_debut">Date de début</label>
                    <input type="date" id="date_debut" name="date_debut" class="t-input"
                           value="<?= e($_POST['date_debut'] ?? $projet['date_debut'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="date_fin">Date de fin</label>
                    <input type="date" id="date_fin" name="date_fin" class="t-input"
                           value="<?= e($_POST['date_fin'] ?? $projet['date_fin'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="date_limite">Date limite</label>
                    <input type="date" id="date_limite" name="date_limite" class="t-input"
                           value="<?= e($_POST['date_limite'] ?? $projet['date_limite'] ?? '') ?>">
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer les modifications
                </button>
                <a href="projets/show/<?= $projet['id_projet'] ?>" class="btn-ghost">Annuler</a>

                <!-- Zone de danger -->
                <div class="ml-auto">
                    <form action="projets/delete/<?= $projet['id_projet'] ?>" method="POST"
                          onsubmit="return confirm('Supprimer définitivement ce projet ? Cette action est irréversible.')">
                        <button type="submit" class="btn-danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Supprimer le projet
                        </button>
                    </form>
                </div>
            </div>

        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
