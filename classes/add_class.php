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
| FETCH COURSES
|--------------------------------------------------------------------------
*/
$coursesQuery = "SELECT * FROM courses ORDER BY course_name ASC";
$coursesStmt = $conn->prepare($coursesQuery);
$coursesStmt->execute();

$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| INSERT CLASS
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $course_id = $_POST['course_id'];
    $section_name = trim($_POST['section_name']);
    $start_time = trim($_POST['start_time']);
    $room = trim($_POST['room']);

    /*
    |--------------------------------------------------------------------------
    | INSERT QUERY
    |--------------------------------------------------------------------------
    */
    $insertQuery = "
        INSERT INTO classes
        (course_id, section_name, start_time, room)
        VALUES (?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insertQuery);

    $stmt->execute([
        $course_id,
        $section_name,
        $start_time,
        $room
    ]);

    /*
    |--------------------------------------------------------------------------
    | FETCH COURSE NAME FOR ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    $courseQuery = "
        SELECT course_name
        FROM courses
        WHERE course_id = ?
    ";

    $courseStmt = $conn->prepare($courseQuery);
    $courseStmt->execute([$course_id]);

    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    logActivity(
        $conn,
        $_SESSION['username'],
        "CREATE",
        "Class",
        "User " . $_SESSION['username'] .
        " inserted class " . $section_name .
        " under the course " . $course['course_name'] . "."
    );

    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */
    $_SESSION['success'] = "Class added successfully.";

    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Class</title>

    <link rel="stylesheet" href="../style.css">

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 20px;
            color: #1e293b;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .buttons {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        button,
        .btn {
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            color: white;
            transition: 0.3s;
        }

        button {
            background: #2563eb;
        }

        button:hover {
            background: #1d4ed8;
        }

        .btn {
            background: #64748b;
        }

        .btn:hover {
            background: #475569;
        }

    </style>
</head>

<body>

    <div class="container">

        <div class="card">

            <h2>Add Class</h2>

            <form method="POST">

                <!-- COURSE -->
                <label>Course</label>

                <select name="course_id" required>

                    <option value="">
                        -- Select Course --
                    </option>

                    <?php foreach($courses as $course): ?>

                        <option value="<?= $course['course_id']; ?>">

                            <?= htmlspecialchars($course['course_name']); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <!-- SECTION -->
                <label>Section Name</label>

                <input
                    type="text"
                    name="section_name"
                    placeholder="Enter section name"
                    required
                >

                <!-- START TIME -->
                <label>Start Time</label>

                <input
                    type="text"
                    name="start_time"
                    placeholder="Example: 8:00 AM"
                    required
                >

                <!-- ROOM -->
                <label>Room</label>

                <input
                    type="text"
                    name="room"
                    placeholder="Enter room"
                    required
                >

                <!-- BUTTONS -->
                <div class="buttons">

                    <button type="submit">
                        Save Class
                    </button>

                    <a href="../index.php" class="btn">
                        Back
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>
</html>