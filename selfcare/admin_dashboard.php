<?php
require_once 'db_connect.php';
require_once 'header.php';

if (!is_admin()) {
    header('Location: admin_login.php');
    exit;
}

// Fetch stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users");
    $totalUsers = $stmt->fetch()['cnt'];

    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM categories");
    $totalCats = $stmt->fetch()['cnt'];

    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM activities");
    $totalActs = $stmt->fetch()['cnt'];

    $stmt = $pdo->query("SELECT al.*, a.name AS activity_name, u.username FROM activity_log al LEFT JOIN activities a ON a.id = al.activity_id LEFT JOIN users u ON u.id = al.user_id ORDER BY al.timestamp DESC LIMIT 50");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Database error: ' . htmlspecialchars($e->getMessage()));
}
?>
<div class="card">
    <h1>Admin Dashboard</h1>
    <p class="small">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</p>
    <div class="stats">
        <strong>Users:</strong> <?= $totalUsers ?> |
        <strong>Categories:</strong> <?= $totalCats ?> |
        <strong>Activities:</strong> <?= $totalActs ?>
    </div>
    <div class="actions">
        <a href="manage_users.php" class="button">Manage Users</a>
        <a href="manage_categories.php" class="button">Manage Categories</a>
        <a href="manage_activities.php" class="button">Manage Activities</a>
    </div>
</div>

<div class="card">
    <h3>Recent Activity Logs</h3>
    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Activity</th>
                <th>Status</th>
                <th>When</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['username'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($log['activity_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($log['status']) ?></td>
                    <td><?= htmlspecialchars($log['timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once 'footer.php'; ?>