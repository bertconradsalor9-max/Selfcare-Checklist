<?php
require_once 'db_connect.php';
include 'header.php';
if (!is_admin()) { header('Location: admin_login.php'); exit; }

// handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $user_id = (int)$_POST['user_id'];
        $category_id = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $time = $_POST['time'] ?? '';
        $day = (int)($_POST['day'] ?? 0);
        $month = (int)($_POST['month'] ?? 0);
        if ($user_id && $category_id && $name && $time && $day && $month) {
            $stmt = $pdo->prepare("INSERT INTO activities (category_id, user_id, name, description, time, day, month) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $user_id, $name, $description, $time, $day, $month]);
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $time = $_POST['time'] ?? '';
        $day = (int)($_POST['day'] ?? 0);
        $month = (int)($_POST['month'] ?? 0);
        if ($name && $time && $day && $month) {
            $stmt = $pdo->prepare("UPDATE activities SET name=?, description=?, time=?, day=?, month=? WHERE id=?");
            $stmt->execute([$name, $description, $time, $day, $month, $id]);
        }
    }
    header('Location: manage_activities.php');
    exit;
}

$users = $pdo->query("SELECT id, username FROM users")->fetchAll();
$cats = $pdo->query("SELECT id, name, user_id FROM categories")->fetchAll();
$activities = $pdo->query("SELECT a.*, c.name AS category_name, u.username FROM activities a LEFT JOIN categories c ON c.id=a.category_id LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC")->fetchAll();
?>
<div class="card">
  <h1>Manage Activities</h1>
  <form method="post" style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;align-items:center;margin-bottom:12px">
    <select name="user_id" required>
      <option value="">--user--</option>
      <?php foreach ($users as $u): ?><option value="<?=$u['id']?>"><?=htmlspecialchars($u['username'])?></option><?php endforeach; ?>
    </select>
    <select name="category_id" required>
      <option value="">--category--</option>
      <?php foreach ($cats as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?>
    </select>
    <input name="name" placeholder="name" />
    <input type="time" name="time" />
    <input type="number" name="day" placeholder="day" min="1" max="31" />
    <input type="number" name="month" placeholder="month" min="1" max="12" />
    <button class="button" name="create" type="submit">Create</button>
  </form>

  <table class="table">
    <thead><tr><th>User</th><th>Category</th><th>Name</th><th>When</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($activities as $a): ?>
        <tr>
          <td><?=htmlspecialchars($a['username'])?></td>
          <td><?=htmlspecialchars($a['category_name'])?></td>
          <td><?=htmlspecialchars($a['name'])?></td>
          <td><?=sprintf("%02d/%02d %s", $a['day'], $a['month'], $a['time'])?></td>
          <td>
            <form method="post" style="display:inline">
              <input type="hidden" name="id" value="<?=$a['id']?>" />
              <input name="name" value="<?=htmlspecialchars($a['name'])?>" />
              <input name="time" value="<?=htmlspecialchars($a['time'])?>" />
              <input type="number" name="day" value="<?=htmlspecialchars($a['day'])?>" min="1" max="31" />
              <input type="number" name="month" value="<?=htmlspecialchars($a['month'])?>" min="1" max="12" />
              <button class="button" name="update" type="submit">Update</button>
            </form>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete activity?')">
              <input type="hidden" name="id" value="<?=$a['id']?>" />
              <button class="button secondary" name="delete" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include 'footer.php'; ?>
