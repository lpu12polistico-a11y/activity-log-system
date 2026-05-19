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
| INSERT COURSE
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $course_name = trim($_POST['course_name']);
    $course_description = trim($_POST['course_description']);

    /*
    |--------------------------------------------------------------------------
    | INSERT QUERY
    |--------------------------------------------------------------------------
    */
    $insertQuery = "
        INSERT INTO courses
        (course_name, course_description)
        VALUES (?, ?)
    ";

    $stmt = $conn->prepare($insertQuery);

    $stmt->execute([
        $course_name,
        $course_description
    ]);

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    logActivity(
        $conn,
        $_SESSION['username'],
        "CREATE",
        "Course",
        "User " . $_SESSION['username'] .
        " inserted a new course named " . $course_name . "."
    );

    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */
    $_SESSION['success'] = "Course added successfully.";

    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Course</title>

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
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            resize: vertical;
        }

        textarea {
            min-height: 120px;
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

            <h2>Add Course</h2>

            <form method="POST">

                <!-- COURSE NAME -->
                <label>Course Name</label>

                <input
                    type="text"
                    name="course_name"
                    placeholder="Enter course name"
                    required
                >

                <!-- COURSE DESCRIPTION -->
                <label>Course Description</label>

                <textarea
                    name="course_description"
                    placeholder="Enter course description"
                    required
                ></textarea>

                <!-- BUTTONS -->
                <div class="buttons">

                    <button type="submit">
                        Save Course
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