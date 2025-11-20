<?php
require_once 'db_connect.php';
include 'header.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$uid = (int)$_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $uid]);
$cat = $stmt->fetch();
if (!$cat) { echo '<div class="card"><p>Category not found</p></div>'; include 'footer.php'; exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') $errors[] = 'Name required';
    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $id, $uid]);
        header('Location: index.php');
        exit;
    }
}
?>
<div class="card">
  <h1>Edit Category</h1>
  <?php foreach ($errors as $e): ?><div class="notice"><?=htmlspecialchars($e)?></div><?php endforeach; ?>
  <form method="post">
    <div class="form-row">
      <label>Name</label>
      <input name="name" value="<?=htmlspecialchars($cat['name'])?>" required />
    </div>
    <button class="button" type="submit">Update</button>
    <a href="index.php" class="button secondary">Cancel</a>
  </form>
</div>
<?php include 'footer.php'; ?>
