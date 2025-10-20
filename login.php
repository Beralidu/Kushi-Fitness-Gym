<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];

        // Redirect based on role
        if ($user['role'] == 'trainer') {
            header("Location: trainer_dashboard.php");
        } elseif ($user['role'] == 'admin') {
            header("Location: admin.html");
        } else {
            header("Location: admin.html");
            // print "Hello Member : " . $_SESSION['name'];
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Log In - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <section id="login-hero">
            <h2>Welcome Back</h2>
            <p>Log in to your account to access your dashboard and manage your fitness journey.</p>
        </section>
        <section id="login-form-section">
            <div class="login-container">
                <h2>Log In</h2>
                <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Log In</button>
                </form>
                <p class="signup-link">Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>