<?php
$host = "127.0.0.1";
$dbname = "activity_log_system";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function logActivity($conn, $username, $action_type, $entity_type, $description) {
    $stmt = $conn->prepare("
        INSERT INTO activity_logs (username, action_type, entity_type, description)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$username, $action_type, $entity_type, $description]);
}
?>