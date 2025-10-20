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
        .admin-page {
            max-width: 900px;
            margin: 32px auto;
            padding: 32px 18px;
            background: linear-gradient(180deg, #fff, #f4f6fb);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(11,30,60,0.08);
            font-family: Inter, system-ui, Arial, sans-serif;
        }
        h2, h3 {
            color: #0b6cff;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .form-section {
            background: #f8fafc;
            padding: 22px 18px;
            border-radius: 12px;
            margin-bottom: 32px;
            box-shadow: 0 2px 10px rgba(11,30,60,0.06);
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #0f172a;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            font-size: 1em;
            background: #fff;
            margin-bottom: 2px;
        }
        button {
            background: #0b6cff;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(11,30,60,0.06);
            transition: background 0.15s;
        }
        button:hover {
            background: #2563eb;
        }
        .view-section {
            background: #f8fafc;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(11,30,60,0.06);
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 18px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(11,30,60,0.04);
        }
        th {
            background: #0b6cff;
            color: #fff;
            font-weight: 600;
            padding: 12px 8px;
            border-bottom: 2px solid #2563eb;
        }
        td {
            padding: 12px 8px;
            text-align: center;
            color: #222;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) td {
            background: #f4f6fb;
        }
        .status-paid {
            color: #16a34a;
            font-weight: bold;
        }
        .status-pending {
            color: #e11d48;
            font-weight: bold;
        }
        @media (max-width: 700px) {
            .admin-page, .form-section, .view-section {
                padding: 10px;
            }
            table, th, td {
                font-size: 0.95em;
            }
        }
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
