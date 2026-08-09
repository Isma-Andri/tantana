<?php
// views/admin/index.php
$pageTitle = 'Administration Système';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';
?>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <!-- Header Banner -->
    <div class="bg-white rounded-2xl shadow-card p-6 mb-8 flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-100 hover-lift">
        <div class="space-y-2 max-w-xl">
            <span class="badge badge-rose">Administration d'État</span>
            <h1 class="font-display text-2xl font-bold text-slate-900">Console d'Administration Globale</h1>
            <p class="text-sm text-slate-500 leading-relaxed">
                Supervisez les utilisateurs, attribuez les privilèges ministériels et consultez l'historique complet d'audit de la plateforme Tantana.
            </p>
        </div>
        <img src="/img/diplomatic_seal.jpg" alt="Sceau Officiel" class="w-24 h-24 rounded-full object-cover border-2 border-jade shadow-sm flex-shrink-0">
    </div>

    <!-- Statistiques Globales -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
        <?php
        $statCards = [
            ['Utilisateurs', $stats['users'], 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0', 'bg-emerald-50 text-jade'],
            ['Dossiers d\'État', $stats['dossiers'], 'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z', 'bg-sky-50 text-sky-700'],
            ['Pièces Jointes', $stats['fichiers'], 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'bg-amber-50 text-amber-700'],
            ['Actions Totales', $stats['actions'], 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'bg-indigo-50 text-indigo-700'],
        ];
        foreach ($statCards as [$label, $val, $icon, $color]): ?>
        <div class="bg-white rounded-2xl p-5 shadow-card hover-lift flex items-center gap-4 border border-slate-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 <?= $color ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-display font-extrabold text-slate-900"><?= $val ?></p>
                <p class="text-xs text-slate-500 font-medium"><?= $label ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Gestion des Utilisateurs -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-card p-6 border border-slate-100 hover-lift">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="font-display text-lg font-bold text-slate-900">Gestion des Utilisateurs & Rôles</h2>
                        <p class="text-xs text-slate-500">Modifiez le niveau de privilège de chaque compte enregistre.</p>
                    </div>
                    <span class="badge badge-gray"><?= count($users) ?> Comptes</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-3">Utilisateur</th>
                                <th class="py-3 px-3">Email</th>
                                <th class="py-3 px-3">Rôle Actuel</th>
                                <th class="py-3 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-3">
                                    <div class="font-semibold text-slate-900"><?= e($u['prenom'] . ' ' . $u['nom']) ?></div>
                                    <div class="text-[11px] text-slate-400">Inscrit le <?= date('d/m/Y', strtotime($u['created_at'])) ?></div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 font-mono text-xs"><?= e($u['email']) ?></td>
                                <td class="py-3.5 px-3">
                                    <form action="/admin/user/role" method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                                        <select name="id_role" onchange="this.form.submit()" class="t-input text-xs py-1 px-2.5 rounded-lg bg-slate-50">
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?= $r['id_role'] ?>" <?= $r['id_role'] == $u['id_role'] ? 'selected' : '' ?>>
                                                    <?= e($r['libelle']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <?php if ($u['id_user'] !== $_SESSION['user']['id']): ?>
                                    <form action="/admin/user/delete" method="POST" onsubmit="return confirm('Supprimer définitivement l\'utilisateur « <?= e($u['prenom'] . ' ' . $u['nom']) ?> » ?')">
                                        <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                                        <button type="submit" class="text-xs text-rose hover:bg-rose-50 px-2.5 py-1.5 rounded-md transition-colors font-medium border border-rose-200">
                                            Supprimer
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-1 rounded">Vous</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audit Log Global -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-card p-6 border border-slate-100 hover-lift">
                <h2 class="font-display text-lg font-bold text-slate-900 mb-4">Audit Global du Système</h2>
                <p class="text-xs text-slate-500 mb-4">Dernières actions enregistrées sur l'ensemble des dossiers.</p>

                <?php if (empty($allLogs)): ?>
                    <p class="text-xs text-slate-400 italic">Aucune activité système.</p>
                <?php else: ?>
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
                        <?php foreach ($allLogs as $log): ?>
                            <div class="text-xs p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center justify-between font-semibold text-slate-900 mb-1">
                                    <span><?= e($log['prenom'] . ' ' . $log['nom']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-normal"><?= date('d/m H:i', strtotime($log['date_action'])) ?></span>
                                </div>
                                <p class="text-slate-600 mb-1"><?= e($log['action']) ?></p>
                                <span class="text-[11px] text-jade font-medium">📁 <?= e($log['dossier_nom']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
