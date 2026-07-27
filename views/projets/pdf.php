<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier PDF - <?= e($projet['nom']) ?></title>
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

    <div class="header">
        <h1 class="title"><?= e($projet['nom']) ?></h1>
        <div class="meta">
            Dossier de Politique | Créé le <?= date('d/m/Y', strtotime($projet['date_creation'])) ?>
            par <?= e($projet['createur_prenom'] . ' ' . $projet['createur_nom']) ?>
        </div>
        <div style="margin-top:10px;">
            <span class="badge"><?= e($projet['statut_libelle']) ?></span>
            <?php if(isset($projet['workflow_libelle'])): ?>
                <span class="badge" style="background:#eef2ff; color:#4f46e5;"><?= e($projet['workflow_libelle']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Description Générale</div>
        <div class="content"><?= empty(trim($projet['description'])) ? 'Aucune description fournie.' : e($projet['description']) ?></div>
    </div>

    <div class="section">
        <div class="section-title">Calendrier et Échéances</div>
        <table>
            <tr>
                <th>Date de début</th>
                <td><?= $projet['date_debut'] ? date('d/m/Y', strtotime($projet['date_debut'])) : 'Non définie' ?></td>
            </tr>
            <tr>
                <th>Date de fin estimée</th>
                <td><?= $projet['date_fin'] ? date('d/m/Y', strtotime($projet['date_fin'])) : 'Non définie' ?></td>
            </tr>
            <tr>
                <th>Date limite absolue</th>
                <td><?= $projet['date_limite'] ? date('d/m/Y', strtotime($projet['date_limite'])) : 'Non définie' ?></td>
            </tr>
        </table>
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
                <td><?= e($m['role_dans_projet']) ?></td>
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
                    <li style="font-size:13px; margin-bottom:5px;"><?= e($f['nom']) ?> (ajouté par <?= e($f['auteur_prenom']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

</body>
</html>
