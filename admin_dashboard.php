<?php
session_start();

// Redirect to login page if user not logged in
if (!isset($_SESSION['name'])) {
    header("Location: admin.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 80px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #333;
        }
        .welcome {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }
        .logout {
            display: inline-block;
            background: #007BFF;
            color: #fff;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .logout:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Dashboard</h1>
    <div class="welcome">
        <?php echo "Hello Admin : " . htmlspecialchars($_SESSION['name']); ?>
    </div>

    <p>Welcome to your Admin dashboard! Here you can manage your profile, view updates, and access exclusive content.</p>

    <a href="logout.php" class="logout">Logout</a>
</div>

</body>
</html>
