<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>E-Kasa</title>
<link rel="stylesheet" href="/public/style.css">
</head>
<body>

<nav>
  <h2>E-Kasa</h2>

  <?php if(isset($_SESSION['user_id'])): ?>
    <span><?= $_SESSION['nom'] ?> (<?= $_SESSION['role'] ?>)</span>
    <a href="/auth/logout.php">Logout</a>
  <?php else: ?>
    <a href="/auth/login.php">Login</a>
    <a href="/auth/register.php">Register</a>
  <?php endif; ?>
</nav>

<div class="container">
