<?php
// controllers/AuthController.php
// Gestion de l'authentification : inscription, connexion, déconnexion

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ----------------------------------------------------------------
    // Afficher le formulaire de connexion
    // ----------------------------------------------------------------
    public function showLogin(): void
    {
        if ($this->isLoggedIn()) {
            redirect('projets');
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    // ----------------------------------------------------------------
    // Traiter la soumission du formulaire de connexion
    // ----------------------------------------------------------------
    public function handleLogin(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            setFlash('error', 'Veuillez remplir tous les champs.');
            redirect('login');
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            setFlash('error', 'Email ou mot de passe incorrect.');
            redirect('login');
        }

        // Démarrer la session sécurisée
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'     => $user['id_user'],
            'nom'    => $user['nom'],
            'prenom' => $user['prenom'],
            'email'  => $user['email'],
            'role'   => $user['role_libelle'],
        ];

        setFlash('success', 'Bienvenue, ' . htmlspecialchars($user['prenom']) . ' !');
        redirect('projets');
    }

    // ----------------------------------------------------------------
    // Afficher le formulaire d'inscription
    // ----------------------------------------------------------------
    public function showRegister(): void
    {
        if ($this->isLoggedIn()) {
            redirect('projets');
        }
        $roles = $this->userModel->getRoles();
        require __DIR__ . '/../views/auth/register.php';
    }

    // ----------------------------------------------------------------
    // Traiter l'inscription
    // ----------------------------------------------------------------
    public function handleRegister(): void
    {
        $nom      = trim($_POST['nom']      ?? '');
        $prenom   = trim($_POST['prenom']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $id_role  = (int) ($_POST['id_role'] ?? 1);

        // --- Validations ---
        $errors = [];

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $errors[] = 'Tous les champs sont obligatoires.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!in_array($id_role, [1, 2], true)) {
            $errors[] = 'Rôle invalide.';
        }

        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('register');
        }

        // --- Création du compte ---
        $result = $this->userModel->create($nom, $prenom, $email, $password, $id_role);

        if ($result === false) {
            setFlash('error', 'Cet email est déjà utilisé. Veuillez vous connecter.');
            redirect('register');
        }

        setFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
        redirect('login');
    }

    // ----------------------------------------------------------------
    // Déconnexion
    // ----------------------------------------------------------------
    public function logout(): void
    {
        session_unset();
        session_destroy();
        setFlash('success', 'Vous avez été déconnecté.');
        redirect('login');
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }
}
