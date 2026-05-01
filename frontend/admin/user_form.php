<?php
require_once __DIR__ . '/../../backend/helpers/session.php';
require_once __DIR__ . '/../../backend/helpers/validators.php';
require_once __DIR__ . '/../../backend/models/User.php';

require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$errors = [];
$old = ['nom' => '', 'prenom' => '', 'email' => '', 'ville' => '', 'code_postal' => '', 'role' => 'consommateur'];

if ($isEdit) {
    $u = User::findById($id);
    if (!$u) {
        http_response_code(404);
        $pageTitle = 'Utilisateur introuvable';
        require __DIR__ . '/../pages/_header.php';
        echo '<h1>Utilisateur introuvable</h1>';
        require __DIR__ . '/../pages/_footer.php';
        exit;
    }
    $old = $u;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['_'] = 'Jeton CSRF invalide.';
    } else {
        $old = [
            'id'          => $id,
            'nom'         => clean($_POST['nom'] ?? ''),
            'prenom'      => clean($_POST['prenom'] ?? ''),
            'email'       => clean($_POST['email'] ?? ''),
            'ville'       => clean($_POST['ville'] ?? ''),
            'code_postal' => clean($_POST['code_postal'] ?? ''),
            'role'        => in_array($_POST['role'] ?? '', ['consommateur','producteur','admin'], true) ? $_POST['role'] : 'consommateur',
        ];
        $errors = validate_required($old, ['nom', 'prenom', 'email', 'ville', 'code_postal']);
        if (empty($errors['email']) && !validate_email($old['email'])) {
            $errors['email'] = 'Adresse email invalide.';
        }

        $pwd = (string) ($_POST['password'] ?? '');
        if (!$isEdit) {
            if ($pwd === '') $errors['password'] = 'Mot de passe obligatoire.';
            elseif ($e = validate_password($pwd)) $errors['password'] = $e;
        } elseif ($pwd !== '' && ($e = validate_password($pwd))) {
            $errors['password'] = $e;
        }

        if (empty($errors)) {
            $existing = User::findByEmail($old['email']);
            if ($existing && (int) $existing['id'] !== $id) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }
        }

        if (empty($errors)) {
            if ($isEdit) {
                User::update($id, $old);
                if ($pwd !== '') User::updatePassword($id, $pwd);
                flash('success', 'Utilisateur mis à jour.');
            } else {
                User::create($old + ['password' => $pwd]);
                flash('success', 'Utilisateur créé.');
            }
            redirect('/admin/users.php');
        }
    }
}

$pageTitle = $isEdit ? 'Modifier utilisateur' : 'Nouvel utilisateur';
require __DIR__ . '/../pages/_header.php';
?>

<h1><?= $isEdit ? 'Modifier un utilisateur' : 'Nouvel utilisateur' ?></h1>

<?php if (!empty($errors['_'])): ?>
  <div class="alert error"><?= e($errors['_']) ?></div>
<?php endif; ?>

<form method="post" action="/admin/user_form.php<?= $isEdit ? '?id=' . $id : '' ?>">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <label>Prénom
    <input type="text" name="prenom" required maxlength="80" value="<?= e($old['prenom']) ?>">
  </label>
  <label>Nom
    <input type="text" name="nom" required maxlength="80" value="<?= e($old['nom']) ?>">
  </label>
  <label>Email
    <input type="email" name="email" required maxlength="150" value="<?= e($old['email']) ?>">
    <?php if (!empty($errors['email'])): ?><small style="color:var(--danger)"><?= e($errors['email']) ?></small><?php endif; ?>
  </label>
  <div class="row">
    <label style="flex:2">Ville
      <input type="text" name="ville" required maxlength="80" value="<?= e($old['ville']) ?>">
    </label>
    <label style="flex:1">Code postal
      <input type="text" name="code_postal" required maxlength="10" value="<?= e($old['code_postal']) ?>">
    </label>
  </div>
  <label>Rôle
    <select name="role" required>
      <?php foreach (['consommateur','producteur','admin'] as $r): ?>
        <option value="<?= $r ?>" <?= $old['role'] === $r ? 'selected' : '' ?>><?= e($r) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>
    Mot de passe <?= $isEdit ? '(laisser vide pour conserver)' : '(8 car. min)' ?>
    <input type="password" name="password" <?= $isEdit ? '' : 'required minlength="8"' ?>>
    <?php if (!empty($errors['password'])): ?><small style="color:var(--danger)"><?= e($errors['password']) ?></small><?php endif; ?>
  </label>

  <div class="row">
    <button type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
    <a href="/admin/users.php" class="btn secondary">Annuler</a>
  </div>
</form>

<?php require __DIR__ . '/../pages/_footer.php'; ?>
