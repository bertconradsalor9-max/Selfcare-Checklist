<?php
require_once 'db_connect.php';
require_once 'header.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $time = $_POST['time'] ?? '';
    $day = (int)($_POST['day'] ?? 0);
    $month = (int)($_POST['month'] ?? 0);

    if (empty($name) || !$category_id || empty($time) || !$day || !$month) {
        $errors[] = 'All fields are required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO activities (category_id, user_id, name, description, time, day, month) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $uid, $name, $desc, $time, $day, $month]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch categories
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$stmt->execute([$uid]);
$cats = $stmt->fetchAll();
?>
<div class="card">
    <h1>Add Activity</h1>
    <?php foreach ($errors as $error): ?>
        <div class="notice error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <form method="post">
        <div class="form-row">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required>
        </div>
        <div class="form-row">
            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div class="form-row">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Choose --</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label for="time">Time</label>
            <input id="time" name="time" type="time" required>
        </div>
        <div class="form-row">
            <label for="day">Day (1-31)</label>
            <input id="day" name="day" type="number" min="1" max="31" required>
        </div>
        <div class="form-row">
            <label for="month">Month (1-12)</label>
            <input id="month" name="month" type="number" min="1" max="12" required>
        </div>
        <button class="button" type="submit">Save</button>
        <a href="index.php" class="button secondary">Cancel</a>
    </form>
</div>
<?php require_once 'footer.php'; ?>