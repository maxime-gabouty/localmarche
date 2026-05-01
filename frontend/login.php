<?php
require_once __DIR__ . '/../backend/helpers/session.php';
require_once __DIR__ . '/../backend/helpers/validators.php';
require_once __DIR__ . '/../backend/models/User.php';

$pageTitle = 'Connexion';
$error = null;
$email_old = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Jeton CSRF invalide.';
    } else {
        $email_old = clean($_POST['email'] ?? '');
        $pwd       = (string) ($_POST['password'] ?? '');

        if ($email_old === '' || $pwd === '') {
            $error = 'Email et mot de passe obligatoires.';
        } else {
            $user = User::findByEmail($email_old);
            if (!$user || !password_verify($pwd, $user['password'])) {
                $error = 'Identifiants invalides.';
                // Anti-brute-force basique (sleep)
                usleep(200000);
            } else {
                start_session();
                session_regenerate_id(true);
                unset($user['password']);
                $_SESSION['user'] = $user;
                $dest = $user['role'] === 'producteur' ? '/dashboard.php'
                      : ($user['role'] === 'admin'    ? '/admin/users.php'
                      : '/account.php');
                redirect($dest);
            }
        }
    }
}

require __DIR__ . '/pages/_header.php';
?>

<h1>Connexion</h1>

<?php if ($error): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($msg = flash('success')): ?>
  <div class="alert success"><?= e($msg) ?></div>
<?php endif; ?>

<form method="post" action="/login.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Email
    <input type="email" name="email" required value="<?= e($email_old) ?>">
  </label>
  <label>Mot de passe
    <input type="password" name="password" required>
  </label>
  <div class="row">
    <button type="submit">Se connecter</button>
    <a href="/register.php" class="muted">Pas encore de compte ?</a>
  </div>
</form>

<?php require __DIR__ . '/pages/_footer.php'; ?>
