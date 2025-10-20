<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: login.php");
    exit();
}

$trainer_id = $_SESSION['user_id']; // Assuming trainers use their user_id

$stmt = $conn->prepare("SELECT ta.member_id AS member_id, u.full_name, u.email, u.phone
                        FROM trainer_assignments ta
                        JOIN users u ON ta.member_id = u.user_id
                        WHERE ta.trainer_id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section id="trainer-hero">
            <h2>Trainer Dashboard</h2>
            <p>Manage your assigned members below:</p>
        </section>
        <section id="member-list">
            <h2>Assigned Member List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><a href="update_progress.php?member_id=<?php echo $row['member_id']; ?>" class="action-button">Update Progress</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
