<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/Product.php';
require_once __DIR__ . '/../backend/models/Order.php';

require_login();
$u = current_user();
if ($u['role'] !== 'producteur' && $u['role'] !== 'admin') redirect('/index.php');

$pageProducts = max(1, (int) ($_GET['pp'] ?? 1));
$pageOrders   = max(1, (int) ($_GET['po'] ?? 1));

$products = Product::listByProducer((int) $u['id'], $pageProducts, 10);
$totalProducts = Product::countByProducer((int) $u['id']);
$totalProductsPages = max(1, (int) ceil($totalProducts / 10));

$orders = Order::listForProducer((int) $u['id'], $pageOrders, 10);

$pageTitle = 'Mon espace producteur';
require __DIR__ . '/pages/_header.php';
?>

<h1>Mon espace producteur</h1>
<p class="muted">Bienvenue <?= e($u['prenom']) ?>. Gérez vos produits et vos commandes.</p>

<?php if ($msg = flash('success')): ?>
  <div class="alert success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="alert error"><?= e($msg) ?></div>
<?php endif; ?>

<h2>Mes produits (<?= $totalProducts ?>)</h2>
<p><a href="/product_form.php" class="btn">Ajouter un produit</a></p>

<?php if (count($products) === 0): ?>
  <p class="muted">Vous n'avez encore enregistré aucun produit.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr><th>Nom</th><th>Prix</th><th>Unité</th><th>Disponible</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= e($p['nom']) ?></td>
          <td><?= number_format((float) $p['prix'], 2, ',', ' ') ?> €</td>
          <td><?= e($p['unite']) ?></td>
          <td>
            <?php if ((int) $p['disponible'] === 1): ?>
              <span class="badge">Oui</span>
            <?php else: ?>
              <span class="badge err">Non</span>
            <?php endif; ?>
          </td>
          <td class="actions">
            <a href="/product_form.php?id=<?= (int) $p['id'] ?>" class="btn secondary">Modifier</a>
            <form method="post" action="/product_delete.php" data-confirm="Supprimer ce produit ?" style="display:inline">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($totalProductsPages > 1): ?>
    <nav class="pagination">
      <?php for ($i = 1; $i <= $totalProductsPages; $i++): ?>
        <?php if ($i === $pageProducts): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?pp=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<h2>Commandes reçues</h2>
<?php if (count($orders) === 0): ?>
  <p class="muted">Aucune commande reçue pour le moment.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr><th>#</th><th>Date</th><th>Client</th><th>Total</th><th>Statut</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= (int) $o['id'] ?></td>
          <td><?= e(date('d/m/Y H:i', strtotime($o['date_commande']))) ?></td>
          <td><?= e($o['cons_prenom'] . ' ' . $o['cons_nom']) ?> <span class="muted">· <?= e($o['cons_ville']) ?></span></td>
          <td><?= number_format((float) $o['total'], 2, ',', ' ') ?> €</td>
          <td>
            <?php
              $cls = $o['statut'] === 'validee' ? '' : ($o['statut'] === 'refusee' ? 'err' : 'warn');
              $lbl = ['en_attente' => 'En attente', 'validee' => 'Validée', 'refusee' => 'Refusée'][$o['statut']];
            ?>
            <span class="badge <?= $cls ?>"><?= e($lbl) ?></span>
          </td>
          <td class="actions">
            <a href="/order_detail.php?id=<?= (int) $o['id'] ?>" class="btn secondary">Voir</a>
            <?php if ($o['statut'] === 'en_attente'): ?>
              <form method="post" action="/order_status.php" style="display:inline">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                <input type="hidden" name="status" value="validee">
                <button type="submit">Valider</button>
              </form>
              <form method="post" action="/order_status.php" style="display:inline">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                <input type="hidden" name="status" value="refusee">
                <button type="submit" class="danger">Refuser</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/pages/_footer.php'; ?>
