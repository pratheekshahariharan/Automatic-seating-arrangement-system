<?php
require_once 'db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') die("Access Denied.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['schedule_ids']) || empty($_POST['faculty_ids'])) {
        header("location: ../hall_plan.php?error=You must select at least one exam and one faculty member.");
        exit;
    }
    
    $schedule_ids = $_POST['schedule_ids'];
    $selected_faculty_ids = $_POST['faculty_ids'];

    // 1. FETCH DATA
    $students = [];
    $student_sql = "
        SELECT s.roll_no, s.name, s.class_dept, s.year, s.semester 
        FROM students s
        JOIN courses c ON s.class_dept = c.dept AND s.semester = c.semester
        JOIN exam_schedule es ON c.course_id = es.course_id
        WHERE es.id = ?";
    
    $stmt = $conn->prepare($student_sql);
    foreach($schedule_ids as $schedule_id) {
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $students = array_merge($students, $result);
    }
    $students = array_unique($students, SORT_REGULAR);

    $all_rooms = $conn->query("SELECT * FROM rooms ORDER BY capacity ASC")->fetch_all(MYSQLI_ASSOC);
    $faculty_placeholders = implode(',', array_fill(0, count($selected_faculty_ids), '?'));
    $faculty_types = str_repeat('s', count($selected_faculty_ids));
    $faculty_sql = "SELECT id, name, dept FROM faculty WHERE id IN ($faculty_placeholders)";
    $fac_stmt = $conn->prepare($faculty_sql);
    $fac_stmt->bind_param($faculty_types, ...$selected_faculty_ids);
    $fac_stmt->execute();
    $faculty = $fac_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($students)) {
        header("location: ../hall_plan.php?error=No students found for the selected exam(s).");
        exit;
    }

    // 2. DYNAMIC ROOM & SEAT CALCULATION
    $total_students = count($students);
    $needed_rooms = [];
    $seat_pool = [];
    $capacity_met = 0;
    foreach ($all_rooms as $room) {
        if ($capacity_met < $total_students) {
            $needed_rooms[] = $room;
            $capacity_met += $room['capacity'];
            for ($r = 1; $r <= $room['rows']; $r++) {
                for ($c = 1; $c <= $room['cols']; $c++) {
                    $seat_pool[] = ['room_id' => $room['id'], 'seat_row' => $r, 'seat_col' => $c];
                }
            }
        } else {
            break;
        }
    }
    if ($capacity_met < $total_students) {
        header("location: ../hall_plan.php?error=Not enough room capacity for all students.");
        exit;
    }
    
    // 3. SEATING ALGORITHM (Strict Alternating Order)
    $students_by_dept = [];
    foreach ($students as $student) {
        $students_by_dept[$student['class_dept']][] = $student;
    }
    foreach ($students_by_dept as &$dept_list) {
        sort($dept_list);
    }
    $seating_plan = [];
    $dept_keys = array_keys($students_by_dept);
    $num_depts = count($dept_keys);
    $dept_cursors = array_fill_keys($dept_keys, 0);
    $current_dept_index = 0;
    for ($i = 0; $i < $total_students; $i++) {
        while ($dept_cursors[$dept_keys[$current_dept_index]] >= count($students_by_dept[$dept_keys[$current_dept_index]])) {
            $current_dept_index = ($current_dept_index + 1) % $num_depts;
        }
        $current_dept = $dept_keys[$current_dept_index];
        $student_to_place = $students_by_dept[$current_dept][$dept_cursors[$current_dept]];
        $seat_info = $seat_pool[$i];
        $seating_plan[] = array_merge($student_to_place, $seat_info);
        $dept_cursors[$current_dept]++;
        $current_dept_index = ($current_dept_index + 1) % $num_depts;
    }
    
    // 4. INVIGILATOR ASSIGNMENT
    if (count($faculty) < count($needed_rooms)) {
         header("location: ../hall_plan.php?error=Not enough selected faculty to cover all needed rooms.");
        exit;
    }
    shuffle($faculty);
    $invigilation_duties = [];
    $faculty_index = 0;
    foreach ($needed_rooms as $room) {
        $invigilation_duties[] = [
            'faculty_id' => $faculty[$faculty_index]['id'],
            'room_id' => $room['id']
        ];
        $faculty_index = ($faculty_index + 1) % count($faculty);
    }

    // 5. SAVE TO DATABASE
    $primary_schedule_id = $schedule_ids[0]; // Used for backward compatibility if needed
    $conn->query("DELETE FROM seating_plan WHERE schedule_id IN (" . implode(',', $schedule_ids) . ")");
    $conn->query("DELETE FROM invigilation_duties WHERE schedule_id IN (" . implode(',', $schedule_ids) . ")");
    
    $seat_stmt = $conn->prepare("INSERT INTO seating_plan (schedule_id, student_roll_no, room_id, seat_row, seat_col) VALUES (?, ?, ?, ?, ?)");
    $invig_stmt = $conn->prepare("INSERT INTO invigilation_duties (schedule_id, faculty_id, room_id) VALUES (?, ?, ?)");
    foreach ($seating_plan as $seat) {
        $seat_stmt->bind_param("isiii", $primary_schedule_id, $seat['roll_no'], $seat['room_id'], $seat['seat_row'], $seat['seat_col']);
        $seat_stmt->execute();
    }
    foreach ($invigilation_duties as $duty) {
        $invig_stmt->bind_param("isi", $primary_schedule_id, $duty['faculty_id'], $duty['room_id']);
        $invig_stmt->execute();
    }
    
    // 6. PREPARE & SAVE VISUALIZATION DATA
    $visual_plan = ['rooms' => [], 'all_students' => $seating_plan];
    $room_details = array_column($needed_rooms, null, 'id');
    $faculty_details = array_column($faculty, null, 'id');
    $used_room_ids = array_unique(array_column($seating_plan, 'room_id'));
    foreach ($used_room_ids as $room_id) {
        if(isset($room_details[$room_id])) {
            $visual_plan['rooms'][$room_id] = [
                'room_name' => $room_details[$room_id]['room_name'], 'rows' => $room_details[$room_id]['rows'],
                'cols' => $room_details[$room_id]['cols'], 'students' => [], 'invigilators' => []
            ];
        }
    }
    foreach ($seating_plan as $seat) {
        if(isset($visual_plan['rooms'][$seat['room_id']])) {
            $visual_plan['rooms'][$seat['room_id']]['students'][] = $seat;
        }
    }
    foreach ($invigilation_duties as $duty) {
        if(isset($visual_plan['rooms'][$duty['room_id']])) {
            $visual_plan['rooms'][$duty['room_id']]['invigilators'][] = $faculty_details[$duty['faculty_id']];
        }
    }
    
    // Convert to JSON and save to the new `hall_plans` table using the linking table
    $plan_data_json = json_encode($visual_plan);
    $save_stmt = $conn->prepare("INSERT INTO hall_plans (plan_data) VALUES (?)");
    $save_stmt->bind_param("s", $plan_data_json);
    $save_stmt->execute();
    $hall_plan_id = $conn->insert_id;

    $link_stmt = $conn->prepare("INSERT INTO hall_plan_schedules (hall_plan_id, schedule_id) VALUES (?, ?)");
    foreach ($schedule_ids as $schedule_id) {
        $link_stmt->bind_param("ii", $hall_plan_id, $schedule_id);
        $link_stmt->execute();
    }

    header("location: ../view_hall_plans.php?success=Hall plan generated and saved!");
    exit;
}
?>