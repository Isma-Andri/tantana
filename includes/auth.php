<?php
session_start();

function isLogged() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLogged()) {
        header("Location: /auth/login.php");
        exit();
    }
}

function requireRole($role) {
    if (!isLogged() || $_SESSION['role'] !== $role) {
        header("Location: /auth/login.php");
        exit();
    }
}
