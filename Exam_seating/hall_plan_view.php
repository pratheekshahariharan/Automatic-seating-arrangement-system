<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['hall_plan_data_for_view'])) {
    die("No hall plan generated. Please generate one from the admin dashboard.");
}
$hall_plan_data = $_SESSION['hall_plan_data_for_view'];
unset($_SESSION['hall_plan_data_for_view']); // Clear session data after loading

// Create a color map for departments
$depts = array_unique(array_column($hall_plan_data['all_students'], 'class_dept'));
$colors = ['#EF9A9A', '#A5D6A7', '#90CAF9', '#FFF59D', '#B39DDB']; // Red, Green, Blue, Yellow, Purple
$dept_color_map = array_combine($depts, array_slice($colors, 0, count($depts)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Hall Plan</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>Generated Hall Plan</h1></div>
        <div class="content-wrapper">
             <?php foreach ($hall_plan_data['rooms'] as $room): ?>
                <div class="room-plan">
                    <h3><?php echo htmlspecialchars($room['room_name']); ?></h3>
                    <div class="invigilators"><strong>Invigilators:</strong> <?php echo htmlspecialchars(implode(', ', array_column($room['invigilators'], 'name'))); ?></div>
                    <div class="seating-grid" style="grid-template-columns: repeat(<?php echo $room['cols']; ?>, 1fr);">
                        <?php
                        // Create a map of seats for easy lookup
                        $seatMap = [];
                        foreach ($room['students'] as $student) {
                            $seatMap[$student['seat_row'] . '-' . $student['seat_col']] = $student;
                        }
                        for ($r = 1; $r <= $room['rows']; $r++) {
                            for ($c = 1; $c <= $room['cols']; $c++) {
                                $seatKey = $r . '-' . $c;
                                $student_info = $seatMap[$seatKey] ?? null;
                                $color = $student_info ? $dept_color_map[$student_info['class_dept']] : '';
                                $tooltip_text = $student_info ? htmlspecialchars("Name: {$student_info['name']}\nDept: {$student_info['class_dept']}\nYear: {$student_info['year']}, Sem: {$student_info['semester']}") : 'Empty Seat';
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
    </div>
</body>
</html>