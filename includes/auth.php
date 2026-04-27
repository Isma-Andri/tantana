<?php
require_once __DIR__ . '/../config/database.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id'], $_SESSION['user_role']);
}

function baseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = $_SERVER['SCRIPT_NAME'] ?? '';
    $base     = rtrim(dirname(dirname($script)), '/');
    return $protocol . '://' . $host . $base;
}

function redirect(string $page): void {
    $url = baseUrl() . '/' . ltrim($page, '/');
    header('Location: ' . $url);
    exit;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireRole(string $role): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        redirect('dashboard.php');
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
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email et mot de passe requis.'];
    }
    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare("
            SELECT u.*, r.libelle AS role_libelle
            FROM users u
            JOIN roles r ON u.id_role = r.id_role
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('[Tantana][loginUser] ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur base de donnees : ' . $e->getMessage()];
    }

    if (!$user) {
        return ['success' => false, 'message' => 'Aucun compte associe a cet email.'];
    }
    if (!password_verify($password, $user['mot_de_passe'])) {
        return ['success' => false, 'message' => 'Mot de passe incorrect.'];
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
    if (empty($nom) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Tous les champs sont requis.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => "Format d'email invalide."];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caracteres.'];
    }
    try {
        $pdo       = getDB();
        $check     = $pdo->prepare("SELECT id_user FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Cette adresse email est deja utilisee.'];
        }
        $roleCheck = $pdo->prepare("SELECT id_role FROM roles WHERE id_role = ? LIMIT 1");
        $roleCheck->execute([$id_role]);
        if (!$roleCheck->fetch()) {
            return ['success' => false, 'message' => 'Role invalide selectionne.'];
        }
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, id_role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $hash, $id_role]);
        return ['success' => true];
    } catch (PDOException $e) {
        error_log('[Tantana][registerUser] ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur creation compte : ' . $e->getMessage()];
    }
}

function getRoles(): array {
    try {
        return getDB()->query("SELECT * FROM roles ORDER BY id_role")->fetchAll();
    } catch (PDOException $e) {
        error_log('[Tantana][getRoles] ' . $e->getMessage());
        return [];
    }
}

function logoutUser(): void {
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    redirect('login.php');
}
