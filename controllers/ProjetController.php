<?php
// controllers/ProjetController.php

require_once __DIR__ . '/../models/Projet.php';
require_once __DIR__ . '/../models/Workflow.php';
require_once __DIR__ . '/../models/PartageDossier.php';
require_once __DIR__ . '/../models/Fichier.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Action.php';

class ProjetController
{
    private Projet $projetModel;

    public function __construct()
    {
        requireAuth();
        $this->projetModel = new Projet();
    }

    public function index(): void
    {
        $user    = $_SESSION['user'];
        $projets = $this->projetModel->getAllForUser($user['id'], $user['role']);
        require __DIR__ . '/../views/projets/index.php';
    }

    public function create(): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $statuts = $this->projetModel->getStatuts();
        require __DIR__ . '/../views/projets/create.php';
    }

    public function store(): void
    {
        requireRole('Responsable de dossier', 'Administrateur');

        if (empty(trim($_POST['nom'] ?? ''))) {
            setFlash('error', 'Le nom du projet est obligatoire.');
            redirect('projets/create');
        }

        $id = $this->projetModel->create([
            'nom'         => $_POST['nom'],
            'description' => $_POST['description'] ?? '',
            'date_debut'  => $_POST['date_debut']  ?? '',
            'date_fin'    => $_POST['date_fin']    ?? '',
            'date_limite' => $_POST['date_limite'] ?? '',
            'cree_par'    => $_SESSION['user']['id'],
        ]);

        setFlash('success', 'Projet créé avec succès !');
        redirect('projets/show/' . $id);
    }

    public function show(int $id): void
    {
        $projet  = $this->findOrFail($id);
        $membres = $this->projetModel->getMembers($id);
        $partages = (new PartageDossier())->getPartagesByDossier($id);
        $fichiers = (new Fichier())->getFichiersByDossier($id);
        $commentaires = (new Commentaire())->getByDossier($id);
        $activityLogs = (new ActivityLog())->getByDossier($id);
        $actions = (new Action())->getByDossier($id);
        $user    = $_SESSION['user'];
        require __DIR__ . '/../views/projets/show.php';
    }

    public function edit(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $projet  = $this->findOrFail($id);
        $statuts = $this->projetModel->getStatuts();
        $workflows = (new Workflow())->getAllStatuts();

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        if ((int) $projet['cree_par'] !== $_SESSION['user']['id'] && !$isAdmin) {
            setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce dossier.');
            redirect('projets');
        }

        require __DIR__ . '/../views/projets/edit.php';
    }

    public function update(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');

        if (empty(trim($_POST['nom'] ?? ''))) {
            setFlash('error', 'Le nom du projet est obligatoire.');
            redirect('projets/edit/' . $id);
        }

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $ok = $this->projetModel->update($id, [
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
            redirect('projets');
        }

        setFlash('success', 'Projet mis à jour avec succès !');
        redirect('projets/show/' . $id);
    }

    public function delete(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');

        $isAdmin = $_SESSION['user']['role'] === 'Administrateur';
        $ok = $this->projetModel->delete($id, $_SESSION['user']['id'], $isAdmin);
        setFlash($ok ? 'success' : 'error', $ok ? 'Projet supprimé.' : 'Suppression impossible.');
        redirect('projets');
    }

    private function findOrFail(int $id): array
    {
        $projet = $this->projetModel->findById($id);
        if (!$projet) {
            setFlash('error', 'Dossier introuvable.');
            redirect('projets');
        }
        return $projet;
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
        redirect('projets/show/' . $id);
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
        redirect('projets/show/' . $id);
    }

    public function exportPdf(int $id): void
    {
        $projet = $this->findOrFail($id);
        $membres = $this->projetModel->getMembers($id);
        $fichiers = (new Fichier())->getFichiersByDossier($id);
        require __DIR__ . '/../views/projets/pdf.php';
    }

    public function comment(int $id): void
    {
        requireAuth();
        $this->findOrFail($id); // verify exists

        $contenu = trim($_POST['contenu'] ?? '');
        if (empty($contenu)) {
            setFlash('error', 'Le commentaire ne peut pas être vide.');
            redirect('projets/show/' . $id);
        }

        (new Commentaire())->add($id, $_SESSION['user']['id'], $contenu);
        (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A ajouté un commentaire');

        setFlash('success', 'Commentaire ajouté.');
        redirect('projets/show/' . $id);
    }

    public function addAction(int $id): void
    {
        requireRole('Responsable de dossier', 'Administrateur');
        $this->findOrFail($id);

        $nom = trim($_POST['nom'] ?? '');
        if (empty($nom)) {
            setFlash('error', 'Le nom de l\'action est obligatoire.');
            redirect('projets/show/' . $id);
        }

        $_POST['id_projet'] = $id;
        $actionModel = new Action();
        $idTache = $actionModel->create($_POST);

        if (!empty($_POST['assign_to'])) {
            $actionModel->assign($idTache, (int)$_POST['assign_to']);
        }

        (new ActivityLog())->log($id, $_SESSION['user']['id'], 'A créé une nouvelle action: ' . $nom);

        setFlash('success', 'Action ajoutée.');
        redirect('projets/show/' . $id);
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

        redirect('projets/show/' . $id);
    }
}
