<?php
session_start();
require '../config/database.php';

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| CHECK IF ID EXISTS
|--------------------------------------------------------------------------
*/
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$course_id = $_GET['id'];

/*
|--------------------------------------------------------------------------
| FETCH COURSE INFORMATION
|--------------------------------------------------------------------------
*/
$courseQuery = "
    SELECT *
    FROM courses
    WHERE course_id = ?
";

$courseStmt = $conn->prepare($courseQuery);
$courseStmt->execute([$course_id]);

$course = $courseStmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| DELETE COURSE
|--------------------------------------------------------------------------
*/
if ($course) {

    /*
    |--------------------------------------------------------------------------
    | DELETE QUERY
    |--------------------------------------------------------------------------
    */
    $deleteQuery = "
        DELETE FROM courses
        WHERE course_id = ?
    ";

    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->execute([$course_id]);

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    logActivity(
        $conn,
        $_SESSION['username'],
        "DELETE",
        "Course",
        "User " . $_SESSION['username'] .
        " deleted the course " . $course['course_name'] . "."
    );

    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */
    $_SESSION['success'] = "Course deleted successfully.";
}

/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/
header("Location: ../index.php");
exit();
?>