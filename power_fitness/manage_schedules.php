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
    <title>Schedules & Classes - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Schedules & Classes</h2>
            <p>Create, update, and manage class schedules and instructor assignments from this page.</p>

            <div class="schedules-placeholder">
                <!-- TODO: class schedule UI -->
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
