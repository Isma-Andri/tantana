<?php
require_once __DIR__ . '/../config/database.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: dashboard.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'nom'   => $_SESSION['user_nom'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

function loginUser(string $email, string $password): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT u.*, r.libelle AS role_libelle
        FROM users u
        JOIN roles r ON u.id_role = r.id_role
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
    }

    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id_user'];
    $_SESSION['user_nom']   = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role_libelle'];

    return ['success' => true, 'role' => $user['role_libelle']];
}

function registerUser(string $nom, string $email, string $password, int $id_role = 2): array {
    $pdo = getDB();

    $check = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, id_role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $email, $hash, $id_role]);

    return ['success' => true];
}

function getRoles(): array {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM roles ORDER BY id_role")->fetchAll();
}

function logoutUser(): void {
    startSession();
    session_destroy();
    header('Location: login.php');
    exit;
}
