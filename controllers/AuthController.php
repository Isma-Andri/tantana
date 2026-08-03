<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['user'])) redirect('dossiers');
        require __DIR__ . '/../views/auth/login.php';
    }

    public function handleLogin(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            setFlash('error', 'Veuillez remplir tous les champs.');
            redirect('login');
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            setFlash('error', 'Email ou mot de passe incorrect.');
            redirect('login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'     => $user['id_user'],
            'nom'    => $user['nom'],
            'prenom' => $user['prenom'],
            'email'  => $user['email'],
            'role'   => $user['role_libelle'],
        ];

        setFlash('success', 'Bienvenue, ' . htmlspecialchars($user['prenom']) . ' !');
        redirect('dossiers');
    }

    public function showRegister(): void
    {
        if (!empty($_SESSION['user'])) redirect('dossiers');
        $roles = $this->userModel->getRoles();
        require __DIR__ . '/../views/auth/register.php';
    }

    public function handleRegister(): void
    {
        $nom      = trim($_POST['nom']      ?? '');
        $prenom   = trim($_POST['prenom']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $id_role  = (int) ($_POST['id_role'] ?? 1);

        $errors = [];
        if (!$nom || !$prenom || !$email || !$password)  $errors[] = 'Tous les champs sont obligatoires.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'Adresse email invalide.';
        if (strlen($password) < 8)                        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        if ($password !== $confirm)                       $errors[] = 'Les mots de passe ne correspondent pas.';
        if (!in_array($id_role, [1, 2], true))            $errors[] = 'Rôle invalide.';

        if ($errors) {
            setFlash('error', implode('<br>', $errors));
            redirect('register');
        }

        if ($this->userModel->create($nom, $prenom, $email, $password, $id_role) === false) {
            setFlash('error', 'Cet email est déjà utilisé. Veuillez vous connecter.');
            redirect('register');
        }

        setFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
        redirect('login');
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        setFlash('success', 'Vous avez été déconnecté.');
        redirect('login');
    }
}
