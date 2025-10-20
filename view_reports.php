<?php
session_start();
include 'db.php';
// Instead of redirecting, we'll check if the user is not logged in or not an admin
// and show an error message while staying on the current page
$isAuthorized = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'member';
// If it's an AJAX request or form submission, return JSON response
if (!$isAuthorized && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Add this JavaScript to handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('form')) {
                document.querySelector('form').addEventListener('submit', function(e) {
                    if (!<?php echo $isAuthorized ? 'true' : 'false'; ?>) {
                        e.preventDefault();
                        alert('Please log in as an admin to perform this action.');
                        return false;
                    }
                });
            }
        });
    </script>
</head>
<body>
    <?php if (!$isAuthorized): ?>
    <div class="auth-warning" style="background: #ffe6e6; color: #cc0000; padding: 10px; margin: 10px; border-radius: 5px; text-align: center;">
        Please <a href="login.php" style="color: #cc0000; text-decoration: underline;">log in as an admin</a> to manage attendance.
    </div>
    <?php endif; ?>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Attendance Reports</h2>
            <p>Generate and view attendance reports. Use the calendar below to pick a day and mark attendance for members.</p>

            <?php
            // Ensure attendance table exists (safe, idempotent)
            $createSql = "CREATE TABLE IF NOT EXISTS attendance (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                attend_date DATE NOT NULL,
                status ENUM('present','absent') NOT NULL DEFAULT 'absent',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_member_date (member_id, attend_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conn->query($createSql);

            // Selected date (YYYY-MM-DD) for marking attendance
            $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            // Normalize and validate
            $d = DateTime::createFromFormat('Y-m-d', $selectedDate);
            if (!$d) {
                $selectedDate = date('Y-m-d');
                $d = new DateTime($selectedDate);
            }

            // Handle POST: save attendance for selected date
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attend_date'])) {
                $attend_date = $_POST['attend_date'];
                // Fetch members list to know which IDs to process
                $mstmt = $conn->prepare("SELECT user_id FROM users WHERE role = 'member'");
                $mstmt->execute();
                $mres = $mstmt->get_result();
                // Build a set of posted present member IDs
                $present = [];
                if (isset($_POST['present']) && is_array($_POST['present'])) {
                    foreach ($_POST['present'] as $mid => $val) {
                        $mid = (int)$mid;
                        if ($mid > 0) $present[$mid] = true;
                    }
                }

                // Upsert attendance per member
                $ins = $conn->prepare("INSERT INTO attendance (member_id, attend_date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()");
                while ($mrow = $mres->fetch_assoc()) {
                    $mid = (int)$mrow['user_id'];
                    $status = isset($present[$mid]) ? 'present' : 'absent';
                    $ins->bind_param('iss', $mid, $attend_date, $status);
                    $ins->execute();
                }
                $message = 'Attendance saved for ' . htmlspecialchars($attend_date);
                // reload selected date variable
                $selectedDate = $attend_date;
                $d = DateTime::createFromFormat('Y-m-d', $selectedDate);
            }

            // Month navigation
            $year = (int)$d->format('Y');
            $month = (int)$d->format('m');
            $firstOfMonth = new DateTime("{$year}-{$month}-01");
            $startDay = (int)$firstOfMonth->format('N'); // 1 (Mon) - 7 (Sun)
            $daysInMonth = (int)$firstOfMonth->format('t');

            // Prev/next month links
            $prev = (clone $firstOfMonth)->modify('-1 month')->format('Y-m-d');
            $next = (clone $firstOfMonth)->modify('+1 month')->format('Y-m-d');
            
            // Fetch attendance data for the whole month
            $monthStart = $firstOfMonth->format('Y-m-d');
            $monthEnd = (clone $firstOfMonth)->modify('+1 month -1 day')->format('Y-m-d');
            $monthAttendance = [];
            $attStmt = $conn->prepare("SELECT attend_date, COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present FROM attendance WHERE attend_date BETWEEN ? AND ? GROUP BY attend_date");
            $attStmt->bind_param('ss', $monthStart, $monthEnd);
            $attStmt->execute();
            $attRes = $attStmt->get_result();
            while ($att = $attRes->fetch_assoc()) {
                $monthAttendance[$att['attend_date']] = [
                    'total' => (int)$att['total'],
                    'present' => (int)$att['present']
                ];
            }
            ?>

            <?php if (isset($message)) echo "<p style='color:green;'>" . $message . "</p>"; ?>

            <div class="calendar-wrap">
                <div class="calendar-nav">
                    <a href="?date=<?php echo urlencode($prev); ?>">&laquo; Prev</a>
                    <span class="calendar-title"><?php echo $firstOfMonth->format('F Y'); ?></span>
                    <a href="?date=<?php echo urlencode($next); ?>">Next &raquo;</a>
                </div>
                
                <div class="calendar-legend">
                    <span class="legend-item"><span class="dot no-attendance"></span> No Records</span>
                    <span class="legend-item"><span class="dot partial-attendance"></span> Partial Attendance</span>
                    <span class="legend-item"><span class="dot full-attendance"></span> Full Attendance</span>
                    <span class="legend-item"><span class="dot selected"></span> Selected Date</span>
                </div>

                <table class="calendar">
                    <thead>
                        <tr>
                            <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $cell = 1 - ($startDay - 1); // position of first cell
                    while ($cell <= $daysInMonth) {
                        echo "<tr>";
                        for ($dow = 1; $dow <= 7; $dow++, $cell++) {
                            if ($cell < 1 || $cell > $daysInMonth) {
                                echo "<td class=\"empty\"></td>";
                            } else {
                                $dayDate = sprintf('%04d-%02d-%02d', $year, $month, $cell);
                                $classes = [];
                                if ($dayDate === $selectedDate) {
                                    $classes[] = 'selected';
                                }
                                
                                // Add attendance status classes
                                if (isset($monthAttendance[$dayDate])) {
                                    $att = $monthAttendance[$dayDate];
                                    if ($att['present'] == $att['total']) {
                                        $classes[] = 'full-attendance';
                                    } elseif ($att['present'] > 0) {
                                        $classes[] = 'partial-attendance';
                                    } else {
                                        $classes[] = 'no-attendance';
                                    }
                                }
                                
                                $classString = implode(' ', $classes);
                                echo "<td class=\"{$classString}\"><a href=\"?date={$dayDate}\">";
                                echo $cell;
                                if (isset($monthAttendance[$dayDate])) {
                                    echo "<span class='attendance-info'>{$monthAttendance[$dayDate]['present']}/{$monthAttendance[$dayDate]['total']}</span>";
                                }
                                echo "</a></td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <?php
            // When a date is selected, show list of members with their attendance status and a form to update
            $displayDate = $selectedDate;
            // Only show the form if user is authorized
            if ($displayDate && $isAuthorized):
                // Fetch members
                $mstmt2 = $conn->prepare("SELECT user_id, full_name, email FROM users WHERE role = 'member' ORDER BY full_name ASC");
                $mstmt2->execute();
                $mres2 = $mstmt2->get_result();
                // Fetch attendance for that date
                $aStmt = $conn->prepare("SELECT member_id, status FROM attendance WHERE attend_date = ?");
                $aStmt->bind_param('s', $displayDate);
                $aStmt->execute();
                $aRes = $aStmt->get_result();
                $attendanceMap = [];
                while ($ar = $aRes->fetch_assoc()) {
                    $attendanceMap[(int)$ar['member_id']] = $ar['status'];
                }
            ?>

            <form method="POST">
                <input type="hidden" name="attend_date" value="<?php echo htmlspecialchars($displayDate); ?>">
                <h3>Mark Attendance for <?php echo htmlspecialchars($displayDate); ?></h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Member</th><th>Email</th><th>Present</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($mrow = $mres2->fetch_assoc()):
                            $mid = (int)$mrow['user_id'];
                            $isPresent = isset($attendanceMap[$mid]) && $attendanceMap[$mid] === 'present';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mrow['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($mrow['email']); ?></td>
                            <td style="text-align:center;"><input type="checkbox" name="present[<?php echo $mid; ?>]" value="1" <?php echo $isPresent ? 'checked' : ''; ?>></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <p><button type="submit">Save Attendance</button></p>
            </form>

            <?php endif; ?>

            <style>
                :root{
                    --primary:#0b6cff;
                    --muted:#6b7280;
                    --card:#ffffff;
                    --bg:#f4f6fb;
                    --accent:#e7f3ff;
                    --radius:10px;
                    --maxWidth:1000px;
                }
                .admin-page{max-width:var(--maxWidth);margin:24px auto;padding:24px;background:linear-gradient(180deg, #fff, #fbfdff);box-shadow:0 6px 20px rgba(11,30,60,0.08);border-radius:12px;font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
                .admin-page h2{margin:0 0 8px;color:#0f172a}
                .admin-page p{color:var(--muted);margin-top:0}

                /* Calendar container */
                .calendar-wrap{display:flex;flex-direction:column;gap:12px;align-items:flex-start;margin:18px 0}
                .calendar-nav{width:100%;display:flex;justify-content:space-between;align-items:center}
                .calendar-nav a{color:var(--primary);text-decoration:none;padding:6px 10px;border-radius:8px;border:1px solid transparent}
                .calendar-nav a:hover{background:rgba(11,108,255,0.06);border-color:rgba(11,108,255,0.12)}
                .calendar-title{font-weight:600;color:#06283d}

                /* Calendar table */
                .calendar{border-collapse:separate;border-spacing:8px;width:100%;max-width:640px;background:transparent}
                .calendar thead th{color:var(--muted);font-weight:600;padding:8px 6px;text-align:center}
                .calendar td{background:var(--card);border-radius:8px;padding:12px 8px;text-align:center;vertical-align:middle;box-shadow:0 1px 0 rgba(16,24,40,0.03);min-width:42px}
                .calendar td.empty{background:transparent;box-shadow:none}
                .calendar td a{display:block;color:#0f172a;text-decoration:none}
                .calendar td.selected{background:linear-gradient(180deg,var(--accent),#ffffff);box-shadow:0 6px 18px rgba(11,30,60,0.06)}

                /* Data table - desktop (true table layout) */
                .data-table{width:100%;border-collapse:collapse;margin-top:12px;background:transparent;border-radius:8px;overflow:hidden}
                .data-table thead th{background:transparent;text-align:left;padding:12px 14px;color:var(--muted);font-weight:600;border-bottom:1px solid rgba(15,23,42,0.04)}
                .data-table tbody tr{background:var(--card);box-shadow:0 4px 14px rgba(2,6,23,0.04)}
                .data-table td{padding:12px 14px;border-top:1px solid rgba(15,23,42,0.03);vertical-align:middle}
                .data-table tbody tr td:first-child{font-weight:700;color:var(--primary);}
                .data-table tbody tr td:nth-child(2){color:var(--muted);}
                .data-table tbody tr td:nth-child(3) input[type=checkbox]{transform:scale(1.1);accent-color:var(--primary)}

                /* Make the checkbox column narrow and center-aligned */
                .data-table td:nth-child(3), .data-table th:nth-child(3) { width: 90px; text-align: center; }

                /* Mobile: stack rows as card-like blocks */
                @media (max-width:900px){
                    .data-table{border-radius:0;overflow:visible}
                    .data-table thead{display:none}
                    .data-table tbody tr{display:block;margin-bottom:12px;border-radius:10px;padding:12px;background:var(--card);box-shadow:0 6px 18px rgba(2,6,23,0.06)}
                    .data-table tbody tr td{display:flex;justify-content:space-between;padding:8px 0;border-top:none}
                    .data-table tbody tr td:first-child{display:block;font-weight:700;margin-bottom:6px;color:var(--primary)}
                    .data-table tbody tr td:nth-child(2){color:var(--muted);font-size:0.95em}
                    .data-table tbody tr td:nth-child(3){text-align:right}
                }

                button, .action-button{background:var(--primary);color:#fff;border:none;padding:8px 14px;border-radius:8px;cursor:pointer}
                button:hover, .action-button:hover{opacity:0.95}

                /* Calendar Attendance Indicators */
                .calendar td.full-attendance {
                    background: #e6ffe6;
                    border: 1px solid #b3ffb3;
                }
                .calendar td.partial-attendance {
                    background: #fff7e6;
                    border: 1px solid #ffe0b3;
                }
                .calendar td.no-attendance {
                    background: #ffe6e6;
                    border: 1px solid #ffb3b3;
                }
                .attendance-info {
                    display: block;
                    font-size: 0.8em;
                    color: var(--muted);
                    margin-top: 2px;
                }
                .calendar-legend {
                    display: flex;
                    gap: 16px;
                    margin-bottom: 8px;
                }
                .legend-item {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-size: 0.9em;
                    color: var(--muted);
                }
                .dot {
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                }
                .dot.full-attendance {
                    background: #e6ffe6;
                    border: 1px solid #b3ffb3;
                }
                .dot.partial-attendance {
                    background: #fff7e6;
                    border: 1px solid #ffe0b3;
                }
                .dot.no-attendance {
                    background: #ffe6e6;
                    border: 1px solid #ffb3b3;
                }
                .dot.selected {
                    background: var(--accent);
                    border: 1px solid var(--primary);
                }

                /* Responsive */
                @media (max-width:900px){
                    .admin-page{padding:16px}
                    .calendar{max-width:100%}
                    .data-table thead{display:none}
                    .data-table tbody tr{display:block;padding:12px}
                    .data-table tbody tr td{display:flex;justify-content:space-between;padding:8px 0;border-top:none}
                }
            </style>

        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
