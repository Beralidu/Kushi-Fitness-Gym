<?php if (session_status() == PHP_SESSION_NONE) { session_start(); } ?>
<header>
    <h1>Kushi Fitness Gym</h1>
    <nav>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="trainer_dashboard.php">Trainer</a></li>
            <li><a href="admin_dashboard.php">Admin</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="help.html">Help</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php">Log Out</a></li>
            <?php else: ?>
                <li><a href="signup.php">Sign Up</a></li>
                <li><a href="login.php">Log In</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
