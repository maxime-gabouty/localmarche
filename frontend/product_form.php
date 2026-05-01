<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/Product.php';

require_login();
$u = current_user();
if ($u['role'] !== 'producteur' && $u['role'] !== 'admin') redirect('/index.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$errors = [];
$old = ['nom' => '', 'description' => '', 'prix' => '', 'unite' => 'kg', 'disponible' => 1];

if ($isEdit) {
    $p = Product::findById($id);
    if (!$p || ((int) $p['id_producteur'] !== (int) $u['id'] && $u['role'] !== 'admin')) {
        http_response_code(404);
        $pageTitle = 'Produit introuvable';
        require __DIR__ . '/pages/_header.php';
        echo '<h1>Produit introuvable</h1>';
        require __DIR__ . '/pages/_footer.php';
        exit;
    }
    $old = $p;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['_'] = 'Jeton CSRF invalide.';
    } else {
        $old = [
            'nom'         => clean($_POST['nom'] ?? ''),
            'description' => clean($_POST['description'] ?? ''),
            'prix'        => str_replace(',', '.', clean($_POST['prix'] ?? '')),
            'unite'       => in_array($_POST['unite'] ?? '', ['kg','piece','litre','botte'], true) ? $_POST['unite'] : 'kg',
            'disponible'  => isset($_POST['disponible']) ? 1 : 0,
        ];

        $errors = validate_required($old, ['nom', 'description', 'prix']);
        if (empty($errors['prix']) && (!is_numeric($old['prix']) || (float) $old['prix'] <= 0)) {
            $errors['prix'] = 'Prix invalide.';
        }
        if (mb_strlen($old['description']) > 200) {
            $errors['description'] = 'La description ne doit pas dépasser 200 caractères.';
        }

        if (empty($errors)) {
            if ($isEdit) {
                Product::update($id, $old);
                flash('success', 'Produit mis à jour.');
            } else {
                $old['id_producteur'] = (int) $u['id'];
                Product::create($old);
                flash('success', 'Produit créé.');
            }
            redirect('/dashboard.php');
        }
    }
}

$pageTitle = $isEdit ? 'Modifier un produit' : 'Nouveau produit';
require __DIR__ . '/pages/_header.php';
?>

<h1><?= $isEdit ? 'Modifier un produit' : 'Nouveau produit' ?></h1>

<?php if (!empty($errors['_'])): ?>
  <div class="alert error"><?= e($errors['_']) ?></div>
<?php endif; ?>

<form method="post" action="/product_form.php<?= $isEdit ? '?id=' . $id : '' ?>">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <label>Nom du produit
    <input type="text" name="nom" required maxlength="120" value="<?= e($old['nom']) ?>">
    <?php if (!empty($errors['nom'])): ?><small style="color:var(--danger)"><?= e($errors['nom']) ?></small><?php endif; ?>
  </label>

  <label>Description courte (200 car. max)
    <textarea name="description" required maxlength="200" rows="3"><?= e($old['description']) ?></textarea>
    <?php if (!empty($errors['description'])): ?><small style="color:var(--danger)"><?= e($errors['description']) ?></small><?php endif; ?>
  </label>

  <div class="row">
    <label style="flex:1">Prix (€)
      <input type="text" name="prix" required value="<?= e((string) $old['prix']) ?>" inputmode="decimal">
      <?php if (!empty($errors['prix'])): ?><small style="color:var(--danger)"><?= e($errors['prix']) ?></small><?php endif; ?>
    </label>
    <label style="flex:1">Unité
      <select name="unite" required>
        <?php foreach (['kg' => 'au kilo', 'piece' => 'à la pièce', 'litre' => 'au litre', 'botte' => 'à la botte'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $old['unite'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>

  <label style="flex-direction:row;align-items:center;gap:0.5rem">
    <input type="checkbox" name="disponible" value="1" <?= !empty($old['disponible']) ? 'checked' : '' ?>>
    Produit disponible à la vente
  </label>

  <div class="row">
    <button type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
    <a href="/dashboard.php" class="btn secondary">Annuler</a>
  </div>
</form>

<?php require __DIR__ . '/pages/_footer.php'; ?>
