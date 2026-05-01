<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/User.php';

require_login();
$u = current_user();
if ($u['role'] === 'admin')      redirect('/admin/users.php');
if ($u['role'] === 'producteur') redirect('/dashboard.php');

$pageTitle = 'Mon compte';
$errors = [];
$old = $u;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['_'] = 'Jeton CSRF invalide.';
    } else {
        $old = [
            'id'          => $u['id'],
            'nom'         => clean($_POST['nom'] ?? ''),
            'prenom'      => clean($_POST['prenom'] ?? ''),
            'email'       => clean($_POST['email'] ?? ''),
            'ville'       => clean($_POST['ville'] ?? ''),
            'code_postal' => clean($_POST['code_postal'] ?? ''),
            'role'        => $u['role'],
        ];
        $errors = validate_required($old, ['nom', 'prenom', 'email', 'ville', 'code_postal']);
        if (empty($errors['email']) && !validate_email($old['email'])) {
            $errors['email'] = 'Adresse email invalide.';
        }
        if (empty($errors)) {
            // Vérifie que l'email n'est pas déjà pris par un autre utilisateur
            $existing = User::findByEmail($old['email']);
            if ($existing && (int) $existing['id'] !== (int) $u['id']) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }
        }
        if (empty($errors)) {
            User::update((int) $u['id'], $old);
            $_SESSION['user'] = User::findById((int) $u['id']);
            flash('success', 'Profil mis à jour.');
            redirect('/account.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_check($_POST['csrf'] ?? null)) {
        User::delete((int) $u['id']);
        $_SESSION = [];
        session_destroy();
        redirect('/index.php');
    }
}

require __DIR__ . '/pages/_header.php';
?>

<h1>Mon compte</h1>

<?php if ($msg = flash('success')): ?>
  <div class="alert success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if (!empty($errors['_'])): ?>
  <div class="alert error"><?= e($errors['_']) ?></div>
<?php endif; ?>

<form method="post" action="/account.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="update">

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
  <button type="submit">Enregistrer</button>
</form>

<h2>Supprimer mon compte</h2>
<p class="muted">Cette action est définitive. Vos commandes et données seront effacées.</p>
<form method="post" action="/account.php" data-confirm="Confirmez-vous la suppression définitive de votre compte ?">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="delete">
  <button type="submit" class="danger">Supprimer mon compte</button>
</form>

<?php require __DIR__ . '/pages/_footer.php'; ?>
