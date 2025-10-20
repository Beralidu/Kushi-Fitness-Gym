<?php
// at top of manage_members.php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}
include 'db.php';


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
            <th>ID</th>
            <th>Full Name</th>
            <th>Address</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Weight (kg)</th>
            <th>Height (cm)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $stmt = $conn->prepare("SELECT user_id, full_name, address, email, phone, weight, height FROM users WHERE role = 'member'");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
    ?>
        <tr>
            <td><?= htmlspecialchars($row['user_id']); ?></td>
            <td><?= htmlspecialchars($row['full_name']); ?></td>
            <td><?= htmlspecialchars($row['address'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= htmlspecialchars($row['phone']); ?></td>
            <td><?= htmlspecialchars($row['weight'] ?: '-'); ?></td>
            <td><?= htmlspecialchars($row['height'] ?: '-'); ?></td>
            <td class="action-buttons">
                <a href="signup.php?id=<?= $row['user_id']; ?>" class="edit-btn">Edit</a>
            </td>
        </tr>
    <?php
        endwhile;
    else:
        echo '<tr><td colspan="8" class="no-data">No members found.</td></tr>';
    endif;
    $stmt->close();
    ?>
    </tbody>
</table>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
