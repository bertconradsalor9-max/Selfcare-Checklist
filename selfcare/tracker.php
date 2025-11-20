<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    // If JSON fetch request, return empty
    if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
        echo json_encode([]);
        exit;
    }
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// JSON endpoint for fetching activities for reminders
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    // return activities for this user with category name
    $stmt = $pdo->prepare("SELECT a.*, c.name AS category_name FROM activities a LEFT JOIN categories c ON c.id=a.category_id WHERE a.user_id = ?");
    $stmt->execute([$uid]);
    $rows = $stmt->fetchAll();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    exit;
}

// Mark completed action
if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['id'])) {
    $aid = (int)$_GET['id'];
    // insert into activity_log
    $stmt = $pdo->prepare("INSERT INTO activity_log (activity_id, user_id, status) VALUES (?, ?, 'completed')");
    $stmt->execute([$aid, $uid]);
    header('Location: tracker.php');
    exit;
}

// Page: show today's activities and logs
include 'header.php';
?>
<div class="card">
  <h1>Tracker</h1>
  <p class="small">Reminders will appear on this page and via browser notifications 10 minutes before scheduled time.</p>

  <?php
    // today's activities by date/month
    $today = (int)date('j');
    $month = (int)date('n');

    $stmt = $pdo->prepare("SELECT a.*, c.name AS category_name,
        (SELECT COUNT(*) FROM activity_log al WHERE al.activity_id = a.id AND al.user_id = ? AND al.status='completed') AS done
      FROM activities a
      LEFT JOIN categories c ON c.id = a.category_id
      WHERE a.user_id = ? AND a.day = ? AND a.month = ?
      ORDER BY a.time ASC
    ");
    $stmt->execute([$uid, $uid, $today, $month]);
    $activities = $stmt->fetchAll();
  ?>

  <table class="table">
    <thead><tr><th>Name</th><th>Time</th><th>Category</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if (!$activities): ?>
        <tr><td colspan="5">No activities scheduled for today.</td></tr>
      <?php else: ?>
        <?php foreach ($activities as $a): ?>
          <tr>
            <td><?=htmlspecialchars($a['name'])?></td>
            <td><?=htmlspecialchars($a['time'])?></td>
            <td><?=htmlspecialchars($a['category_name'])?></td>
            <td><?=($a['done']>0)?'<strong style="color:green">Completed</strong>':'Pending'?></td>
            <td>
              <?php if ($a['done'] == 0): ?>
                <a class="button" href="tracker.php?action=complete&id=<?=$a['id']?>">Mark Completed</a>
              <?php else: ?>
                <span class="small">Done</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h3>Activity Log (recent)</h3>
  <?php
    $stmt = $pdo->prepare("SELECT al.*, a.name AS activity_name, u.username FROM activity_log al
      LEFT JOIN activities a ON a.id = al.activity_id
      LEFT JOIN users u ON u.id = al.user_id
      WHERE al.user_id = ?
      ORDER BY al.timestamp DESC
      LIMIT 50
    ");
    $stmt->execute([$uid]);
    $logs = $stmt->fetchAll();
  ?>
  <table class="table">
    <thead><tr><th>Activity</th><th>Status</th><th>When</th></tr></thead>
    <tbody>
      <?php if (!$logs): ?>
        <tr><td colspan="3">No logs yet.</td></tr>
      <?php else: foreach ($logs as $l): ?>
        <tr>
          <td><?=htmlspecialchars($l['activity_name'] ?? '—')?></td>
          <td><?=htmlspecialchars($l['status'])?></td>
          <td><?=htmlspecialchars($l['timestamp'])?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<script>
  // mark this page as tracker for script.js
  document.body.dataset.tracker = "1";
</script>

<?php include 'footer.php'; ?>
