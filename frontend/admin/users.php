<?php
require_once __DIR__ . '/../../backend/helpers/session.php';
require_once __DIR__ . '/../../backend/models/User.php';

require_admin();

$page = max(1, (int) ($_GET['page'] ?? 1));
$users = User::listPaginated($page, 20);
$total = User::count();
$totalPages = max(1, (int) ceil($total / 20));

$pageTitle = 'Administration · Utilisateurs';
require __DIR__ . '/../pages/_header.php';
?>

<h1>Utilisateurs (<?= $total ?>)</h1>

<?php if ($msg = flash('success')): ?>
  <div class="alert success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="alert error"><?= e($msg) ?></div>
<?php endif; ?>

<p><a href="/admin/user_form.php" class="btn">Ajouter un utilisateur</a></p>

<table class="table">
  <thead>
    <tr>
      <th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Ville</th><th>Inscrit</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $row): ?>
      <tr>
        <td>#<?= (int) $row['id'] ?></td>
        <td><?= e($row['prenom'] . ' ' . $row['nom']) ?></td>
        <td><?= e($row['email']) ?></td>
        <td><span class="badge <?= $row['role'] === 'admin' ? 'warn' : ($row['role'] === 'producteur' ? '' : 'info') ?>"><?= e($row['role']) ?></span></td>
        <td><?= e($row['ville']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($row['date_inscription']))) ?></td>
        <td class="actions">
          <a href="/admin/user_form.php?id=<?= (int) $row['id'] ?>" class="btn secondary">Modifier</a>
          <form method="post" action="/admin/user_delete.php" data-confirm="Confirmer la suppression de cet utilisateur ?" style="display:inline">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button type="submit" class="danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
  <nav class="pagination" aria-label="Pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?php if ($i === $page): ?>
        <span class="current"><?= $i ?></span>
      <?php else: ?>
        <a href="?page=<?= $i ?>"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/../pages/_footer.php'; ?>
