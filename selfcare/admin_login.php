<?php
require_once 'db_connect.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Enter credentials.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
                session_start();
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'admin';
                session_regenerate_id(true);
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $errors[] = 'Invalid admin credentials.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error.';
        }
    }
}

require_once 'header.php';
?>
<div class="card">
    <h1>Admin Login</h1>
    <?php foreach ($errors as $error): ?>
        <div class="notice error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <form method="post">
        <div class="form-row">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required>
        </div>
        <div class="form-row">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button class="button" type="submit">Admin Login</button>
    </form>
</div>
<?php require_once 'footer.php'; ?>