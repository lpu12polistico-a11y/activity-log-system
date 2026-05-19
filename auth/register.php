<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);

            $_SESSION['success'] = "Registration successful. You may now login.";
            header("Location: login.php");
            exit;
        } catch(PDOException $e) {
            $error = "Username already exists.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<link rel="stylesheet" href="../style.css">

<div class="container">
    <h2>User Registration</h2>

    <?php if(isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>