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

        $user    = $_SESSION['user'];
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
        $dossier = $this->findOrFail($id); // verify exists and hasAccess
        $this->requireNotSigned($dossier);

        // Check upload permissions for collaborators
        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $isChef = $_SESSION['user']['role'] === 'Responsable de dossier' || $isAdmin;
        if (!$isChef && (int)$dossier['droit_depot'] === 0) {
            setFlash('error', 'Le dépôt de fichiers a été désactivé par le responsable pour les collaborateurs.');
            redirect('dossiers/show/' . $id);
        }

        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['fichier']['tmp_name'];
            $name = basename($_FILES['fichier']['name']);
            $size = $_FILES['fichier']['size'];
            
            $uploadDir = __DIR__ . '/../public/uploads/';
            $destPath = $uploadDir . time() . '_' . $name;
            
            if (move_uploaded_file($tmpName, $destPath)) {
                $fichierModel = new Fichier();
                $idFichier = $fichierModel->uploadFichier([
                    'nom' => $name,
                    'chemin' => str_replace(__DIR__ . '/../public', '', $destPath),
                    'taille' => $size
                ], $_SESSION['user']['id']);
                
                if ($idFichier) {
                    $fichierModel->linkToDossier($idFichier, $id);
                    (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A ajouté une pièce jointe: ' . $name);
                    setFlash('success', 'Pièce jointe ajoutée.');
                }
            } else {
                setFlash('error', 'Erreur lors du déplacement du fichier.');
            }
        } else {
            setFlash('error', 'Erreur de téléchargement du fichier.');
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

        $actionModel = new Action();

        if (isset($_POST['nom'])) {
            $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
            $isChef = $_SESSION['user']['role'] === 'Responsable de dossier' || $isAdmin;
            if (!$isChef) {
                setFlash('error', 'Accès refusé.');
                redirect('dossiers/show/' . $id);
            }

            $nom = trim($_POST['nom']);
            if (empty($nom)) {
                setFlash('error', 'Le nom de l\'action est obligatoire.');
                redirect('dossiers/show/' . $id);
            }

            $ok = $actionModel->updateFull($idAction, [
                'nom'         => $nom,
                'description' => $_POST['description'] ?? '',
                'date_debut'  => $_POST['date_debut'] ?? '',
                'date_fin'    => $_POST['date_fin'] ?? '',
                'date_limite' => $_POST['date_limite'] ?? '',
                'id_statut'   => (int) ($_POST['id_statut'] ?? 1),
                'id_priorite' => (int) ($_POST['id_priorite'] ?? 2),
                'assign_to'   => $_POST['assign_to'] ?? '',
            ]);

            if ($ok) {
                (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A modifié l\'action: ' . $nom);
                setFlash('success', 'Action mise à jour avec succès.');
            } else {
                setFlash('error', 'Erreur lors de la mise à jour de l\'action.');
            }
        } else {
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
}
