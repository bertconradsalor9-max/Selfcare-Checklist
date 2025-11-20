<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$categoryId = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$categoryId, $userId]);
} catch (PDOException $e) {
    // Log error if needed
}

header('Location: index.php');
exit;