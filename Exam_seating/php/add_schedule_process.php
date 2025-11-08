<?php
require_once 'db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $exam_name = $_POST['exam_name']; // New exam name field
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];

    // --- Server-side date validation ---
    $selected_date = new DateTime($exam_date);
    $today = new DateTime();
    $min_date = (new DateTime())->add(new DateInterval('P14D'));
    $max_date = (new DateTime())->add(new DateInterval('P1M'));
    
    // To be precise, we compare dates only, not time.
    $today->setTime(0,0,0);
    $min_date->setTime(0,0,0);
    $max_date->setTime(0,0,0);

    if ($selected_date < $min_date || $selected_date > $max_date) {
        header("location: ../add_schedule.php?error=Invalid date selected. Please choose a date between 2 weeks and 1 month from now.");
        exit;
    }

    // Insert into the database
    $sql = "INSERT INTO exam_schedule (course_id, exam_name, exam_date, start_time) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $course_id, $exam_name, $exam_date, $start_time);
        
        if ($stmt->execute()) {
            header("location: ../add_schedule.php?success=Exam schedule added successfully!");
        } else {
            header("location: ../add_schedule.php?error=Error adding schedule: " . $stmt->error);
        }
        $stmt->close();
    } else {
        header("location: ../add_schedule.php?error=Database error: " . $conn->error);
    }
    $conn->close();
}
?>