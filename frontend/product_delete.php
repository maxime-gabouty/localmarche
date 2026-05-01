<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/Product.php';

require_login();
$u = current_user();
if ($u['role'] !== 'producteur' && $u['role'] !== 'admin') redirect('/index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) {
    http_response_code(400);
    exit('Requête invalide.');
}

$id = (int) ($_POST['id'] ?? 0);

if ($u['role'] === 'admin' || Product::belongsTo($id, (int) $u['id'])) {
    Product::delete($id);
    flash('success', 'Produit supprimé.');
} else {
    flash('error', 'Action non autorisée.');
}

redirect('/dashboard.php');
