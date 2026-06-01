<?php
// controllers/ProjetController.php

require_once __DIR__ . '/../models/Projet.php';

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
        requireRole('Chef de projet');
        $statuts = $this->projetModel->getStatuts();
        require __DIR__ . '/../views/projets/create.php';
    }

    public function store(): void
    {
        requireRole('Chef de projet');

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
        $user    = $_SESSION['user'];
        require __DIR__ . '/../views/projets/show.php';
    }

    public function edit(int $id): void
    {
        requireRole('Chef de projet');
        $projet  = $this->findOrFail($id);
        $statuts = $this->projetModel->getStatuts();

        if ((int) $projet['cree_par'] !== $_SESSION['user']['id']) {
            setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce projet.');
            redirect('projets');
        }

        require __DIR__ . '/../views/projets/edit.php';
    }

    public function update(int $id): void
    {
        requireRole('Chef de projet');

        if (empty(trim($_POST['nom'] ?? ''))) {
            setFlash('error', 'Le nom du projet est obligatoire.');
            redirect('projets/edit/' . $id);
        }

        $ok = $this->projetModel->update($id, [
            'nom'         => $_POST['nom'],
            'description' => $_POST['description'] ?? '',
            'date_debut'  => $_POST['date_debut']  ?? '',
            'date_fin'    => $_POST['date_fin']    ?? '',
            'date_limite' => $_POST['date_limite'] ?? '',
            'id_statut'   => (int) ($_POST['id_statut'] ?? 1),
        ], $_SESSION['user']['id']);

        if (!$ok) {
            setFlash('error', 'Modification impossible.');
            redirect('projets');
        }

        setFlash('success', 'Projet mis à jour avec succès !');
        redirect('projets/show/' . $id);
    }

    public function delete(int $id): void
    {
        requireRole('Chef de projet');

        $ok = $this->projetModel->delete($id, $_SESSION['user']['id']);
        setFlash($ok ? 'success' : 'error', $ok ? 'Projet supprimé.' : 'Suppression impossible.');
        redirect('projets');
    }

    private function findOrFail(int $id): array
    {
        $projet = $this->projetModel->findById($id);
        if (!$projet) {
            setFlash('error', 'Projet introuvable.');
            redirect('projets');
        }
        return $projet;
    }
}
