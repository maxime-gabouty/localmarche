<?php
require_once __DIR__ . '/../backend/models/User.php';
require_once __DIR__ . '/../backend/models/Product.php';

$id = (int) ($_GET['id'] ?? 0);
$producteur = User::findById($id);
if (!$producteur || $producteur['role'] !== 'producteur') {
    http_response_code(404);
    $pageTitle = 'Producteur introuvable';
    require __DIR__ . '/pages/_header.php';
    echo '<h1>Producteur introuvable</h1><p>Le producteur demandé n\'existe pas.</p>';
    require __DIR__ . '/pages/_footer.php';
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$products = Product::listByProducer($id, $page, $perPage);
$total = Product::countByProducer($id);
$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = $producteur['prenom'] . ' ' . $producteur['nom'];
require __DIR__ . '/pages/_header.php';
?>

<h1><?= e($producteur['prenom'] . ' ' . $producteur['nom']) ?></h1>
<p class="muted"><?= e($producteur['ville']) ?> · <?= e($producteur['code_postal']) ?></p>

<h2>Produits proposés</h2>
<?php if (count($products) === 0): ?>
  <p class="muted">Ce producteur n'a pas encore enregistré de produits.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
      <article class="card">
        <h3><?= e($p['nom']) ?></h3>
        <p><?= e($p['description']) ?></p>
        <p class="price"><?= number_format((float) $p['prix'], 2, ',', ' ') ?> € / <?= e($p['unite']) ?></p>
        <?php if ((int) $p['disponible'] === 1): ?>
          <span class="badge">Disponible</span>
          <p style="margin-top:0.6rem">
            <a href="/order.php?produit=<?= (int) $p['id'] ?>" class="btn">Commander</a>
          </p>
        <?php else: ?>
          <span class="badge err">Indisponible</span>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
          <a href="?id=<?= $id ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/pages/_footer.php'; ?>
