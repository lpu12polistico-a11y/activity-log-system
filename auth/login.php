<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];

        logActivity($conn, $user['username'], "READ", "User", "User " . $user['username'] . " logged in to the system.");

        header("Location: ../index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<link rel="stylesheet" href="../style.css">

<div class="container">
    <h2>User Login</h2>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p>No account yet? <a href="register.php">Register here</a></p>
</div>