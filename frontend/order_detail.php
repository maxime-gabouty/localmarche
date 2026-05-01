<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/models/Order.php';
require_once __DIR__ . '/../backend/config/database.php';

require_login();
$u = current_user();
$id = (int) ($_GET['id'] ?? 0);

// Récupération de la commande
$stmt = db()->prepare(
    'SELECT id, id_consommateur, id_producteur, date_commande, statut, total
     FROM orders WHERE id = :id LIMIT 1'
);
$stmt->execute([':id' => $id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    $pageTitle = 'Commande introuvable';
    require __DIR__ . '/pages/_header.php';
    echo '<h1>Commande introuvable</h1>';
    require __DIR__ . '/pages/_footer.php';
    exit;
}

// Vérification des droits
$isOwner = (int) $order['id_consommateur'] === (int) $u['id']
        || (int) $order['id_producteur']   === (int) $u['id']
        || $u['role'] === 'admin';

if (!$isOwner) {
    http_response_code(403);
    $pageTitle = 'Accès refusé';
    require __DIR__ . '/pages/_header.php';
    echo '<h1>Accès refusé</h1>';
    require __DIR__ . '/pages/_footer.php';
    exit;
}

$items = Order::items($id);

$pageTitle = 'Commande #' . $id;
require __DIR__ . '/pages/_header.php';
?>

<h1>Commande #<?= (int) $order['id'] ?></h1>
<p class="meta">
  Passée le <?= e(date('d/m/Y à H:i', strtotime($order['date_commande']))) ?>
  · Statut :
  <?php
    $cls = $order['statut'] === 'validee' ? '' : ($order['statut'] === 'refusee' ? 'err' : 'warn');
    $lbl = ['en_attente' => 'En attente', 'validee' => 'Validée', 'refusee' => 'Refusée'][$order['statut']];
  ?>
  <span class="badge <?= $cls ?>"><?= e($lbl) ?></span>
</p>

<table class="table">
  <thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Sous-total</th></tr></thead>
  <tbody>
    <?php $total = 0; foreach ($items as $it): $st = (float) $it['quantite'] * (float) $it['prix_unitaire']; $total += $st; ?>
      <tr>
        <td><?= e($it['produit_nom']) ?></td>
        <td><?= number_format((float) $it['quantite'], 2, ',', ' ') ?> <?= e($it['unite']) ?></td>
        <td><?= number_format((float) $it['prix_unitaire'], 2, ',', ' ') ?> €</td>
        <td><?= number_format($st, 2, ',', ' ') ?> €</td>
      </tr>
    <?php endforeach; ?>
    <tr><td colspan="3" style="text-align:right"><strong>Total</strong></td>
        <td><strong><?= number_format($total, 2, ',', ' ') ?> €</strong></td></tr>
  </tbody>
</table>

<p><a href="<?= $u['role'] === 'producteur' ? '/dashboard.php' : '/orders.php' ?>" class="btn secondary">Retour</a></p>

<?php require __DIR__ . '/pages/_footer.php'; ?>
