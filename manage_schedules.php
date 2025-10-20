<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules & Classes - POWER FITNESS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="admin-page">
            <h2>Schedules & Classes</h2>
            <p>Create, update, and manage class schedules and instructor assignments from this page.</p>

            <style>
                .schedules-table{display:flex;gap:24px;flex-wrap:wrap;justify-content:center;margin-top:24px}
                .schedule-col{flex:1 1 300px;min-width:280px;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,0.10);padding:22px 18px 18px 18px;transition:transform 0.15s,box-shadow 0.15s;position:relative;overflow:hidden;border:2px solid #e5e7eb}
                .schedule-col:hover{transform:translateY(-4px) scale(1.03);box-shadow:0 8px 32px rgba(0,0,0,0.16);z-index:2}
                .schedule-col.weight1{background:linear-gradient(135deg,#0b6cff 80%,#2563eb 100%);color:#fff;border-color:#2563eb}
                .schedule-col.weight2{background:linear-gradient(135deg,#16a34a 80%,#22c55e 100%);color:#fff;border-color:#22c55e}
                .schedule-col.girls{background:linear-gradient(135deg,#e11d48 80%,#f43f5e 100%);color:#fff;border-color:#f43f5e}
                .schedule-col h3{margin-top:0;text-align:center;font-size:1.3em;letter-spacing:1px;display:flex;align-items:center;justify-content:center;gap:8px}
                .schedule-col ul{list-style:none;padding:0;margin:0}
                .schedule-col li{margin-bottom:18px;padding-bottom:8px;border-bottom:1px solid rgba(255,255,255,0.10)}
                .schedule-col li:last-child{border-bottom:none}
                .schedule-icon{font-size:1.5em;vertical-align:middle;}
            </style>
            <div class="schedules-table">
                <div class="schedule-col weight1">
                    <h3><span class="schedule-icon">🏋️‍♂️</span> Weight 50-70</h3>
                    <ul style="list-style:none;padding:0;">
                        <li><strong>Day 1 – Chest & Triceps</strong><br>
                            • Bench Press – 4 sets × 08–10 reps<br>
                            • Incline Dumbbell Press – 3×10<br>
                            • Cable Fly or Dumbbell Fly – 3×12<br>
                            • Tricep Pushdown – 3×12<br>
                            • Overhead Tricep Extension – 3×10
                        </li>
                        <li><strong>Day 2 – Back & Biceps</strong><br>
                            • Lat Pulldown – 4×10<br>
                            • Seated Row – 3×10<br>
                            • Deadlift (optional) – 3×8<br>
                            • Barbell Curl – 3×10<br>
                            • Hammer Curl – 3×12
                        </li>
                        <li><strong>Day 3 – Shoulders & Abs</strong><br>
                            • Shoulder Press – 4×10<br>
                            • Lateral Raise – 3×12<br>
                            • Front Raise – 3×12<br>
                            • Shrugs – 3×15<br>
                            • Plank – 3×30 sec<br>
                            • Crunches – 3×15
                        </li>
                        <li><strong>Day 4 – Legs</strong><br>
                            • Squats – 4×10<br>
                            • Leg Press – 3×10<br>
                            • Lunges – 3×12 (each leg)<br>
                            • Leg Curl – 3×12<br>
                            Calf Raise – 3×15
                        </li>
                        <li><strong>Day 5 – Cardio & Core</strong><br>
                            • Treadmill / Cycling – 30–45 min<br>
                            • Russian Twists – 3×20<br>
                            • Leg Raises – 3×15<br>
                            • Mountain Climbers – 3×20
                        </li>
                        <li><strong>Day 6 – Full Body / Functional Training</strong><br>
                            • Burpees – 3×15<br>
                            • Push-ups – 3×15<br>
                            • Kettlebell Swings – 3×15<br>
                            • Rope or circuit training (varies by gym)
                        </li>
                        <li><strong>Day 7 – Rest / Recovery</strong><br>
                            • Stretching or light yoga (optional walk)
                        </li>
                    </ul>
                </div>
                <div class="schedule-col weight2">
                    <h3><span class="schedule-icon">🏋️</span> Weight 70-100</h3>
                    <ul style="list-style:none;padding:0;">
                        <li><strong>Day 1 – Chest & Triceps</strong><br>
                            • Bench Press – 4 sets × 12–15 reps<br>
                            • Incline Dumbbell Press – 3×15<br>
                            • Cable Fly or Dumbbell Fly – 3×15<br>
                            • Tricep Pushdown – 3×15<br>
                            • Overhead Tricep Extension – 3×15
                        </li>
                        <li><strong>Day 2 – Back & Biceps</strong><br>
                            • Lat Pulldown – 4×15<br>
                            • Seated Row – 3×15<br>
                            • Deadlift (optional) – 3×10<br>
                            • Barbell Curl – 3×15<br>
                            • Hammer Curl – 3×15
                        </li>
                        <li><strong>Day 3 – Shoulders & Abs</strong><br>
                            • Shoulder Press – 4×15<br>
                            • Lateral Raise – 3×15<br>
                            • Front Raise – 3×15<br>
                            • Shrugs – 3×25<br>
                            • Plank – 3×45 sec<br>
                            • Crunches – 3×25
                        </li>
                        <li><strong>Day 4 – Legs</strong><br>
                            • Squats – 4×15<br>
                            • Leg Press – 3×15<br>
                            • Lunges – 3×15 (each leg)<br>
                            • Leg Curl – 3×25<br>
                            Calf Raise – 3×25
                        </li>
                        <li><strong>Day 5 – Cardio & Core</strong><br>
                            • Treadmill / Cycling – 40–60 min<br>
                            • Russian Twists – 3×30<br>
                            • Leg Raises – 3×25<br>
                            • Mountain Climbers – 3×30
                        </li>
                        <li><strong>Day 6 – Full Body / Functional Training</strong><br>
                            • Burpees – 3×25<br>
                            • Push-ups – 3×25<br>
                            • Kettlebell Swings – 3×25<br>
                            • Rope or circuit training (varies by gym)
                        </li>
                        <li><strong>Day 7 – Rest / Recovery</strong><br>
                            • Stretching or light yoga (optional walk)
                        </li>
                    </ul>
                </div>
                <div class="schedule-col girls">
                    <h3><span class="schedule-icon">👩‍🎤</span> Only Girls</h3>
                    <ul style="list-style:none;padding:0;">
                        <li><strong>Day 1 – Chest & Triceps</strong><br>
                            • Bench Press – 4 sets × 08–10 reps<br>
                            • Incline Dumbbell Press – 3×10<br>
                            • Cable Fly or Dumbbell Fly – 3×12<br>
                            • Tricep Pushdown – 3×12<br>
                            • Overhead Tricep Extension – 3×10
                        </li>
                        <li><strong>Day 2 – Back & Biceps</strong><br>
                            • Lat Pulldown – 4×10<br>
                            • Seated Row – 3×10<br>
                            • Deadlift (optional) – 3×8<br>
                            • Barbell Curl – 3×10<br>
                            • Hammer Curl – 3×12
                        </li>
                        <li><strong>Day 3 – Shoulders & Abs</strong><br>
                            • Shoulder Press – 4×10<br>
                            • Lateral Raise – 3×12<br>
                            • Front Raise – 3×12<br>
                            • Shrugs – 3×15<br>
                            • Plank – 3×30 sec<br>
                            • Crunches – 3×15
                        </li>
                        <li><strong>Day 4 – Legs</strong><br>
                            • Squats – 4×10<br>
                            • Leg Press – 3×10<br>
                            • Lunges – 3×12 (each leg)<br>
                            • Leg Curl – 3×12<br>
                            Calf Raise – 3×15
                        </li>
                        <li><strong>Day 5 – Cardio & Core</strong><br>
                            • Treadmill / Cycling – 30–45 min<br>
                            • Russian Twists – 3×20<br>
                            • Leg Raises – 3×15<br>
                            • Mountain Climbers – 3×20
                        </li>
                        <li><strong>Day 6 – Full Body / Functional Training</strong><br>
                            • Burpees – 3×15<br>
                            • Push-ups – 3×15<br>
                            • Kettlebell Swings – 3×15<br>
                            • Rope or circuit training (varies by gym)
                        </li>
                        <li><strong>Day 7 – Rest / Recovery</strong><br>
                            • Stretching or light yoga (optional walk)
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
