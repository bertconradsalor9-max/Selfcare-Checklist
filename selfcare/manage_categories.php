<?php
require_once 'db_connect.php';
include 'header.php';
if (!is_admin()) { header('Location: admin_login.php'); exit; }

// handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $user_id = (int)$_POST['user_id'];
        $name = trim($_POST['name'] ?? '');
        if ($name && $user_id) {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            $stmt->execute([$user_id, $name]);
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
        }
    }
    header('Location: manage_categories.php');
    exit;
}

$users = $pdo->query("SELECT id, username FROM users")->fetchAll();
$cats = $pdo->query("SELECT c.*, u.username FROM categories c LEFT JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC")->fetchAll();
?>
<div class="card">
  <h1>Manage Categories</h1>
  <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
    <select name="user_id" required>
      <option value="">-- user --</option>
      <?php foreach ($users as $u): ?><option value="<?=$u['id']?>"><?=htmlspecialchars($u['username'])?></option><?php endforeach; ?>
    </select>
    <input name="name" placeholder="category name" />
    <button class="button" name="create" type="submit">Create</button>
  </form>

  <table class="table">
    <thead><tr><th>User</th><th>Name</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($cats as $c): ?>
        <tr>
          <td><?=htmlspecialchars($c['username'])?></td>
          <td><?=htmlspecialchars($c['name'])?></td>
          <td><?=htmlspecialchars($c['created_at'])?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="id" value="<?=$c['id']?>" />
              <input name="name" value="<?=htmlspecialchars($c['name'])?>" />
              <button class="button" name="update" type="submit">Update</button>
            </form>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete category?')">
              <input type="hidden" name="id" value="<?=$c['id']?>" />
              <button class="button secondary" name="delete" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include 'footer.php'; ?>
