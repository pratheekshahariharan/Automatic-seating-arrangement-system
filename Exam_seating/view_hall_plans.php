<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// --- CORRECTED SQL QUERY ---
// This query now joins through the new `hall_plan_schedules` linking table.
$result = $conn->query("
    SELECT 
        hp.id, 
        hp.created_at, 
        es.exam_name, 
        c.name as course_name
    FROM hall_plans hp
    JOIN hall_plan_schedules hps ON hp.id = hps.hall_plan_id
    JOIN exam_schedule es ON hps.schedule_id = es.id
    JOIN courses c ON es.course_id = c.course_id
    GROUP BY hp.id
    ORDER BY hp.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Hall Plans</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>View Hall Plans</h1></div>
        <div class="content-wrapper">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Exam(s)</th>
                            <th>Generated On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['exam_name'] . " - " . $row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars(date('d-M-Y h:i A', strtotime($row['created_at']))); ?></td>
                                <td>
                                    <a href="display_hall_plan.php?plan_id=<?php echo $row['id']; ?>" class="back-button" target="_blank">View Plan</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No hall plans have been generated yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>