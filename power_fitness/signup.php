<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = trim($_POST['phone']);

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    // Check if email exists
    $check = $conn->prepare("SELECT * FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name,email,password_hash,phone,role) VALUES (?, ?, ?, ?, 'member')");
        $stmt->bind_param("ssss", $name, $email, $password, $phone);
        if ($stmt->execute()) {
            // Redirect to login to avoid form resubmission
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
    <title>Sign Up - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section id="signup-hero">
            <h2>Join Our Community</h2>
            <p>Create your account to start your fitness journey with us. It only takes a minute!</p>
        </section>
        <section id="signup-form-section">
            <div class="signup-container">
                <h2>Create Your Account</h2>
                <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Create Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <button type="submit">Sign Up</button>
                </form>
                <p class="login-link">Already have an account? <a href="login.php">Log In</a></p>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
