<?php
require_once __DIR__ . '/../backend/models/User.php';

$pageTitle = 'Producteurs';
$ville = isset($_GET['ville']) && $_GET['ville'] !== '' ? trim($_GET['ville']) : null;
$page  = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

$producteurs = User::listProducteurs($ville, $page, $perPage);
$total = User::countProducteurs($ville);
$totalPages = max(1, (int) ceil($total / $perPage));
$cities = User::distinctCities();

require __DIR__ . '/pages/_header.php';
?>

<h1>Producteurs locaux</h1>
<p class="muted"><?= $total ?> producteur<?= $total > 1 ? 's' : '' ?> référencé<?= $total > 1 ? 's' : '' ?>.</p>

<form method="get" action="/producers.php" style="max-width:none;flex-direction:row;align-items:end;flex-wrap:wrap">
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
  <?php if ($ville): ?><a href="/producers.php" class="btn secondary">Réinitialiser</a><?php endif; ?>
</form>

<?php if (count($producteurs) === 0): ?>
  <p class="muted">Aucun producteur trouvé pour ce filtre.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($producteurs as $p): ?>
      <article class="card">
        <h3><?= e($p['prenom'] . ' ' . $p['nom']) ?></h3>
        <p class="meta"><?= e($p['ville']) ?> · <?= e($p['code_postal']) ?></p>
        <a class="btn secondary" href="/producer.php?id=<?= (int) $p['id'] ?>">Voir ses produits</a>
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
