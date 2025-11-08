<?php
require_once 'db_connect.php';

// Function to generate seating plan (no change to this function itself)
function generateSeatingPlan(array $students, array $rooms): array {
    // ... (the seating logic function remains the same as before) ...
    $studentsByClass = [];
    foreach ($students as $student) {
        $studentsByClass[$student['class_dept']][] = $student;
    }
    $seatPool = [];
    foreach ($rooms as $room) {
        for ($r = 1; $r <= $room['rows']; $r++) {
            for ($c = 1; $c <= $room['cols']; $c++) {
                $seatPool[] = ['room_id' => $room['id'], 'seat_row' => $r, 'seat_col' => $c];
            }
        }
    }
    shuffle($seatPool);
    if (count($seatPool) < count($students)) {
        return ['error' => 'Not enough seats for all students!'];
    }
    $finalSeatingPlan = [];
    for ($i = 0; $i < count($students); $i++) {
        uasort($studentsByClass, function ($a, $b) { return count($b) <=> count($a); });
        $largestGroupKey = key($studentsByClass);
        $studentToPlace = array_pop($studentsByClass[$largestGroupKey]);
        if (empty($studentsByClass[$largestGroupKey])) unset($studentsByClass[$largestGroupKey]);
        $seat = $seatPool[$i];
        $finalSeatingPlan[] = [
            'student_roll_no' => $studentToPlace['roll_no'],
            'room_id' => $seat['room_id'],
            'seat_row' => $seat['seat_row'],
            'seat_col' => $seat['seat_col']
        ];
    }
    return $finalSeatingPlan;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exam_id'])) {
    $examId = $_POST['exam_id'];

    $conn->query("DELETE FROM seating_plan WHERE exam_id = $examId");
    $conn->query("DELETE FROM invigilation_duties WHERE exam_id = $examId");

    $studentsResult = $conn->query("SELECT s.roll_no, s.class_dept FROM students s JOIN exam_registrations er ON s.roll_no = er.student_roll_no WHERE er.exam_id = $examId");
    $students = $studentsResult->fetch_all(MYSQLI_ASSOC);

    $roomsResult = $conn->query("SELECT id, `rows`, cols FROM rooms");
    $rooms = $roomsResult->fetch_all(MYSQLI_ASSOC);
    
    $plan = generateSeatingPlan($students, $rooms);

    if(isset($plan['error'])) {
        header("location: ../admin_dashboard.php?error=" . urlencode($plan['error']));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO seating_plan (exam_id, student_roll_no, room_id, seat_row, seat_col) VALUES (?, ?, ?, ?, ?)");
    foreach ($plan as $seat) {
        $stmt->bind_param("isiii", $examId, $seat['student_roll_no'], $seat['room_id'], $seat['seat_row'], $seat['seat_col']);
        $stmt->execute();
    }
    
    // --- THIS IS THE CHANGED PART ---
    // Get faculty from the new `faculty` table for invigilation duties
    $usedRoomsResult = $conn->query("SELECT DISTINCT room_id FROM seating_plan WHERE exam_id = $examId");
    $usedRoomIds = $usedRoomsResult->fetch_all(MYSQLI_ASSOC);
    
    // The query now selects from `faculty` instead of `users`
    $facultyResult = $conn->query("SELECT id FROM faculty ORDER BY RAND()");
    $faculty = $facultyResult->fetch_all(MYSQLI_ASSOC);

    if(count($faculty) > 0) {
        $facultyIndex = 0;
        $invigStmt = $conn->prepare("INSERT INTO invigilation_duties (exam_id, faculty_id, room_id) VALUES (?, ?, ?)");
        foreach($usedRoomIds as $room) {
            $facultyId = $faculty[$facultyIndex % count($faculty)]['id'];
            $invigStmt->bind_param("isi", $examId, $facultyId, $room['room_id']);
            $invigStmt->execute();
            $facultyIndex++;
        }
    }
    
    header("location: ../admin_dashboard.php?success=Seating plan generated successfully!");
}
?>