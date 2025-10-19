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
    <title>Manage Payments - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Manage Payments</h2>
            <p>Track and manage member payments. Connect this UI to your payments table to enable billing operations.</p>

            <div class="payments-placeholder">
                <!-- TODO: list payments and actions -->
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
