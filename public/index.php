<?php
// public/index.php

declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/DbSessionsHandler.php';

$handler = new DbSessionsHandler(getPDO());
session_set_save_handler($handler, true);

session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();


// --- Helpers globaux ---

function redirect(string $path): never
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    header('Location: ' . $base . '/' . ltrim($path, '/'));
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function requireAuth(): void
{
    if (empty($_SESSION['user'])) {
        setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
        redirect('login');
    }
}

function requireRole(string $role): void
{
    requireAuth();
    if ($_SESSION['user']['role'] !== $role) {
        setFlash('error', 'Accès refusé.');
        redirect('projets');
    }
}

function e(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// --- Routage ---

$base     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path     = ltrim(substr($uri, strlen($base)), '/');
$method   = $_SERVER['REQUEST_METHOD'];
$segments = array_values(array_filter(explode('/', $path)));

$seg0 = $segments[0] ?? '';
$seg1 = $segments[1] ?? '';
$id   = isset($segments[2]) ? (int) $segments[2] : 0;

// Landing page
if ($seg0 === '') {
    if (!empty($_SESSION['user'])) redirect('projets');
    require __DIR__ . '/../views/home.php';
    exit;
}

// Auth
if (in_array($seg0, ['login', 'register', 'logout'], true)) {
    require_once __DIR__ . '/../controllers/AuthController.php';
    $ctrl = new AuthController();

    match ($seg0) {
        'login'    => $method === 'POST' ? $ctrl->handleLogin()    : $ctrl->showLogin(),
        'register' => $method === 'POST' ? $ctrl->handleRegister() : $ctrl->showRegister(),
        'logout'   => $ctrl->logout(),
    };
    exit;
}

// Projets
if ($seg0 === 'projets') {
    require_once __DIR__ . '/../controllers/ProjetController.php';
    $ctrl = new ProjetController();

    match (true) {
        $seg1 === '' && $method === 'GET'                   => $ctrl->index(),
        $seg1 === 'create' && $method === 'GET'             => $ctrl->create(),
        $seg1 === 'create' && $method === 'POST'            => $ctrl->store(),
        $seg1 === 'show'   && $id > 0 && $method === 'GET'  => $ctrl->show($id),
        $seg1 === 'edit'   && $id > 0 && $method === 'GET'  => $ctrl->edit($id),
        $seg1 === 'edit'   && $id > 0 && $method === 'POST' => $ctrl->update($id),
        $seg1 === 'delete' && $id > 0 && $method === 'POST' => $ctrl->delete($id),
        default                                             => $ctrl->index(),
    };
    exit;
}

// 404
http_response_code(404);
require __DIR__ . '/../views/partials/404.php';
