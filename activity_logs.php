<?php
session_start();
require 'config/database.php';

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['username'])) {
    header("Location: auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH ACTIVITY LOGS
|--------------------------------------------------------------------------
*/
$query = "
    SELECT *
    FROM activity_logs
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs | Activity Log System</title>

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

        /*
        |--------------------------------------------------------------------------
        | NAVBAR
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | CONTAINER
        |--------------------------------------------------------------------------
        */

        .container {
            width: 95%;
            max-width: 1300px;
            margin: 30px auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 15px;
            color: #1e293b;
        }

        .description {
            margin-bottom: 20px;
            color: #555;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
        }

        table th {
            background: #1e293b;
            color: white;
            padding: 14px;
            text-align: left;
            font-size: 14px;
        }

        table td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
            font-size: 14px;
        }

        table tr:hover {
            background: #f8fafc;
        }

        /*
        |--------------------------------------------------------------------------
        | BADGES
        |--------------------------------------------------------------------------
        */

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .insert {
            background: #16a34a;
        }

        .update {
            background: #f59e0b;
        }

        .delete {
            background: #dc2626;
        }

        .read {
            background: #2563eb;
        }

        /*
        |--------------------------------------------------------------------------
        | EMPTY MESSAGE
        |--------------------------------------------------------------------------
        */

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #777;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
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

        <h1>Activity Logs</h1>

        <div class="navbar-right">

            <span>
                Logged in as:
                <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>
            </span>

            <a href="index.php">Home</a>
            <a href="auth/logout.php">Logout</a>

        </div>

    </div>

    <!-- MAIN CONTAINER -->
    <div class="container">

        <div class="card">

            <h2>System Activity Logs</h2>

            <p class="description">
                This page records all CRUD operations performed in the system.
                Activity logs cannot be edited or deleted to maintain
                data integrity, accountability, and security.
            </p>

            <table>

                <thead>
                    <tr>
                        <th width="120">Username</th>
                        <th width="120">Action Type</th>
                        <th width="150">Entity Type</th>
                        <th>Description</th>
                        <th width="180">Date & Time</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($logs) > 0): ?>

                        <?php foreach ($logs as $log): ?>

                            <tr>

                                <!-- USERNAME -->
                                <td>
                                    <?= htmlspecialchars($log['username']); ?>
                                </td>

                                <!-- ACTION TYPE -->
                                <td>

                                    <?php
                                    $action = strtoupper($log['action_type']);

                                    $badgeClass = '';

                                    switch ($action) {
                                        case 'INSERT':
                                            $badgeClass = 'insert';
                                            break;

                                        case 'UPDATE':
                                            $badgeClass = 'update';
                                            break;

                                        case 'DELETE':
                                            $badgeClass = 'delete';
                                            break;

                                        default:
                                            $badgeClass = 'read';
                                    }
                                    ?>

                                    <span class="badge <?= $badgeClass; ?>">
                                        <?= htmlspecialchars($action); ?>
                                    </span>

                                </td>

                                <!-- ENTITY TYPE -->
                                <td>
                                    <?= htmlspecialchars($log['entity_type']); ?>
                                </td>

                                <!-- DESCRIPTION -->
                                <td>
                                    <?= htmlspecialchars($log['description']); ?>
                                </td>

                                <!-- DATE -->
                                <td>
                                    <?= date(
                                        "F d, Y h:i A",
                                        strtotime($log['created_at'])
                                    ); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="empty-message">
                                No activity logs found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</body>
</html>