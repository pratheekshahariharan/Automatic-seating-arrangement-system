

<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

// Fetch scheduled exams and available faculty
$schedules = $conn->query("SELECT s.id, s.exam_name, c.name as course_name FROM exam_schedule s JOIN courses c ON s.course_id = c.course_id ORDER BY s.exam_date DESC");
$faculty = $conn->query("SELECT id, name, dept FROM faculty ORDER BY name");

// Check for and display any generated hall plan from the session
$hall_plan_data = null;
if (isset($_SESSION['hall_plan_data'])) {
    $hall_plan_data = $_SESSION['hall_plan_data'];
    unset($_SESSION['hall_plan_data']);

    // Create a color map for departments
    $depts = array_unique(array_column($hall_plan_data['all_students'], 'class_dept'));
    $colors = ['#EF9A9A', '#A5D6A7', '#90CAF9', '#FFF59D', '#B39DDB'];
    $dept_color_map = array_combine($depts, array_slice($colors, 0, count($depts)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Hall Plan</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>Generate Hall Plan</h1></div>
        <div class="content-wrapper">
            <div class="form-card">
                <form action="php/generate_hall_plan_process.php" method="post">
                    <?php if (isset($_GET['error'])) echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>'; ?>
                    
                    <div class="form-group">
                        <label>Select Scheduled Exam(s):</label>
                        <div class="checkbox-group">
                            <?php while ($row = $schedules->fetch_assoc()): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="schedule_ids[]" value="<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['exam_name'] . ' - ' . $row['course_name']); ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Select Available Faculty for Invigilation:</label>
                        <div class="checkbox-group">
                            <?php while ($fac = $faculty->fetch_assoc()): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="faculty_ids[]" value="<?php echo $fac['id']; ?>">
                                    <?php echo htmlspecialchars($fac['name'] . ' (' . $fac['dept'] . ')'); ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="form-button">Generate Plan</button>
                        <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
                    </div>
                </form>
            </div>
            
            <?php if ($hall_plan_data): ?>
            <div class="hall-plan-container">
                <h2>Generated Hall Plan</h2>
                <?php foreach ($hall_plan_data['rooms'] as $room): ?>
                    <div class="room-plan">
                        <h3><?php echo htmlspecialchars($room['room_name']); ?></h3>
                        <div class="invigilators">
                            <strong>Invigilators:</strong>
                            <?php echo htmlspecialchars(implode(', ', array_column($room['invigilators'], 'name'))); ?>
                        </div>
                        <div class="seating-grid" style="grid-template-columns: repeat(<?php echo $room['cols']; ?>, 1fr);">
                            <?php
                            $seatMap = [];
                            foreach ($room['students'] as $student) {
                                $seatMap[$student['seat_row'] . '-' . $student['seat_col']] = $student;
                            }
                            for ($r = 1; $r <= $room['rows']; $r++) {
                                for ($c = 1; $c <= $room['cols']; $c++) {
                                    $seatKey = $r . '-' . $c;
                                    $student_info = $seatMap[$seatKey] ?? null;
                                    $color = $student_info ? ($dept_color_map[$student_info['class_dept']] ?? '#cccccc') : '';
                                    $tooltip_text = $student_info ? htmlspecialchars("Name: {$student_info['name']}\nDept: {$student_info['class_dept']}") : 'Empty Seat';
                                    echo '<div class="seat '.($student_info ? 'occupied' : 'empty').'" style="background-color: '.$color.';" title="'.$tooltip_text.'">';
                                    echo $student_info ? htmlspecialchars($student_info['roll_no']) : '';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>     