<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = getCurrentUser();

if ($user['role'] === 'chef_projet') {
    header('Location: dashboard_chef.php');
} else {
    header('Location: dashboard_membre.php');
}
exit;
