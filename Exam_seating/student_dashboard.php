<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    header("location: index.php");
    exit;
}
$rollNo = $_SESSION['id'];
$hall_plan_data = null;
$exam_details = null;

// This query finds the correct hall plan by matching the student's department and semester
// to the course associated with a generated plan, using the new linking table.
$sql = "
    SELECT hp.plan_data, es.exam_name, es.exam_date, es.start_time
    FROM hall_plans hp
    JOIN hall_plan_schedules hps ON hp.id = hps.hall_plan_id
    JOIN exam_schedule es ON hps.schedule_id = es.id
    JOIN courses c ON es.course_id = c.course_id
    JOIN students s ON c.dept = s.class_dept AND c.semester = s.semester
    WHERE s.roll_no = ?
    ORDER BY hp.created_at DESC
    LIMIT 1";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $rollNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hall_plan_data = json_decode($row['plan_data'], true);
        $exam_details = $row;
    }
    $stmt->close();
}

// Create color map for departments
$dept_color_map = [];
if($hall_plan_data){
    $depts = array_unique(array_column($hall_plan_data['all_students'], 'class_dept'));
    $colors = ['#EF9A9A', '#A5D6A7', '#90CAF9', '#FFF59D', '#B39DDB'];
    $dept_color_map = array_combine($depts, array_slice($colors, 0, count($depts)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="student-dashboard">
    <div class="sidebar">
        <div class="sidebar-header"><h3>Student Portal</h3></div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active">My Seating Plan</a></li>
            <li><a href="view_hall_plans.php">View All Hall Plans</a></li>
        </ul>
        <div class="sidebar-logout"><a href="php/logout.php" class="logout-button">Logout</a></div>
    </div>
    <div class="main-content">
        <div class="header"><h1>STUDENT DASHBOARD</h1></div>
        <div class="content-wrapper">
            <?php if ($hall_plan_data && $exam_details): ?>
                <div class="card">
                    <h2>Your Exam Details</h2>
                    <p><strong>Exam:</strong> <?php echo htmlspecialchars($exam_details['exam_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars(date('d-M-Y', strtotime($exam_details['exam_date']))); ?></p>
                    <p><strong>Time:</strong> <?php echo htmlspecialchars(date('h:i A', strtotime($exam_details['start_time']))); ?></p>
                </div>
                <?php foreach ($hall_plan_data['rooms'] as $room): ?>
                    <?php
                    $student_in_this_room = false;
                    foreach($room['students'] as $student) {
                        if ($student['roll_no'] === $rollNo) {
                            $student_in_this_room = true;
                            break;
                        }
                    }
                    if (!$student_in_this_room) continue;
                    ?>
                    <div class="room-plan">
                        <h3>Your Room: <?php echo htmlspecialchars($room['room_name']); ?></h3>
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
                                    $highlight_class = ($student_info && $student_info['roll_no'] === $rollNo) ? 'highlight-seat' : '';
                                    echo '<div class="seat '.($student_info ? 'occupied' : 'empty').' '.$highlight_class.'" style="background-color: '.$color.';" >';
                                    echo $student_info ? htmlspecialchars($student_info['roll_no']) : '';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <h2>No Seating Plan Found</h2>
                    <p>Your seating plan has not been generated or assigned yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>