<?php
// controllers/AdminController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Dossier.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class AdminController
{
    private User $userModel;

    public function __construct()
    {
        requireRole('Administrateur');
        $this->userModel = new User();
    }

    public function index(): void
    {
        $stats        = $this->userModel->getSystemStats();
        $users        = $this->userModel->getAllUsers();
        $roles        = $this->userModel->getRoles();
        $allLogs      = (new ActivityLog())->getAllLogs(30);
        $currentUser  = $_SESSION['user'];

        require __DIR__ . '/../views/admin/index.php';
    }

    public function updateRole(): void
    {
        $idUser = (int) ($_POST['id_user'] ?? 0);
        $idRole = (int) ($_POST['id_role'] ?? 0);

        if ($idUser > 0 && $idRole > 0) {
            $this->userModel->updateRole($idUser, $idRole);
            setFlash('success', 'Rôle utilisateur mis à jour.');
        } else {
            setFlash('error', 'Données invalides.');
        }

        redirect('admin');
    }

    public function deleteUser(): void
    {
        $idUser = (int) ($_POST['id_user'] ?? 0);

        if ($idUser === $_SESSION['user']['id']) {
            setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte administrateur.');
            redirect('admin');
        }

        if ($idUser > 0) {
            $this->userModel->deleteUser($idUser);
            setFlash('success', 'Utilisateur supprimé.');
        } else {
            setFlash('error', 'Données invalides.');
        }

        redirect('admin');
    }
}
