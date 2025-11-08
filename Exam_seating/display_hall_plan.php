<?php
require_once 'php/db_connect.php';
// Ensure a plan ID is provided in the URL
if (!isset($_GET['plan_id'])) {
    die("No plan specified.");
}

$plan_id = (int)$_GET['plan_id'];
$result = $conn->query("SELECT plan_data FROM hall_plans WHERE id = $plan_id");

if ($result->num_rows === 0) {
    die("Hall plan not found.");
}

// Fetch and decode the JSON data from the database
$plan_json = $result->fetch_assoc()['plan_data'];
$hall_plan_data = json_decode($plan_json, true);

// Check if decoding was successful before proceeding
if (is_null($hall_plan_data)) {
    die("Error decoding hall plan data.");
}

// Create a color map for different departments
$depts = array_unique(array_column($hall_plan_data['all_students'], 'class_dept'));
$colors = ['#EF9A9A', '#A5D6A7', '#90CAF9', '#FFF59D', '#B39DDB']; // Red, Green, Blue, Yellow, Purple
$dept_color_map = array_combine($depts, array_slice($colors, 0, count($depts)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hall Plan Visualization</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>Hall Plan Visualization</h1></div>
        <div class="content-wrapper">
            <?php foreach ($hall_plan_data['rooms'] as $room): ?>
                <div class="room-plan">
                    <h3><?php echo htmlspecialchars($room['room_name']); ?></h3>
                    <div class="invigilators">
                        <strong>Invigilators:</strong> 
                        <?php echo htmlspecialchars(implode(', ', array_column($room['invigilators'], 'name'))); ?>
                    </div>
                    <div class="seating-grid" style="grid-template-columns: repeat(<?php echo $room['cols']; ?>, 1fr);">
                        <?php
                        // Create a map of seats for easy lookup
                        $seatMap = [];
                        foreach ($room['students'] as $student) {
                            $seatMap[$student['seat_row'] . '-' . $student['seat_col']] = $student;
                        }

                        // Generate the grid of seats
                        for ($r = 1; $r <= $room['rows']; $r++) {
                            for ($c = 1; $c <= $room['cols']; $c++) {
                                $seatKey = $r . '-' . $c;
                                $student_info = $seatMap[$seatKey] ?? null;
                                $color = $student_info ? ($dept_color_map[$student_info['class_dept']] ?? '#cccccc') : '';
                                
                                // Data attributes for the CSS hover tooltip
                                $data_attributes = $student_info ? 
                                    "data-name='" . htmlspecialchars($student_info['name']) . "' " .
                                    "data-rollno='" . htmlspecialchars($student_info['roll_no']) . "' " .
                                    "data-dept='" . htmlspecialchars($student_info['class_dept']) . "'" : "";

                                echo '<div class="seat '.($student_info ? 'occupied' : 'empty').'" style="background-color: '.$color.';" '.$data_attributes.'>';
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