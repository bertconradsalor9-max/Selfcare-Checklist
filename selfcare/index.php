<?php
require_once 'db_connect.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// fetch categories for this user
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$categories = $stmt->fetchAll();

// fetch activities and calculate completed vs pending
$stmt = $pdo->prepare("
  SELECT a.*, c.name AS category_name,
  (SELECT COUNT(*) FROM activity_log al WHERE al.activity_id=a.id AND al.status='completed') AS completed_count
  FROM activities a
  LEFT JOIN categories c ON c.id = a.category_id
  WHERE a.user_id = ?
  ORDER BY a.created_at DESC
");
$stmt->execute([$uid]);
$activities = $stmt->fetchAll();

// count stats
$totalActivities = count($activities);
$completedActivities = 0;
foreach ($activities as $a) {
    if ((int)$a['completed_count'] > 0) $completedActivities++;
}
$pendingActivities = $totalActivities - $completedActivities;
?>
<div class="grid">
  <div>
    <div class="card">
      <h1>Dashboard</h1>
      <p class="small">Welcome, <?=htmlspecialchars($_SESSION['username'])?></p>

      <h3>Progress</h3>
      <p class="small">Completed: <?=$completedActivities?> / <?=$totalActivities?></p>
      <div class="progress" title="Progress">
        <i style="width: <?= $totalActivities ? (int)($completedActivities*100/$totalActivities) : 0 ?>%"></i>
      </div>
    </div>

    <div class="card">
      <h3>Activities</h3>
      <a href="add_activity.php" class="button">Add Activity</a>
      <table class="table">
        <thead><tr><th>Name</th><th>When</th><th>Category</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($activities as $act): ?>
          <tr>
            <td><?=htmlspecialchars($act['name'])?></td>
            <td><?=htmlspecialchars(sprintf("%02d/%02d at %s", $act['day'], $act['month'], $act['time']))?></td>
            <td><?=htmlspecialchars($act['category_name'])?></td>
            <td class="actions">
              <a href="edit_activity.php?id=<?=$act['id']?>">Edit</a>
              <a href="delete_activity.php?id=<?=$act['id']?>" onclick="return confirm('Delete activity?')">Delete</a>
              <a href="tracker.php?action=complete&id=<?=$act['id']?>">Mark Completed</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <aside>
    <div class="card">
      <h3>Categories</h3>
      <a href="add_category.php" class="button">Add Category</a>
      <ul>
        <?php foreach ($categories as $cat): ?>
          <li>
            <?=htmlspecialchars($cat['name'])?>
            <span class="small">
              <a href="edit_category.php?id=<?=$cat['id']?>">Edit</a> |
              <a href="delete_category.php?id=<?=$cat['id']?>" onclick="return confirm('Delete category?')">Delete</a>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card">
      <h3>Tracker</h3>
      <p class="small">Open <a href="tracker.php">Tracker</a> to view reminders and mark tasks as done.</p>
    </div>
  </aside>
</div>
<?php include 'footer.php'; ?>
