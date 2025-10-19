<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Attendance Reports</h2>
            <p>Generate and view attendance reports. This placeholder should be wired to your attendance/progress tables.</p>

            <div class="reports-placeholder">
                <!-- TODO: report generation UI -->
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
