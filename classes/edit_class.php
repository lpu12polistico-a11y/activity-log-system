<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$class_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM classes WHERE class_id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

$courses = $conn->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $section_name = trim($_POST['section_name']);
    $start_time = trim($_POST['start_time']);
    $room = trim($_POST['room']);

    $stmt = $conn->prepare("
        UPDATE classes 
        SET course_id = ?, section_name = ?, start_time = ?, room = ?
        WHERE class_id = ?
    ");
    $stmt->execute([$course_id, $section_name, $start_time, $room, $class_id]);

    logActivity($conn, $_SESSION['username'], "UPDATE", "Class", "User " . $_SESSION['username'] . " updated class " . $section_name . ".");

    $_SESSION['success'] = "Class updated successfully.";
    header("Location: ../index.php");
    exit;
}
?>

<link rel="stylesheet" href="../style.css">

<div class="container">
    <h2>Edit Class</h2>

    <form method="POST">
        <label>Course</label>
        <select name="course_id" required>
            <?php foreach($courses as $course): ?>
                <option value="<?= $course['course_id']; ?>" 
                    <?= $course['course_id'] == $class['course_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($course['course_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Section Name</label>
        <input type="text" name="section_name" value="<?= htmlspecialchars($class['section_name']); ?>" required>

        <label>Start Time</label>
        <input type="text" name="start_time" value="<?= htmlspecialchars($class['start_time']); ?>" required>

        <label>Room</label>
        <input type="text" name="room" value="<?= htmlspecialchars($class['room']); ?>" required>

        <button type="submit">Update Class</button>
        <a href="../index.php">Back</a>
    </form>
</div>