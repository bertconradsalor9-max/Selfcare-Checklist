<?php
require_once 'db_connect.php';
include 'header.php';
if (!is_admin()) { header('Location: admin_login.php'); exit; }

// actions: delete user, change role, create user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';
        if ($username && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);
        }
    } elseif (isset($_POST['delete']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['change_role']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
    }
    header('Location: manage_users.php');
    exit;
}

$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<div class="card">
  <h1>Manage Users</h1>
  <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
    <input name="username" placeholder="username" required />
    <input type="password" name="password" placeholder="password" required />
    <select name="role"><option value="user">user</option><option value="admin">admin</option></select>
    <button class="button" name="create_user" type="submit">Create User</button>
  </form>

  <table class="table">
    <thead><tr><th>Username</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?=htmlspecialchars($u['username'])?></td>
          <td><?=htmlspecialchars($u['role'])?></td>
          <td><?=htmlspecialchars($u['created_at'])?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="id" value="<?=$u['id']?>" />
              <select name="role">
                <option value="user" <?=($u['role']=='user')?'selected':''?>>user</option>
                <option value="admin" <?=($u['role']=='admin')?'selected':''?>>admin</option>
              </select>
              <button class="button" name="change_role" type="submit">Change</button>
            </form>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete user?')">
              <input type="hidden" name="id" value="<?=$u['id']?>" />
              <button class="button secondary" name="delete" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include 'footer.php'; ?>
