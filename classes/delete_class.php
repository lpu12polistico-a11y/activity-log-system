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

$class_id = $_GET['id'];

/*
|--------------------------------------------------------------------------
| FETCH CLASS INFORMATION
|--------------------------------------------------------------------------
*/
$classQuery = "
    SELECT
        classes.*,
        courses.course_name
    FROM classes
    INNER JOIN courses
        ON classes.course_id = courses.course_id
    WHERE classes.class_id = ?
";

$classStmt = $conn->prepare($classQuery);
$classStmt->execute([$class_id]);

$class = $classStmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| DELETE CLASS
|--------------------------------------------------------------------------
*/
if ($class) {

    /*
    |--------------------------------------------------------------------------
    | DELETE QUERY
    |--------------------------------------------------------------------------
    */
    $deleteQuery = "
        DELETE FROM classes
        WHERE class_id = ?
    ";

    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->execute([$class_id]);

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    logActivity(
        $conn,
        $_SESSION['username'],
        "DELETE",
        "Class",
        "User " . $_SESSION['username'] .
        " deleted class " . $class['section_name'] .
        " under the course " . $class['course_name'] . "."
    );

    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */
    $_SESSION['success'] = "Class deleted successfully.";
}

/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/
header("Location: ../index.php");
exit();
?>