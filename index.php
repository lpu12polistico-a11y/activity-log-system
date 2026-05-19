<?php
session_start();
require 'config/database.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['username'])) {
    header("Location: auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/
$search = trim($_GET['search'] ?? '');

/*
|--------------------------------------------------------------------------
| FETCH COURSES
|--------------------------------------------------------------------------
*/
$courseQuery = "
    SELECT *
    FROM courses
    WHERE course_name LIKE :search
       OR course_description LIKE :search
    ORDER BY course_name ASC
";

$courseStmt = $conn->prepare($courseQuery);

$courseStmt->execute([
    ':search' => "%{$search}%"
]);

$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| FETCH CLASSES
|--------------------------------------------------------------------------
*/
$classQuery = "
    SELECT
        classes.*,
        courses.course_name
    FROM classes
    INNER JOIN courses
        ON classes.course_id = courses.course_id
    WHERE classes.section_name LIKE :search
       OR classes.start_time LIKE :search
       OR classes.room LIKE :search
       OR courses.course_name LIKE :search
    ORDER BY courses.course_name ASC
";

$classStmt = $conn->prepare($classQuery);

$classStmt->execute([
    ':search' => "%{$search}%"
]);

$classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ACTIVITY LOG
|--------------------------------------------------------------------------
*/
if (!empty($search)) {
    logActivity(
        $conn,
        $_SESSION['username'],
        "READ",
        "Search",
        "User searched for: {$search}"
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Activity Log System</title>

    <link rel="stylesheet" href="style.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .navbar {
            background: #1e293b;
            color: white;
            padding: 15px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 22px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar a {
            text-decoration: none;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .navbar a:hover {
            background: rgba(255,255,255,0.15);
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        h2 {
            margin-bottom: 20px;
            color: #1e293b;
        }

        form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="text"] {
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button,
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            display: inline-block;
        }

        button {
            background: #2563eb;
        }

        button:hover {
            background: #1d4ed8;
        }

        .btn {
            background: #2563eb;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .edit {
            background: #f59e0b;
        }

        .delete {
            background: #dc2626;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .top-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 10px;
        }

        table th {
            background: #1e293b;
            color: white;
            padding: 14px;
            text-align: left;
        }

        table td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
        }

        table tr:hover {
            background: #f8fafc;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #777;
        }

        @media (max-width: 768px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            form {
                flex-direction: column;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <div class="navbar">
        <h1>Activity Log System</h1>

        <div class="navbar-right">
            <span>
                Logged in as:
                <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>
            </span>

            <a href="index.php">Home</a>
            <a href="activity_logs.php">Activity Logs</a>
            <a href="auth/logout.php">Logout</a>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="container">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success">
                <?= htmlspecialchars($_SESSION['success']); ?>
            </div>

            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- SEARCH -->
        <div class="card">
            <h2>Search</h2>

            <form method="GET">
                <input
                    type="text"
                    name="search"
                    placeholder="Search courses or classes..."
                    value="<?= htmlspecialchars($search); ?>"
                >

                <button type="submit">Search</button>
            </form>

            <div class="top-buttons">
                <a class="btn" href="courses/add_course.php">
                    + Add Course
                </a>

                <a class="btn" href="classes/add_class.php">
                    + Add Class
                </a>
            </div>
        </div>

        <!-- COURSES -->
        <div class="card">
            <h2>Courses</h2>

            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($courses) > 0): ?>

                        <?php foreach ($courses as $course): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($course['course_name']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($course['course_description']); ?>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            class="btn edit"
                                            href="courses/edit_course.php?id=<?= $course['course_id']; ?>"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            class="btn delete"
                                            href="courses/delete_course.php?id=<?= $course['course_id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this course?')"
                                        >
                                            Delete
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="3" class="empty-message">
                                No courses found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>

        <!-- CLASSES -->
        <div class="card">
            <h2>Classes</h2>

            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Start Time</th>
                        <th>Room</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($classes) > 0): ?>

                        <?php foreach ($classes as $class): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($class['course_name']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($class['section_name']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($class['start_time']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($class['room']); ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            class="btn edit"
                                            href="classes/edit_class.php?id=<?= $class['class_id']; ?>"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            class="btn delete"
                                            href="classes/delete_class.php?id=<?= $class['class_id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this class?')"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="empty-message">
                                No classes found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>

</body>
</html>