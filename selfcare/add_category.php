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
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            $stmt->execute([$uid, $name]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<div class="card">
    <h1>Add Category</h1>
    <?php foreach ($errors as $error): ?>
        <div class="notice error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <form method="post">
        <div class="form-row">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required>
        </div>
        <button class="button" type="submit">Save</button>
        <a href="index.php" class="button secondary">Cancel</a>
    </form>
</div>
<?php require_once 'footer.php'; ?>