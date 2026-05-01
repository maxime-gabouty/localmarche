<?php
require_once __DIR__ . '/../backend/models/Product.php';
require_once __DIR__ . '/../backend/models/User.php';

$pageTitle = 'Produits';
$ville = isset($_GET['ville']) && $_GET['ville'] !== '' ? trim($_GET['ville']) : null;
$page  = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

$catalog = Product::catalog($ville, $page, $perPage);
$total   = Product::countCatalog($ville);
$totalPages = max(1, (int) ceil($total / $perPage));
$cities = User::distinctCities();

require __DIR__ . '/pages/_header.php';
?>

<h1>Catalogue de produits</h1>
<p class="muted"><?= $total ?> produit<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?>.</p>

<form method="get" action="/products.php" style="max-width:none;flex-direction:row;align-items:end;flex-wrap:wrap">
  <label style="flex:1;min-width:200px">
    Ville
    <select name="ville">
      <option value="">— Toutes les villes —</option>
      <?php foreach ($cities as $c): ?>
        <option value="<?= e($c) ?>" <?= $ville === $c ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit">Filtrer</button>
  <?php if ($ville): ?><a href="/products.php" class="btn secondary">Réinitialiser</a><?php endif; ?>
</form>

<?php if (count($catalog) === 0): ?>
  <p class="muted">Aucun produit trouvé.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($catalog as $p): ?>
      <article class="card">
        <h3><?= e($p['nom']) ?></h3>
        <p><?= e($p['description']) ?></p>
        <p class="price"><?= number_format((float) $p['prix'], 2, ',', ' ') ?> € / <?= e($p['unite']) ?></p>
        <p class="meta">
          Vendu par
          <a href="/producer.php?id=<?= (int) $p['producteur_id'] ?>">
            <?= e($p['producteur_prenom'] . ' ' . $p['producteur_nom']) ?>
          </a>
          · <?= e($p['producteur_ville']) ?>
        </p>
        <a href="/order.php?produit=<?= (int) $p['id'] ?>" class="btn">Commander</a>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
          <a href="?<?= http_build_query(array_filter(['ville' => $ville, 'page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/pages/_footer.php'; ?>
