<?php
require_once __DIR__ . '/../../backend/helpers/session.php';
require_once __DIR__ . '/../../backend/helpers/validators.php';
require_once __DIR__ . '/../../backend/models/User.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) {
    http_response_code(400);
    exit('Requête invalide.');
}

$id = (int) ($_POST['id'] ?? 0);
$current = current_user();

if ($id === (int) $current['id']) {
    flash('error', 'Vous ne pouvez pas supprimer votre propre compte depuis cet écran.');
} else {
    User::delete($id);
    flash('success', 'Utilisateur supprimé.');
}

redirect('/admin/users.php');
