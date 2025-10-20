<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: login.php");
    exit();
}

$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;
if ($member_id <= 0) {
    echo "Invalid member specified.";
    exit();
}

// Retrieve trainer_id (assuming trainers table maps user_id to trainer_id)
$trainer_stmt = $conn->prepare("SELECT trainer_id FROM trainers WHERE user_id = ?");
$trainer_stmt->bind_param("i", $_SESSION['user_id']);
$trainer_stmt->execute();
$trainer_res = $trainer_stmt->get_result();
if ($trainer_row = $trainer_res->fetch_assoc()) {
    $trainer_id = (int)$trainer_row['trainer_id'];
} else {
    echo "Trainer record not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

    // Validate status
    $allowed = ['on-track', 'exceeding', 'needs-attention'];
    if (!in_array($status, $allowed)) {
        $status = 'on-track';
    }

    // Check if progress exists
    $check_stmt = $conn->prepare("SELECT id FROM progress WHERE member_id = ? AND trainer_id = ?");
    $check_stmt->bind_param("ii", $member_id, $trainer_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();

    if ($check_res->num_rows > 0) {
        // Update
        $update_stmt = $conn->prepare("UPDATE progress SET status = ?, notes = ?, updated_at = NOW() WHERE member_id = ? AND trainer_id = ?");
        $update_stmt->bind_param("ssii", $status, $notes, $member_id, $trainer_id);
        $update_stmt->execute();
    } else {
        // Insert
        $insert_stmt = $conn->prepare("INSERT INTO progress (member_id, trainer_id, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $insert_stmt->bind_param("iiss", $member_id, $trainer_id, $status, $notes);
        $insert_stmt->execute();
    }
    $message = "Progress updated successfully!";
}
?>

<?php if (isset($message)) echo "<p style='color:green;'>" . htmlspecialchars($message) . "</p>"; ?>

<form method="POST">
    <label>Status:</label>
    <select name="status">
        <option value="on-track">On Track</option>
        <option value="exceeding">Exceeding</option>
        <option value="needs-attention">Needs Attention</option>
    </select><br><br>
    <label>Notes:</label><br>
    <textarea name="notes" rows="5" cols="50"></textarea><br><br>
    <button type="submit">Update Progress</button>
</form>
