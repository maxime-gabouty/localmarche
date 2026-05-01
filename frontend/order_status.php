<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/Order.php';
require_once __DIR__ . '/../backend/config/database.php';

require_login();
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) {
    http_response_code(400);
    exit('Requête invalide.');
}

$id     = (int) ($_POST['id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');

// Vérifier que la commande appartient bien au producteur courant
$stmt = db()->prepare(
    'SELECT id_producteur FROM orders WHERE id = :id LIMIT 1'
);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    flash('error', 'Commande introuvable.');
    redirect('/dashboard.php');
}

if ((int) $row['id_producteur'] !== (int) $u['id'] && $u['role'] !== 'admin') {
    http_response_code(403);
    exit('Accès refusé.');
}

if (Order::updateStatus($id, $status)) {
    flash('success', 'Statut de la commande mis à jour.');
} else {
    flash('error', 'Mise à jour impossible.');
}

redirect('/dashboard.php');
