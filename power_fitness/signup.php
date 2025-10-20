<?php
session_start();
include 'db.php';

$editing = false;
if (isset($_GET['id'])) {
    $editing = true;
    $id = $_GET['id'];
    // Fetch existing member data
    $stmt = $conn->prepare("SELECT full_name, email, phone, address, weight, height FROM users WHERE user_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : NULL;
    $height = !empty($_POST['height']) ? (float)$_POST['height'] : NULL;

    if ($editing) {
        // Update existing member
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=?, weight=?, height=? WHERE user_id=?");
        $stmt->bind_param("ssssddi", $name, $email, $phone, $address, $weight, $height, $id);
        if ($stmt->execute()) {
            header("Location: manage_members.php?update=success");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    } else {
        // New member signup (insert) logic here
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (full_name,email,password_hash,phone,role,address,weight,height) VALUES (?, ?, ?, ?, 'member', ?, ?, ?)");
        $stmt->bind_param("ssssddd", $name, $email, $password, $phone, $address, $weight, $height);
        if ($stmt->execute()) {
            header("Location: login.php?signup=success");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Edit Member' : 'Sign Up'; ?> - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section id="signup-hero">
            <h2><?= $editing ? 'Edit Member' : 'Join POWER FITNESS'; ?></h2>
            <p><?= $editing ? 'Update member details below.' : 'Create your account to start your fitness journey with us!'; ?></p>
        </section>
        <section id="signup-form-section">
            <div class="signup-container">
                <h2><?= $editing ? 'Edit Member' : 'Sign Up'; ?></h2>
                <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <form method="POST">
                    <div class="form-group
">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?= $editing ? htmlspecialchars($member['full_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">    
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" value="<?= $editing ? htmlspecialchars($member['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="text" id="phone" name="phone" value="<?= $editing ? htmlspecialchars($member['phone']) : ''; ?>" required>
                    </div>
                    <div class="form-group
">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" value="<?= $editing ? htmlspecialchars($member['address']) : ''; ?>"> 
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (kg):</label>
                        <input type="number" step="0.1" id="weight" name="weight" value="<?= $editing ? htmlspecialchars($member['weight']) : ''; ?>">  
                    </div>
                    <div class="form-group  
">
                        <label for="height">Height (cm):</label>
                        <input type="number" step="0.1" id="height" name="height" value="<?= $editing ? htmlspecialchars($member['height']) : ''; ?>">
                    </div>
                    <?php if (!$editing): ?>    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <?php endif; ?> 
                    <button type="submit"><?= $editing ? 'Update Member' : 'Sign Up'; ?></button>
                </form>
            </div>
        </section>  
    </main>
    <?php include 'footer.php'; ?>  
</body>
</html> 

