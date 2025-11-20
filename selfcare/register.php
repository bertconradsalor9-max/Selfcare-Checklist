<?php
require_once 'db_connect.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields required';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match';
    } else {
        // check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hash]);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        }
    }
}
include 'header.php';
?>
<div class="card">
  <h1>Register</h1>
  <?php foreach ($errors as $e): ?>
    <div class="notice"><?=htmlspecialchars($e)?></div>
  <?php endforeach; ?>
  <form method="post" action="register.php">
    <div class="form-row">
      <label>Username</label>
      <input name="username" required />
    </div>
    <div class="form-row">
      <label>Password</label>
      <input type="password" name="password" required />
    </div>
    <div class="form-row">
      <label>Confirm Password</label>
      <input type="password" name="confirm" required />
    </div>
    <button class="button" type="submit">Register</button>
  </form>
  <p class="small">Already have account? <a href="login.php">Login</a></p>
</div>
<?php include 'footer.php'; ?>
