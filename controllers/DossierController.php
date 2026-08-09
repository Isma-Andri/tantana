<?php
// controllers/DossierController.php

require_once __DIR__ . '/../models/Dossier.php';
require_once __DIR__ . '/../models/Workflow.php';
require_once __DIR__ . '/../models/PartageDossier.php';
require_once __DIR__ . '/../models/Fichier.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Action.php';
require_once __DIR__ . '/../models/DemandeValidation.php';

class DossierController
{
    private Dossier $dossierModel;

    public function __construct()
    {
        requireAuth();
        $this->dossierModel = new Dossier();
    }

    public function index(): void
    {
        $user    = $_SESSION['user'];
        $dossiers = $this->dossierModel->getAllForUser($user['id'], $user['role']);
        require __DIR__ . '/../views/dossiers/index.php';
    }

    public function create(): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $statuts = $this->dossierModel->getStatuts();
        $userModel = new User();
        $users = $userModel->getAllUsers();
        require __DIR__ . '/../views/dossiers/create.php';
    }

    public function store(): void
    {
        requireRole('Responsable de dossier', 'Administrateur');

        if (empty(trim($_POST['nom'] ?? ''))) {
            setFlash('error', 'Le nom du dossier est obligatoire.');
            redirect('dossiers/create');
        }

        $id = $this->dossierModel->create([
            'nom'         => $_POST['nom'],
            'description' => $_POST['description'] ?? '',
            'date_debut'  => $_POST['date_debut']  ?? '',
            'date_fin'    => $_POST['date_fin']    ?? '',
            'date_limite' => $_POST['date_limite'] ?? '',
            'cree_par'    => $_SESSION['user']['id'],
            'droit_depot' => isset($_POST['droit_depot']) ? 1 : 0,
        ]);

        if (!empty($_POST['collaborateurs']) && is_array($_POST['collaborateurs'])) {
            $logModel = new ActivityLog();
            $userModel = new User();
            foreach ($_POST['collaborateurs'] as $userId) {
                $this->dossierModel->addMember($id, (int)$userId, 'Collaborateur');
                $targetUser = $userModel->findById((int)$userId);
                if ($targetUser) {
                    $logModel->log($id, $_SESSION['user']['id'], "A ajouté le collaborateur : " . $targetUser['prenom'] . ' ' . $targetUser['nom']);
                }
            }
        }

        setFlash('success', 'Dossier créé avec succès !');
        redirect('dossiers/show/' . $id);
    }

    public function show(int $id): void
    {
        $dossier  = $this->findOrFail($id);
        $membres = $this->dossierModel->getMembers($id);
        $partages = (new PartageDossier())->getPartagesByDossier($id);
        $fichiers = (new Fichier())->getFichiersByDossier($id);
        $commentaires = (new Commentaire())->getByDossier($id);
        $activityLogs = (new ActivityLog())->getByDossier($id);

        $actionModel = new Action();
        $actions = $actionModel->getByDossier($id);
        $priorites = $actionModel->getPriorites();
        $statutsAction = $actionModel->getStatuts();

        $user = $_SESSION['user'];
        $isOwnerOrAdmin = ((int)$dossier['cree_par'] === (int)$user['id'])
                       || $user['role'] === 'Administrateur';
        $demandesEnAttente = $isOwnerOrAdmin
            ? (new DemandeValidation())->getEnAttenteByDossier($id)
            : [];

        require __DIR__ . '/../views/dossiers/show.php';
    }

    public function edit(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier  = $this->findOrFail($id);
        $this->requireNotSigned($dossier);
        $statuts = $this->dossierModel->getStatuts();
        $workflows = (new Workflow())->getAllStatuts();

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if ((int) $dossier['cree_par'] !== $_SESSION['user']['id'] && !$isAdmin) {
            setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce dossier.');
            redirect('dossiers');
        }

        $userModel = new User();
        $users = $userModel->getAllUsers();
        $membres = $this->dossierModel->getMembers($id);
        $currentMemberIds = array_map(fn($m) => (int) $m['id_user'], $membres);

        require __DIR__ . '/../views/dossiers/edit.php';
    }

    public function update(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        if (empty(trim($_POST['nom'] ?? ''))) {
            setFlash('error', 'Le nom du dossier est obligatoire.');
            redirect('dossiers/edit/' . $id);
        }

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $ok = $this->dossierModel->update($id, [
            'nom'         => $_POST['nom'],
            'description' => $_POST['description'] ?? '',
            'date_debut'  => $_POST['date_debut']  ?? '',
            'date_fin'    => $_POST['date_fin']    ?? '',
            'date_limite' => $_POST['date_limite'] ?? '',
            'id_statut'   => (int) ($_POST['id_statut'] ?? 1),
            'id_workflow' => (int) ($_POST['id_workflow'] ?? 1),
            'droit_depot' => isset($_POST['droit_depot']) ? 1 : 0,
        ], $_SESSION['user']['id'], $isAdmin);

        if (!$ok) {
            setFlash('error', 'Modification impossible.');
            redirect('dossiers');
        }

        $membresAvant = $this->dossierModel->getMembers($id);
        $memberIdsAvant = [];
        $memberNamesAvant = [];
        foreach ($membresAvant as $m) {
            if ((int)$m['id_user'] !== (int)$dossier['cree_par']) {
                $memberIdsAvant[] = (int)$m['id_user'];
                $memberNamesAvant[(int)$m['id_user']] = $m['prenom'] . ' ' . $m['nom'];
            }
        }

        $collaborateurs = $_POST['collaborateurs'] ?? [];
        if (!is_array($collaborateurs)) {
            $collaborateurs = [];
        }
        $collaborateurs = array_map('intval', $collaborateurs);

        $this->dossierModel->syncMembers($id, $collaborateurs, (int)$dossier['cree_par']);

        // Log added/removed collaborators
        $logModel = new ActivityLog();
        $userModel = new User();
        
        foreach ($collaborateurs as $uid) {
            if (!in_array($uid, $memberIdsAvant, true)) {
                $targetUser = $userModel->findById($uid);
                if ($targetUser) {
                    $logModel->log($id, $_SESSION['user']['id'], "A ajouté le collaborateur : " . $targetUser['prenom'] . ' ' . $targetUser['nom']);
                }
            }
        }

        foreach ($memberIdsAvant as $uid) {
            if (!in_array($uid, $collaborateurs, true)) {
                $name = $memberNamesAvant[$uid] ?? 'Collaborateur';
                $logModel->log($id, $_SESSION['user']['id'], "A retiré le collaborateur : " . $name);
            }
        }

        setFlash('success', 'Dossier mis à jour avec succès !');
        redirect('dossiers/show/' . $id);
    }

    public function delete(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $ok = $this->dossierModel->delete($id, $_SESSION['user']['id'], $isAdmin);
        setFlash($ok ? 'success' : 'error', $ok ? 'Dossier supprimé.' : 'Suppression impossible.');
        redirect('dossiers');
    }

    private function findOrFail(int $id): array
    {
        $dossier = $this->dossierModel->findById($id);
        if (!$dossier) {
            setFlash('error', 'Dossier introuvable.');
            redirect('dossiers');
        }

        $user = $_SESSION['user'];
        if (!$this->dossierModel->hasAccess($id, (int)$user['id'], $user['role'])) {
            setFlash('error', 'Accès refusé à ce dossier.');
            redirect('dossiers');
        }

        return $dossier;
    }

    private function requireNotSigned(array $dossier): void
    {
        if ((int)($dossier['id_workflow'] ?? 0) === 3) {
            setFlash('error', 'Ce dossier est signé et verrouillé.');
            redirect('dossiers/show/' . $dossier['id_dossier']);
        }
    }

    public function upload(int $id): void
    {
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $isChef  = $_SESSION['user']['role'] === 'Responsable de dossier' || $isAdmin;

        if (!$isChef && (int)$dossier['droit_depot'] === 0) {
            setFlash('error', 'Le dépôt de fichiers a été désactivé par le responsable pour les collaborateurs.');
            redirect('dossiers/show/' . $id);
        }

        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'Erreur de téléchargement du fichier.');
            redirect('dossiers/show/' . $id);
        }

        $tmpName = $_FILES['fichier']['tmp_name'];
        $name    = basename($_FILES['fichier']['name']);
        $size    = $_FILES['fichier']['size'];

        // --- Collaborateur : soumet une demande de validation ---
        if (!$isChef) {
            // Stocker le fichier temporairement dans uploads/pending/
            $pendingDir = __DIR__ . '/../public/uploads/pending/';
            if (!is_dir($pendingDir)) {
                mkdir($pendingDir, 0775, true);
            }
            $tmpDest = $pendingDir . time() . '_' . $name;
            if (!move_uploaded_file($tmpName, $tmpDest)) {
                setFlash('error', 'Impossible de stocker le fichier temporairement.');
                redirect('dossiers/show/' . $id);
            }
            (new DemandeValidation())->soumettre($id, $_SESSION['user']['id'], 'upload_fichier', [
                'nom'      => $name,
                'chemin_tmp' => str_replace(__DIR__ . '/../public', '', $tmpDest),
                'taille'   => $size,
            ]);
            (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A soumis une demande d\'upload : ' . $name);
            setFlash('success', 'Votre fichier a été soumis et attend la validation du responsable.');
            redirect('dossiers/show/' . $id);
        }

        // --- Chef / Admin : upload direct ---
        $uploadDir = __DIR__ . '/../public/uploads/';
        $destPath  = $uploadDir . time() . '_' . $name;
        if (move_uploaded_file($tmpName, $destPath)) {
            $fichierModel = new Fichier();
            $idFichier = $fichierModel->uploadFichier([
                'nom'    => $name,
                'chemin' => str_replace(__DIR__ . '/../public', '', $destPath),
                'taille' => $size,
            ], $_SESSION['user']['id']);
            if ($idFichier) {
                $fichierModel->linkToDossier($idFichier, $id);
                (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A ajouté une pièce jointe : ' . $name);
                setFlash('success', 'Pièce jointe ajoutée.');
            }
        } else {
            setFlash('error', 'Erreur lors du déplacement du fichier.');
        }
        redirect('dossiers/show/' . $id);
    }

    public function deleteFile(int $id): void
    {
        $dossier = $this->findOrFail($id); // verify exists and access
        $this->requireNotSigned($dossier);

        $idFichier = (int)($_POST['id_fichier'] ?? 0);
        if ($idFichier <= 0) {
            setFlash('error', 'Fichier invalide.');
            redirect('dossiers/show/' . $id);
        }

        $fichierModel = new Fichier();
        $fichier = $fichierModel->findById($idFichier);

        if (!$fichier) {
            setFlash('error', 'Fichier introuvable.');
            redirect('dossiers/show/' . $id);
        }

        // Permission check
        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $isDossierOwner = (int)$dossier['cree_par'] === (int)$_SESSION['user']['id'];
        $isUploader = (int)$fichier['ajoute_par'] === (int)$_SESSION['user']['id'];

        if (!$isAdmin && !$isDossierOwner && !$isUploader) {
            setFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce fichier.');
            redirect('dossiers/show/' . $id);
        }

        // Delete from filesystem
        $filePath = __DIR__ . '/../public' . $fichier['chemin'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $fichierModel->delete($idFichier);

        (new ActivityLog())->log($id, $_SESSION['user']['id'], "A supprimé la pièce jointe: " . $fichier['nom']);
        setFlash('success', 'Pièce jointe supprimée.');
        redirect('dossiers/show/' . $id);
    }

    public function unshare(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if (!$isAdmin && (int)$dossier['cree_par'] !== (int)$_SESSION['user']['id']) {
            setFlash('error', 'Seul le responsable du dossier peut gérer les partages.');
            redirect('dossiers/show/' . $id);
        }

        $idUser = (int)($_POST['id_user'] ?? 0);
        if ($idUser <= 0) {
            setFlash('error', 'Utilisateur invalide.');
            redirect('dossiers/show/' . $id);
        }

        $partageModel = new PartageDossier();
        $target = (new User())->findById($idUser);
        $partageModel->removePartage($id, $idUser);

        if ($target) {
            (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A retiré le partage de ' . $target['prenom'] . ' ' . $target['nom']);
        }

        setFlash('success', 'Accès révoqué.');
        redirect('dossiers/show/' . $id);
    }

    public function share(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        // Only the dossier owner or an admin can share
        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if (!$isAdmin && (int)$dossier['cree_par'] !== (int)$_SESSION['user']['id']) {
            setFlash('error', 'Seul le responsable du dossier peut gérer les partages.');
            redirect('dossiers/show/' . $id);
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $niveau = $_POST['niveau_acces'] ?? 'Lecture';

        $userModel = new User();
        $targetUser = $userModel->findByEmail($email);

        if ($targetUser) {
            (new PartageDossier())->addPartage($id, $targetUser['id_user'], $niveau);
            setFlash('success', 'Dossier partagé avec ' . $email);
        } else {
            setFlash('error', 'Utilisateur introuvable.');
        }
        redirect('dossiers/show/' . $id);
    }

    public function exportPdf(int $id): void
    {
        $dossier = $this->findOrFail($id);
        $membres = $this->dossierModel->getMembers($id);
        $fichiers = (new Fichier())->getFichiersByDossier($id);
        $actions = (new Action())->getByDossier($id);
        require __DIR__ . '/../views/dossiers/pdf.php';
    }

    public function comment(int $id): void
    {
        $dossier = $this->findOrFail($id); // verify exists
        $this->requireNotSigned($dossier);

        $contenu = trim($_POST['contenu'] ?? '');
        if (empty($contenu)) {
            setFlash('error', 'Le commentaire ne peut pas être vide.');
            redirect('dossiers/show/' . $id);
        }

        (new Commentaire())->add($id, $_SESSION['user']['id'], $contenu);
        (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A ajouté un commentaire');

        setFlash('success', 'Commentaire ajouté.');
        redirect('dossiers/show/' . $id);
    }

    public function addAction(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        $nom = trim($_POST['nom'] ?? '');
        if (empty($nom)) {
            setFlash('error', 'Le nom de l\'action est obligatoire.');
            redirect('dossiers/show/' . $id);
        }

        $_POST['id_dossier'] = $id;
        $actionModel = new Action();
        $idAction = $actionModel->create($_POST);

        if (!empty($_POST['assign_to'])) {
            $actionModel->assign($idAction, (int)$_POST['assign_to']);
        }

        (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A créé une nouvelle action: ' . $nom);

        setFlash('success', 'Action ajoutée.');
        redirect('dossiers/show/' . $id);
    }

    public function updateAction(int $id): void
    {
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        $idAction = (int)($_POST['id_action'] ?? 0);
        if ($idAction <= 0) {
            setFlash('error', 'Action invalide.');
            redirect('dossiers/show/' . $id);
        }

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $isChef  = $_SESSION['user']['role'] === 'Responsable de dossier' || $isAdmin;
        $actionModel = new Action();

        // --- Modification complète (nom, dates, priorité…) ---
        if (isset($_POST['nom'])) {
            $nom = trim($_POST['nom']);
            if (empty($nom)) {
                setFlash('error', 'Le nom de l\'action est obligatoire.');
                redirect('dossiers/show/' . $id);
            }

            $payload = [
                'id_action'   => $idAction,
                'nom'         => $nom,
                'description' => $_POST['description'] ?? '',
                'date_debut'  => $_POST['date_debut'] ?? '',
                'date_fin'    => $_POST['date_fin'] ?? '',
                'date_limite' => $_POST['date_limite'] ?? '',
                'id_statut'   => (int) ($_POST['id_statut'] ?? 1),
                'id_priorite' => (int) ($_POST['id_priorite'] ?? 2),
                'assign_to'   => $_POST['assign_to'] ?? '',
            ];

            if (!$isChef) {
                // Collaborateur : soumet une demande
                $dv = new DemandeValidation();
                if ($dv->demandeExiste($id, 'modif_action', 'id_action', $idAction)) {
                    setFlash('error', 'Une demande de modification est déjà en attente pour cette action.');
                    redirect('dossiers/show/' . $id);
                }
                $dv->soumettre($id, $_SESSION['user']['id'], 'modif_action', $payload);
                (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A soumis une demande de modification pour l\'action : ' . $nom);
                setFlash('success', 'Votre demande de modification a été soumise au responsable.');
            } else {
                // Chef / Admin : modification directe
                $ok = $actionModel->updateFull($idAction, $payload);
                if ($ok) {
                    (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A modifié l\'action : ' . $nom);
                    setFlash('success', 'Action mise à jour avec succès.');
                } else {
                    setFlash('error', 'Erreur lors de la mise à jour de l\'action.');
                }
            }
        } else {
            // --- Changement de statut rapide (select) : direct pour tous ---
            $idStatut = (int)($_POST['id_statut'] ?? 0);
            if ($idStatut > 0) {
                $actionModel->updateStatut($idAction, $idStatut);
                (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A mis à jour le statut d\'une action');
                setFlash('success', 'Statut de l\'action mis à jour.');
            } else {
                setFlash('error', 'Données invalides.');
            }
        }

        redirect('dossiers/show/' . $id);
    }

    public function deleteAction(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        $idAction = (int)($_POST['id_action'] ?? 0);

        if ($idAction > 0) {
            $actionModel = new Action();
            $actionModel->delete($idAction);
            (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A supprimé une action');
            setFlash('success', 'Action supprimée.');
        } else {
            setFlash('error', 'Action invalide.');
        }

        redirect('dossiers/show/' . $id);
    }

    /**
     * Soumet une demande de changement de workflow (pour les Collaborateurs).
     * Les Responsables/Admin font le changement directement via edit().
     */
    public function demandeWorkflow(int $id): void
    {
        requireAuth();
        $dossier = $this->findOrFail($id);
        $this->requireNotSigned($dossier);

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $isChef  = $_SESSION['user']['role'] === 'Responsable de dossier' || $isAdmin;

        // Les chefs modifient directement via edit()
        if ($isChef) {
            setFlash('error', 'Utilisez la modification du dossier pour changer le workflow.');
            redirect('dossiers/show/' . $id);
        }

        $idWorkflow = (int)($_POST['id_workflow'] ?? 0);
        if ($idWorkflow <= 0) {
            setFlash('error', 'Workflow invalide.');
            redirect('dossiers/show/' . $id);
        }

        $dv = new DemandeValidation();
        if ($dv->demandeExiste($id, 'changement_workflow', 'id_workflow', $idWorkflow)) {
            setFlash('error', 'Une demande pour ce workflow est déjà en attente.');
            redirect('dossiers/show/' . $id);
        }

        // Récupérer le libellé du workflow demandé
        $allWorkflows = (new Workflow())->getAllStatuts();
        $libelle = '';
        foreach ($allWorkflows as $wf) {
            if ((int)$wf['id_workflow'] === $idWorkflow) {
                $libelle = $wf['libelle'];
                break;
            }
        }

        $dv->soumettre($id, $_SESSION['user']['id'], 'changement_workflow', [
            'id_workflow'      => $idWorkflow,
            'workflow_libelle' => $libelle,
        ]);
        (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A demandé un changement de workflow vers : ' . $libelle);
        setFlash('success', 'Votre demande de changement de workflow a été soumise au responsable.');
        redirect('dossiers/show/' . $id);
    }

    // =========================================================
    // VALIDATION DES DEMANDES
    // =========================================================

    /**
     * Approuve une demande en attente et exécute l'action associée.
     * Accessible uniquement au créateur du dossier ou à un Administrateur.
     */
    public function approuverDemande(int $idDossier): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($idDossier);
        $this->requireNotSigned($dossier);

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if (!$isAdmin && (int)$dossier['cree_par'] !== (int)$_SESSION['user']['id']) {
            setFlash('error', 'Accès refusé.');
            redirect('dossiers/show/' . $idDossier);
        }

        $idDemande = (int)($_POST['id_demande'] ?? 0);
        $dv = new DemandeValidation();
        $demande = $dv->findById($idDemande);

        if (!$demande || (int)$demande['id_dossier'] !== $idDossier) {
            setFlash('error', 'Demande introuvable.');
            redirect('dossiers/show/' . $idDossier);
        }

        $payload = $demande['payload'];
        $log = new ActivityLog();

        switch ($demande['type_demande']) {

            case 'upload_fichier':
                // Déplacer le fichier de pending/ vers uploads/
                $cheminTmp = __DIR__ . '/../public' . $payload['chemin_tmp'];
                $nom = basename($payload['nom']);
                $dest = __DIR__ . '/../public/uploads/' . time() . '_' . $nom;
                if (file_exists($cheminTmp) && rename($cheminTmp, $dest)) {
                    $fichierModel = new Fichier();
                    $idFichier = $fichierModel->uploadFichier([
                        'nom'    => $payload['nom'],
                        'chemin' => str_replace(__DIR__ . '/../public', '', $dest),
                        'taille' => $payload['taille'],
                    ], (int)$demande['id_demandeur']);
                    if ($idFichier) {
                        $fichierModel->linkToDossier($idFichier, $idDossier);
                    }
                    $log->log($idDossier, $_SESSION['user']['id'], 'A approuvé l\'upload de : ' . $payload['nom']);
                } else {
                    setFlash('error', 'Fichier temporaire introuvable. La demande sera rejetée.');
                    $dv->resoudre($idDemande, $_SESSION['user']['id'], 'rejetee', 'Fichier temporaire introuvable.');
                    redirect('dossiers/show/' . $idDossier);
                }
                break;

            case 'modif_action':
                $actionModel = new Action();
                $actionModel->updateFull((int)$payload['id_action'], $payload);
                $log->log($idDossier, $_SESSION['user']['id'], 'A approuvé la modification de l\'action : ' . $payload['nom']);
                break;

            case 'changement_workflow':
                $nouvelleEtape = (int)($payload['id_workflow'] ?? 1);
                $this->dossierModel->update($idDossier, [
                    'nom'         => $dossier['nom'],
                    'description' => $dossier['description'] ?? '',
                    'date_debut'  => $dossier['date_debut']  ?? '',
                    'date_fin'    => $dossier['date_fin']    ?? '',
                    'date_limite' => $dossier['date_limite'] ?? '',
                    'id_statut'   => (int)($dossier['id_statut'] ?? 1),
                    'id_workflow' => $nouvelleEtape,
                    'droit_depot' => (int)($dossier['droit_depot'] ?? 1),
                ], $_SESSION['user']['id'], $isAdmin);
                $log->log($idDossier, $_SESSION['user']['id'], 'A approuvé le passage du workflow à : ' . ($payload['workflow_libelle'] ?? $nouvelleEtape));
                break;
        }

        $dv->resoudre($idDemande, $_SESSION['user']['id'], 'approuvee');
        setFlash('success', 'Demande approuvée et appliquée.');
        redirect('dossiers/show/' . $idDossier);
    }

    /**
     * Rejette une demande en attente.
     */
    public function rejeterDemande(int $idDossier): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier = $this->findOrFail($idDossier);

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if (!$isAdmin && (int)$dossier['cree_par'] !== (int)$_SESSION['user']['id']) {
            setFlash('error', 'Accès refusé.');
            redirect('dossiers/show/' . $idDossier);
        }

        $idDemande   = (int)($_POST['id_demande'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        $dv = new DemandeValidation();
        $demande = $dv->findById($idDemande);

        if (!$demande || (int)$demande['id_dossier'] !== $idDossier) {
            setFlash('error', 'Demande introuvable.');
            redirect('dossiers/show/' . $idDossier);
        }

        // Supprimer le fichier temporaire si c'est un upload rejeté
        if ($demande['type_demande'] === 'upload_fichier') {
            $cheminTmp = __DIR__ . '/../public' . ($demande['payload']['chemin_tmp'] ?? '');
            if (file_exists($cheminTmp)) {
                unlink($cheminTmp);
            }
        }

        $dv->resoudre($idDemande, $_SESSION['user']['id'], 'rejetee', $commentaire);
        (new ActivityLog())->log($idDossier, $_SESSION['user']['id'], 'A rejeté une demande de validation.');
        setFlash('success', 'Demande rejetée.');
        redirect('dossiers/show/' . $idDossier);
    }
}
