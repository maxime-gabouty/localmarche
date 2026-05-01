<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/Product.php';
require_once __DIR__ . '/../backend/models/Order.php';

require_login();
$u = current_user();
if ($u['role'] !== 'consommateur') {
    flash('error', 'Seuls les consommateurs peuvent commander.');
    redirect('/products.php');
}

$id = (int) ($_GET['produit'] ?? 0);
$p  = Product::findById($id);

$pageTitle = 'Commander';

if (!$p) {
    require __DIR__ . '/pages/_header.php';
    echo '<h1>Produit introuvable</h1>';
    require __DIR__ . '/pages/_footer.php';
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Jeton CSRF invalide.';
    } else {
        $qte = (float) str_replace(',', '.', (string) ($_POST['quantite'] ?? '0'));
        if ($qte <= 0 || $qte > 10000) {
            $error = 'Quantité invalide.';
        } elseif ((int) $p['disponible'] !== 1) {
            $error = 'Ce produit n\'est plus disponible.';
        } else {
            try {
                $orderId = Order::createFromCart((int) $u['id'], [
                    ['id_product' => (int) $p['id'], 'quantite' => $qte],
                ]);
                flash('success', 'Commande #' . $orderId . ' enregistrée. Le producteur va la valider.');
                redirect('/orders.php');
            } catch (Throwable $ex) {
                $error = 'Erreur : ' . $ex->getMessage();
            }
        }
    }
}

require __DIR__ . '/pages/_header.php';
?>

<h1>Commander un produit</h1>
<article class="card" style="max-width:520px">
  <h3><?= e($p['nom']) ?></h3>
  <p><?= e($p['description']) ?></p>
  <p class="price"><?= number_format((float) $p['prix'], 2, ',', ' ') ?> € / <?= e($p['unite']) ?></p>
  <p class="meta">
    Vendu par <?= e($p['producteur_prenom'] . ' ' . $p['producteur_nom']) ?>
    · <?= e($p['producteur_ville']) ?>
  </p>
</article>

<?php if ($error): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="/order.php?produit=<?= (int) $p['id'] ?>">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Quantité (<?= e($p['unite']) ?>)
    <input type="number" name="quantite" min="0.1" step="0.1" max="100" required value="1">
  </label>
  <button type="submit">Valider la commande</button>
</form>

<?php require __DIR__ . '/pages/_footer.php'; ?>
