<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie params
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => $cookieParams["path"],
        'domain' => $cookieParams["domain"],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Helper functions
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Self-Care Checklist</title>
    <link rel="stylesheet" href="asset/css/style.css">
</head>
<body>
<header class="header">
    <div class="container">
        <a href="index.php" class="brand"></a>
        <div class="topbar">
    <div class="container">
        <a href="index.php" class="logo-img">
            <img src="asset/img/logo.png" class="logo-img">

        </a>
        <nav class="nav">
            <?php if (is_logged_in()): ?>
                <a href="index.php">Dashboard</a>
                <a href="tracker.php">Tracker</a>
                <?php if (is_admin()): ?>
                    <a href="admin_dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="admin_login.php">Admin Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main">
    <div class="container">