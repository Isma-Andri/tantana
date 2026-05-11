<?php
// controllers/ProjetController.php
// Logique métier CRUD pour les projets

require_once __DIR__ . '/../models/Projet.php';

class ProjetController
{
    private Projet $projetModel;

    public function __construct()
    {
        requireAuth(); // Protège toutes les actions
        $this->projetModel = new Projet();
    }

    // ----------------------------------------------------------------
    // Dashboard — liste des projets
    // ----------------------------------------------------------------
    public function index(): void
    {
        $user    = $_SESSION['user'];
        $projets = $this->projetModel->getAllForUser($user['id'], $user['role']);

        require __DIR__ . '/../views/projets/index.php';
    }

    // ----------------------------------------------------------------
    // Formulaire de création
    // ----------------------------------------------------------------
    public function create(): void
    {
        requireRole('Chef de projet');
        $statuts = $this->projetModel->getStatuts();
        require __DIR__ . '/../views/projets/create.php';
    }

    // ----------------------------------------------------------------
    // Traiter la création
    // ----------------------------------------------------------------
    public function store(): void
    {
        requireRole('Chef de projet');

        $data = [
            'nom'         => $_POST['nom']         ?? '',
            'description' => $_POST['description'] ?? '',
            'date_debut'  => $_POST['date_debut']  ?? '',
            'date_fin'    => $_POST['date_fin']    ?? '',
            'date_limite' => $_POST['date_limite'] ?? '',
            'cree_par'    => $_SESSION['user']['id'],
        ];

        if (empty(trim($data['nom']))) {
            setFlash('error', 'Le nom du projet est obligatoire.');
            redirect('projets/create');
        }

        $id = $this->projetModel->create($data);
        setFlash('success', 'Projet créé avec succès !');
        redirect('projets/show/' . $id);
    }

    // ----------------------------------------------------------------
    // Détail d'un projet
    // ----------------------------------------------------------------
    public function show(int $id): void
    {
        $projet  = $this->getProjetOrFail($id);
        $membres = $this->projetModel->getMembers($id);
        $user    = $_SESSION['user'];

        require __DIR__ . '/../views/projets/show.php';
    }

    // ----------------------------------------------------------------
    // Formulaire de modification
    // ----------------------------------------------------------------
    public function edit(int $id): void
    {
        requireRole('Chef de projet');
        $projet  = $this->getProjetOrFail($id);
        $statuts = $this->projetModel->getStatuts();

        // Seul le créateur peut modifier
        if ($projet['cree_par'] !== $_SESSION['user']['id']) {
            setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce projet.');
            redirect('projets');
        }

        require __DIR__ . '/../views/projets/edit.php';
    }

    // ----------------------------------------------------------------
    // Traiter la modification
    // ----------------------------------------------------------------
    public function update(int $id): void
    {
        requireRole('Chef de projet');

        $data = [
            'nom'         => $_POST['nom']         ?? '',
            'description' => $_POST['description'] ?? '',
            'date_debut'  => $_POST['date_debut']  ?? '',
            'date_fin'    => $_POST['date_fin']    ?? '',
            'date_limite' => $_POST['date_limite'] ?? '',
            'id_statut'   => (int) ($_POST['id_statut'] ?? 1),
        ];

        if (empty(trim($data['nom']))) {
            setFlash('error', 'Le nom du projet est obligatoire.');
            redirect('projets/edit/' . $id);
        }

        $ok = $this->projetModel->update($id, $data, $_SESSION['user']['id']);

        if (!$ok) {
            setFlash('error', 'Modification impossible. Vous n\'êtes peut-être pas le créateur.');
            redirect('projets');
        }

        setFlash('success', 'Projet mis à jour avec succès !');
        redirect('projets/show/' . $id);
    }

    // ----------------------------------------------------------------
    // Supprimer un projet
    // ----------------------------------------------------------------
    public function delete(int $id): void
    {
        requireRole('Chef de projet');

        $ok = $this->projetModel->delete($id, $_SESSION['user']['id']);

        if (!$ok) {
            setFlash('error', 'Suppression impossible. Vous n\'êtes peut-être pas le créateur.');
        } else {
            setFlash('success', 'Projet supprimé.');
        }

        redirect('projets');
    }

    // ----------------------------------------------------------------
    // Helper : récupère un projet ou redirige avec erreur
    // ----------------------------------------------------------------
    private function getProjetOrFail(int $id): array
    {
        $projet = $this->projetModel->findById($id);
        if (!$projet) {
            setFlash('error', 'Projet introuvable.');
            redirect('projets');
        }
        return $projet;
    }
}
