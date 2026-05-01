<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/Order.php';

require_login();
$u = current_user();
if ($u['role'] !== 'consommateur') redirect('/index.php');

$page = max(1, (int) ($_GET['page'] ?? 1));
$orders = Order::listForConsumer((int) $u['id'], $page, 20);

$pageTitle = 'Mes commandes';
require __DIR__ . '/pages/_header.php';
?>

<h1>Mes commandes</h1>

<?php if ($msg = flash('success')): ?>
  <div class="alert success"><?= e($msg) ?></div>
<?php endif; ?>

<?php if (count($orders) === 0): ?>
  <p class="muted">Vous n'avez pas encore passé de commande.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>#</th><th>Date</th><th>Producteur</th><th>Total</th><th>Statut</th><th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= (int) $o['id'] ?></td>
          <td><?= e(date('d/m/Y H:i', strtotime($o['date_commande']))) ?></td>
          <td><?= e($o['prod_prenom'] . ' ' . $o['prod_nom']) ?> <span class="muted">· <?= e($o['prod_ville']) ?></span></td>
          <td><?= number_format((float) $o['total'], 2, ',', ' ') ?> €</td>
          <td>
            <?php
              $cls = $o['statut'] === 'validee' ? '' : ($o['statut'] === 'refusee' ? 'err' : 'warn');
              $lbl = ['en_attente' => 'En attente', 'validee' => 'Validée', 'refusee' => 'Refusée'][$o['statut']];
            ?>
            <span class="badge <?= $cls ?>"><?= e($lbl) ?></span>
          </td>
          <td><a href="/order_detail.php?id=<?= (int) $o['id'] ?>">Détail</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/pages/_footer.php'; ?>
