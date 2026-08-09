<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier PDF - <?= e($dossier['nom']) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; padding: 40px; margin: 0; }
        .header { border-bottom: 2px solid #005f56; padding-bottom: 10px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #000; margin: 0; }
        .meta { color: #666; font-size: 14px; margin-top: 5px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #e0f2fe; color: #0369a1; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #005f56; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .content { font-size: 14px; text-align: justify; white-space: pre-line; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        th { background-color: #f9fafb; font-weight: bold; }
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <button onclick="window.print()" style="margin-bottom:20px; padding:10px; cursor:pointer;">Imprimer / Sauvegarder en PDF</button>

    <?php
    $totalActions = count($actions);
    $finishedActions = 0;
    $unfinishedActions = 0;
    foreach ($actions as $act) {
        if ((int)$act['id_statut'] === 3) {
            $finishedActions++;
        } else {
            $unfinishedActions++;
        }
    }
    $progression = $totalActions > 0 ? (int)round(($finishedActions / $totalActions) * 100) : 0;
    ?>

    <div class="header">
        <h1 class="title"><?= e($dossier['nom']) ?></h1>
        <div class="meta">
            Dossier de Politique | Créé le <?= date('d/m/Y', strtotime($dossier['date_creation'])) ?>
            par <?= e($dossier['createur_prenom'] . ' ' . $dossier['createur_nom']) ?>
        </div>
        <div style="margin-top:10px;">
            <span class="badge"><?= e($dossier['statut_libelle']) ?></span>
            <?php if(isset($dossier['workflow_libelle'])): ?>
                <span class="badge" style="background:#eef2ff; color:#4f46e5;"><?= e($dossier['workflow_libelle']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Synthèse et Progression du Dossier</div>
        <div style="display: flex; justify-content: space-between; gap: 20px; margin-top: 15px;">
            <div style="flex: 1; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 13px; color: #166534; font-weight: bold; text-transform: uppercase;">Progression Générale</div>
                <div style="font-size: 32px; font-weight: bold; color: #15803d; margin: 10px 0;"><?= $progression ?>%</div>
                <div style="height: 8px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; width: 100%;">
                    <div style="height: 100%; background: #16a34a; width: <?= $progression ?>%;"></div>
                </div>
            </div>
            <div style="flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 13px; color: #475569; font-weight: bold; text-transform: uppercase;">Statistiques des Actions</div>
                <div style="display: flex; justify-content: space-around; margin-top: 15px;">
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #0f172a;"><?= $totalActions ?></div>
                        <div style="font-size: 11px; color: #64748b;">Total</div>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #16a34a;"><?= $finishedActions ?></div>
                        <div style="font-size: 11px; color: #16a34a;">Terminées</div>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #ea580c;"><?= $unfinishedActions ?></div>
                        <div style="font-size: 11px; color: #ea580c;">Restantes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Description Générale</div>
        <div class="content"><?= empty(trim($dossier['description'])) ? 'Aucune description fournie.' : e($dossier['description']) ?></div>
    </div>

    <div class="section">
        <div class="section-title">Calendrier et Échéances</div>
        <table>
            <tr>
                <th>Date de début</th>
                <td><?= $dossier['date_debut'] ? date('d/m/Y', strtotime($dossier['date_debut'])) : 'Non définie' ?></td>
            </tr>
            <tr>
                <th>Date de fin estimée</th>
                <td><?= $dossier['date_fin'] ? date('d/m/Y', strtotime($dossier['date_fin'])) : 'Non définie' ?></td>
            </tr>
            <tr>
                <th>Date limite absolue</th>
                <td><?= $dossier['date_limite'] ? date('d/m/Y', strtotime($dossier['date_limite'])) : 'Non définie' ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Plan d'Actions et Tâches</div>
        <?php if (empty($actions)): ?>
            <p style="font-size:13px; color:#666;">Aucune action définie pour ce dossier.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%;">Action</th>
                        <th style="width: 25%;">Assigné à</th>
                        <th style="width: 15%;">Priorité</th>
                        <th style="width: 15%;">Statut</th>
                        <th style="width: 10%;">Limite</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actions as $act): ?>
                    <tr>
                        <td>
                            <strong><?= e($act['nom']) ?></strong>
                            <?php if (!empty($act['description'])): ?>
                                <br><small style="color: #666; font-style: italic;"><?= e($act['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $act['assigne_nom'] ? e($act['assigne_prenom'] . ' ' . $act['assigne_nom']) : 'Non assigné' ?></td>
                        <td><?= e($act['priorite_libelle']) ?></td>
                        <td>
                            <span style="font-weight: bold; color: <?= (int)$act['id_statut'] === 3 ? '#16a34a' : '#ea580c' ?>;">
                                <?= e($act['statut_libelle']) ?>
                            </span>
                        </td>
                        <td><?= $act['date_limite'] ? date('d/m/Y', strtotime($act['date_limite'])) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">Équipe du Dossier</div>
        <table>
            <tr>
                <th>Nom</th>
                <th>Rôle</th>
                <th>Date d'ajout</th>
            </tr>
            <?php foreach ($membres as $m): ?>
            <tr>
                <td><?= e($m['prenom'] . ' ' . $m['nom']) ?></td>
                <td><?= e($m['role_dans_dossier']) ?></td>
                <td><?= date('d/m/Y', strtotime($m['date_participation'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Pièces Jointes Rattachées</div>
        <?php if(empty($fichiers)): ?>
            <p style="font-size:13px; color:#666;">Aucune pièce jointe.</p>
        <?php else: ?>
            <ul>
                <?php foreach($fichiers as $f): ?>
                    <li style="font-size:13px; margin-bottom:5px;"><?= e($f['nom']) ?> (ajouté par <?= e($f['auteur_prenom'] . ' ' . $f['auteur_nom']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

</body>
</html>
