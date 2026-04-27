<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = getCurrentUser();

if ($user['role'] === 'chef_projet') {
    redirect('dashboard_chef.php');
} else {
    redirect('dashboard_membre.php');
}
