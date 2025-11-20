<?php
require_once 'db_connect.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Enter username and password';
    } else {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['password'])) {
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $u['role'];
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials';
        }
    }
}
include 'header.php';
?>
<div class="card">
  <h1>Login</h1>
  <?php foreach ($errors as $e): ?>
    <div class="notice"><?=htmlspecialchars($e)?></div>
  <?php endforeach; ?>
  <form method="post" action="login.php">
    <div class="form-row">
      <label>Username</label>
      <input name="username" required />
    </div>
    <div class="form-row">
      <label>Password</label>
      <input type="password" name="password" required />
    </div>
    <button class="button" type="submit">Login</button>
  </form>
  <p class="small">No account? <a href="register.php">Register</a></p>
</div>
<?php include 'footer.php'; ?>
