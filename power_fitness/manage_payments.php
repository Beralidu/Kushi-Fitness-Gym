<?php
session_start();

// TEMPORARY ADMIN ACCESS (remove later when login system ready)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}

include 'db.php';

// Handle Add Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $member_id = $_POST['member_id'];
    $package_type = $_POST['package_type'];
    $package_duration = $_POST['package_duration'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];

    // Prevent duplicate entry for same member + month + year
    $check = $conn->prepare("SELECT * FROM payments WHERE user_id=? AND month=? AND year=?");
    $check->bind_param("isi", $member_id, $month, $year);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO payments (user_id, package_type, package_duration, month, year, amount, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiss", $member_id, $package_type, $package_duration, $month, $year, $amount, $status);
        $stmt->execute();
        echo "<script>alert('Payment added successfully!');</script>";
        $stmt->close();
    } else {
        echo "<script>alert('Payment already exists for this month and year.');</script>";
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-page { padding: 30px; }
        h2, h3 { color: #333; margin-bottom: 10px; }
        .form-section { background: #f9f9f9; padding: 15px; border-radius: 10px; margin-bottom: 25px; }
        .form-group { margin-bottom: 10px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, button { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Manage Payments</h2>
            <p>Track and manage member payments with package details.</p>

            <!-- Add Payment Section -->
            <div class="form-section">
                <h3>Add New Payment</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="member_id">Select Member:</label>
                        <select name="member_id" required>
                            <option value="">-- Select Member --</option>
                            <?php
                            $members = $conn->query("SELECT user_id, full_name FROM users WHERE role='member'");
                            while ($m = $members->fetch_assoc()) {
                                echo "<option value='{$m['user_id']}'>{$m['full_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="package_type">Package Type:</label>
                        <select name="package_type" id="package_type" required>
                            <option value="">-- Select Package --</option>
                            <option value="Trainee and schedule">Trainee and schedule</option>
                            <option value="Schedule and Diet plan">Schedule and Diet plan</option>
                            <option value="Diet plan and Cardio">Diet plan and Cardio</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="package_duration">Package Duration:</label>
                        <select name="package_duration" id="package_duration" required>
                            <option value="">-- Select Duration --</option>
                            <option value="Per month">Per month</option>
                            <option value="3 months">3 months</option>
                            <option value="6 months">6 months</option>
                            <option value="1 year">1 year</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="month">Month:</label>
                        <select name="month" required>
                            <?php
                            $months = [
                                "January","February","March","April","May","June",
                                "July","August","September","October","November","December"
                            ];
                            foreach ($months as $month) {
                                echo "<option value='$month'>$month</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Year:</label>
                        <input type="number" name="year" min="2020" max="2100" value="<?= date('Y'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="amount">Amount (LKR):</label>
                        <input type="number" name="amount" id="amount" step="0.01" required readonly>
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select name="status" required>
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <button type="submit" name="add_payment">Add Payment</button>
                </form>
            </div>

<!-- View Payments -->
<div class="view-section">
    <h3>All Payment Records</h3>
    <table>
        <thead>
            <tr>
                <th>Member</th>
                <th>Package Details</th>
                <th>Period</th>
                <th>Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT p.*, u.full_name 
                      FROM payments p 
                      JOIN users u ON p.user_id = u.user_id 
                      ORDER BY p.year DESC, FIELD(p.month, 
                        'January','February','March','April','May','June',
                        'July','August','September','October','November','December')";
            $payments = $conn->query($query);

            if ($payments->num_rows > 0) {
                while ($pay = $payments->fetch_assoc()) {
                    echo "<tr>
                        <td><strong>{$pay['full_name']}</strong></td>
                        <td>
                            <b>{$pay['package_type']}</b><br>
                            <small>Duration: {$pay['package_duration']}</small>
                        </td>
                        <td>{$pay['month']} {$pay['year']}</td>
                        <td><b>{$pay['amount']}</b></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No payment records yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

        </section>
    </main>

    <script>
    const packagePrices = {
        "Trainee and schedule": {
            "Per month": 5000,
            "6 months": 25000,
            "1 year": 55000
        },
        "Schedule and Diet plan": {
            "Per month": 3000,
            "6 months": 12500,
            "1 year": 40000
        },
        "Diet plan and Cardio": {
            "Per month": 2000,
            "3 months": 7000,
            "6 months": 15000
        }
    };

    document.getElementById('package_type').addEventListener('change', updateAmount);
    document.getElementById('package_duration').addEventListener('change', updateAmount);

    function updateAmount() {
        const type = document.getElementById('package_type').value;
        const duration = document.getElementById('package_duration').value;
        const amountField = document.getElementById('amount');

        if (type && duration && packagePrices[type][duration]) {
            amountField.value = packagePrices[type][duration];
        } else {
            amountField.value = '';
        }
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
