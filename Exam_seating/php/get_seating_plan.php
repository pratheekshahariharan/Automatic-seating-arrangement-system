<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

if ($exam_id === 0 || $room_id === 0) {
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

// Get Room Dimensions
$room_stmt = $conn->prepare("SELECT `rows`, cols FROM rooms WHERE id = ?");
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();
$room_details = $room_stmt->get_result()->fetch_assoc();

// Get Seating Plan for this room/exam
$plan_stmt = $conn->prepare("
    SELECT sp.seat_row, sp.seat_col, s.roll_no, s.name, s.class_dept 
    FROM seating_plan sp 
    JOIN students s ON sp.student_roll_no = s.roll_no 
    WHERE sp.exam_id = ? AND sp.room_id = ?
");
$plan_stmt->bind_param("ii", $exam_id, $room_id);
$plan_stmt->execute();
$seating_plan = $plan_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Combine and return as JSON
$response = [
    'roomDetails' => $room_details,
    'seatingPlan' => $seating_plan
];

echo json_encode($response);
?>