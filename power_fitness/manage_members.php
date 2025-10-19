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
    <title>Manage Members - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Manage Members</h2>
            <p>View, add, edit, and remove members. This page is a placeholder UI — connect to your `users` table to populate data.</p>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Full Name</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Weight</th>
                        <th>Height</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Fetch all members (role = 'member')
                $stmt = $conn->prepare("SELECT user_id, full_name, address, email, phone, weight, height FROM users WHERE role = 'member'");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['weight'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['height'] ?? ''); ?></td>
                        <td><!-- Actions: Edit/Delete can go here --></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
