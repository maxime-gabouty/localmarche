<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/User.php';

$pageTitle = 'Inscription';
$errors = [];
$old    = ['nom' => '', 'prenom' => '', 'email' => '', 'ville' => '', 'code_postal' => '', 'role' => 'consommateur'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['_'] = 'Jeton CSRF invalide.';
    } else {
        $old = [
            'nom'         => clean($_POST['nom'] ?? ''),
            'prenom'      => clean($_POST['prenom'] ?? ''),
            'email'       => clean($_POST['email'] ?? ''),
            'ville'       => clean($_POST['ville'] ?? ''),
            'code_postal' => clean($_POST['code_postal'] ?? ''),
            'role'        => in_array($_POST['role'] ?? '', ['consommateur', 'producteur'], true) ? $_POST['role'] : 'consommateur',
        ];
        $pwd  = (string) ($_POST['password'] ?? '');
        $pwd2 = (string) ($_POST['password_confirm'] ?? '');

        $errors = validate_required(
            $old + ['password' => $pwd],
            ['nom', 'prenom', 'email', 'ville', 'code_postal', 'password']
        );

        if (empty($errors['email']) && !validate_email($old['email'])) {
            $errors['email'] = 'Adresse email invalide.';
        }
        if (empty($errors['password'])) {
            if ($e = validate_password($pwd)) $errors['password'] = $e;
            elseif ($pwd !== $pwd2)            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
        }
        if (empty($errors) && User::findByEmail($old['email'])) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (empty($errors)) {
            $id = User::create($old + ['password' => $pwd]);
            // Connexion automatique
            $_SESSION['user'] = User::findById($id);
            flash('success', 'Bienvenue sur LocalMarché !');
            redirect($_SESSION['user']['role'] === 'producteur' ? '/dashboard.php' : '/account.php');
        }
    }
}

require __DIR__ . '/pages/_header.php';
?>

<h1>Inscription</h1>
<p class="muted">Créez un compte consommateur ou producteur en moins d'une minute.</p>

<?php if (!empty($errors['_'])): ?>
  <div class="alert error"><?= e($errors['_']) ?></div>
<?php endif; ?>

<form method="post" action="/register.php" novalidate>
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <label>Prénom
    <input type="text" name="prenom" required maxlength="80" value="<?= e($old['prenom']) ?>">
    <?php if (!empty($errors['prenom'])): ?><small style="color:var(--danger)"><?= e($errors['prenom']) ?></small><?php endif; ?>
  </label>

  <label>Nom
    <input type="text" name="nom" required maxlength="80" value="<?= e($old['nom']) ?>">
    <?php if (!empty($errors['nom'])): ?><small style="color:var(--danger)"><?= e($errors['nom']) ?></small><?php endif; ?>
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

  <label>Je suis
    <select name="role" required>
      <option value="consommateur" <?= $old['role'] === 'consommateur' ? 'selected' : '' ?>>Consommateur</option>
      <option value="producteur" <?= $old['role'] === 'producteur' ? 'selected' : '' ?>>Producteur</option>
    </select>
  </label>

  <label>Mot de passe (8 caractères minimum)
    <input type="password" name="password" required minlength="8">
    <?php if (!empty($errors['password'])): ?><small style="color:var(--danger)"><?= e($errors['password']) ?></small><?php endif; ?>
  </label>

  <label>Confirmer le mot de passe
    <input type="password" name="password_confirm" required minlength="8">
    <?php if (!empty($errors['password_confirm'])): ?><small style="color:var(--danger)"><?= e($errors['password_confirm']) ?></small><?php endif; ?>
  </label>

  <div class="row">
    <button type="submit">Créer mon compte</button>
    <a href="/login.php" class="muted">Déjà inscrit ? Connectez-vous</a>
  </div>
</form>

<?php require __DIR__ . '/pages/_footer.php'; ?>
