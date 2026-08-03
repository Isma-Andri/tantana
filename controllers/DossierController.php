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
        ]);

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
        $actions = (new Action())->getByDossier($id);
        $user    = $_SESSION['user'];
        require __DIR__ . '/../views/dossiers/show.php';
    }

    public function edit(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $dossier  = $this->findOrFail($id);
        $statuts = $this->dossierModel->getStatuts();
        $workflows = (new Workflow())->getAllStatuts();

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if ((int) $dossier['cree_par'] !== $_SESSION['user']['id'] && !$isAdmin) {
            setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce dossier.');
            redirect('dossiers');
        }

        require __DIR__ . '/../views/dossiers/edit.php';
    }

    public function update(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');

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
        ], $_SESSION['user']['id'], $isAdmin);

        if (!$ok) {
            setFlash('error', 'Modification impossible.');
            redirect('dossiers');
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
        return $dossier;
    }

    public function upload(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $this->findOrFail($id); // verify exists

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

    public function share(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $this->findOrFail($id);
        
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
        require __DIR__ . '/../views/dossiers/pdf.php';
    }

    public function comment(int $id): void
    {
        requireAuth();
        $this->findOrFail($id); // verify exists

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
        $this->findOrFail($id);

        $nom = trim($_POST['nom'] ?? '');
        if (empty($nom)) {
            setFlash('error', 'Le nom de l\'action est obligatoire.');
            redirect('dossiers/show/' . $id);
        }

        $_POST['id_dossier'] = $id;
        $actionModel = new Action();
        $idTache = $actionModel->create($_POST);

        if (!empty($_POST['assign_to'])) {
            $actionModel->assign($idTache, (int)$_POST['assign_to']);
        }

        (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A créé une nouvelle action: ' . $nom);

        setFlash('success', 'Action ajoutée.');
        redirect('dossiers/show/' . $id);
    }

    public function updateAction(int $id): void
    {
        requireAuth();
        $this->findOrFail($id);

        $idTache = (int)($_POST['id_tache'] ?? 0);
        $idStatut = (int)($_POST['id_statut'] ?? 0);

        if ($idTache > 0 && $idStatut > 0) {
            (new Action())->updateStatut($idTache, $idStatut);
            (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A mis à jour le statut d\'une action');
            setFlash('success', 'Statut de l\'action mis à jour.');
        } else {
            setFlash('error', 'Données invalides.');
        }

        redirect('dossiers/show/' . $id);
    }
}
