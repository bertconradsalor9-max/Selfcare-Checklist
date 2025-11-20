<?php
require_once 'db_connect.php';
include 'header.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$uid = (int)$_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $uid]);
$act = $stmt->fetch();
if (!$act) { echo '<div class="card"><p>Activity not found</p></div>'; include 'footer.php'; exit; }

$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ?");
$stmt->execute([$uid]);
$cats = $stmt->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $time = $_POST['time'] ?? '';
    $day = (int)($_POST['day'] ?? 0);
    $month = (int)($_POST['month'] ?? 0);

    if ($name === '' || !$category_id || !$time || !$day || !$month) $errors[] = 'All fields required';
    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE activities SET category_id = ?, name = ?, description = ?, time = ?, day = ?, month = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$category_id, $name, $desc, $time, $day, $month, $id, $uid]);
        header('Location: index.php');
        exit;
    }
}
?>
<div class="card">
  <h1>Edit Activity</h1>
  <?php foreach ($errors as $e): ?><div class="notice"><?=htmlspecialchars($e)?></div><?php endforeach; ?>
  <form method="post">
    <div class="form-row"><label>Name</label><input name="name" value="<?=htmlspecialchars($act['name'])?>" required /></div>
    <div class="form-row"><label>Description</label><textarea name="description"><?=htmlspecialchars($act['description'])?></textarea></div>
    <div class="form-row"><label>Category</label>
      <select name="category_id" required>
        <option value="">-- choose --</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?=$c['id']?>" <?=($c['id']==$act['category_id'])?'selected':''?>><?=htmlspecialchars($c['name'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row"><label>Time</label><input type="time" name="time" value="<?=htmlspecialchars($act['time'])?>" required /></div>
    <div class="form-row"><label>Day (1-31)</label><input type="number" name="day" min="1" max="31" value="<?=htmlspecialchars($act['day'])?>" required /></div>
    <div class="form-row"><label>Month (1-12)</label><input type="number" name="month" min="1" max="12" value="<?=htmlspecialchars($act['month'])?>" required /></div>
    <button class="button" type="submit">Update</button>
    <a href="index.php" class="button secondary">Cancel</a>
  </form>
</div>
<?php include 'footer.php'; ?>
