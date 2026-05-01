<?php
require_once __DIR__ . '/../../backend/helpers/session.php';
require_once __DIR__ . '/../../backend/helpers/validators.php';

// On capture toute la sortie pour mesurer son poids dans le footer
ob_start();

$u = current_user();
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="LocalMarché - circuits courts entre producteurs et consommateurs locaux. Site éco-conçu, sobre et rapide.">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — LocalMarché' : 'LocalMarché' ?></title>
<link rel="stylesheet" href="/css/style.css">
<script src="/js/main.js" defer></script>
</head>
<body>
<header class="site">
  <div class="container bar">
    <a href="/index.php" class="logo">LocalMarché</a>
    <nav aria-label="Navigation principale">
      <a href="/index.php" <?= $current === 'index.php' ? 'class="active"' : '' ?>>Accueil</a>
      <a href="/producers.php" <?= $current === 'producers.php' ? 'class="active"' : '' ?>>Producteurs</a>
      <a href="/products.php" <?= $current === 'products.php' ? 'class="active"' : '' ?>>Produits</a>
      <?php if ($u): ?>
        <?php if ($u['role'] === 'producteur'): ?>
          <a href="/dashboard.php">Mon espace</a>
        <?php elseif ($u['role'] === 'admin'): ?>
          <a href="/admin/users.php">Admin</a>
        <?php else: ?>
          <a href="/account.php">Mon compte</a>
          <a href="/orders.php">Mes commandes</a>
        <?php endif; ?>
        <a href="/logout.php">Déconnexion</a>
      <?php else: ?>
        <a href="/login.php">Connexion</a>
        <a href="/register.php">Inscription</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main>
  <div class="container">
