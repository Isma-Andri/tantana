<?php
// public/index.php
// Routeur principal de l'application Tantana
// Toutes les requêtes passent par ce fichier (via .htaccess)

declare(strict_types=1);

// ----------------------------------------------------------------
// 1. Session sécurisée
// ----------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false,          // Passer à true en HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ----------------------------------------------------------------
// 2. Chargement des dépendances
// ----------------------------------------------------------------
require_once __DIR__ . '/../config/database.php';

// ----------------------------------------------------------------
// 3. Fonctions helpers globales
// ----------------------------------------------------------------

/**
 * Redirige vers une URL relative à la racine de l'app.
 */
function redirect(string $path): never
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    header('Location: ' . $base . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Stocke un message flash en session.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Récupère et supprime le message flash.
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirige vers /login si l'utilisateur n'est pas connecté.
 */
function requireAuth(): void
{
    if (empty($_SESSION['user'])) {
        setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
        redirect('login');
    }
}

/**
 * Vérifie que l'utilisateur possède le rôle requis.
 */
function requireRole(string $role): void
{
    requireAuth();
    if ($_SESSION['user']['role'] !== $role) {
        setFlash('error', 'Accès refusé. Rôle insuffisant.');
        redirect('projets');
    }
}

/**
 * Échappe une chaîne pour l'affichage HTML.
 */
function e(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// ----------------------------------------------------------------
// 4. Routage
// ----------------------------------------------------------------

// Récupère le chemin depuis l'URL (ex: /projets/edit/3 → projets/edit/3)
$base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path   = ltrim(substr($uri, strlen($base)), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Découpe le chemin en segments
$segments = array_values(array_filter(explode('/', $path)));

$seg0 = $segments[0] ?? '';
$seg1 = $segments[1] ?? '';
$seg2 = isset($segments[2]) ? (int) $segments[2] : 0;

// ----------------------------------------------------------------
// Routes d'authentification
// ----------------------------------------------------------------
if ($seg0 === 'login' || $seg0 === '') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    $ctrl = new AuthController();
    if ($method === 'POST') {
        $ctrl->handleLogin();
    } else {
        $ctrl->showLogin();
    }
    exit;
}

if ($seg0 === 'register') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    $ctrl = new AuthController();
    if ($method === 'POST') {
        $ctrl->handleRegister();
    } else {
        $ctrl->showRegister();
    }
    exit;
}

if ($seg0 === 'logout') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->logout();
    exit;
}

// ----------------------------------------------------------------
// Routes des projets
// ----------------------------------------------------------------
if ($seg0 === 'projets') {
    require_once __DIR__ . '/../controllers/ProjetController.php';
    $ctrl = new ProjetController();

    switch (true) {
        // GET /projets
        case ($seg1 === '' && $method === 'GET'):
            $ctrl->index();
            break;

        // GET /projets/create
        case ($seg1 === 'create' && $method === 'GET'):
            $ctrl->create();
            break;

        // POST /projets/create
        case ($seg1 === 'create' && $method === 'POST'):
            $ctrl->store();
            break;

        // GET /projets/show/{id}
        case ($seg1 === 'show' && $seg2 > 0 && $method === 'GET'):
            $ctrl->show($seg2);
            break;

        // GET /projets/edit/{id}
        case ($seg1 === 'edit' && $seg2 > 0 && $method === 'GET'):
            $ctrl->edit($seg2);
            break;

        // POST /projets/edit/{id}
        case ($seg1 === 'edit' && $seg2 > 0 && $method === 'POST'):
            $ctrl->update($seg2);
            break;

        // POST /projets/delete/{id}
        case ($seg1 === 'delete' && $seg2 > 0 && $method === 'POST'):
            $ctrl->delete($seg2);
            break;

        default:
            $ctrl->index();
            break;
    }
    exit;
}

// ----------------------------------------------------------------
// 404 — Route inconnue
// ----------------------------------------------------------------
http_response_code(404);
require __DIR__ . '/../views/partials/404.php';
