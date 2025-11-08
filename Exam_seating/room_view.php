<?php
require_once 'php/db_connect.php';
// Everyone who is logged in can view this page
if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Get exam and room ID from URL, ensuring they are integers
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

if ($exam_id === 0 || $room_id === 0) {
    die("Error: Invalid Exam or Room ID.");
}

// Fetch room and exam details for the title
$exam_details_q = $conn->query("SELECT subject_name FROM exams WHERE id = $exam_id");
$exam_details = $exam_details_q->fetch_assoc();
$room_details_q = $conn->query("SELECT room_name FROM rooms WHERE id = $room_id");
$room_details = $room_details_q->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Seating Plan</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="room-view-body">
    <div class="container room-container-full">
        <div class="header">
             <h2>Seating Plan: <?php echo htmlspecialchars($exam_details['subject_name']); ?></h2>
             <h3>Room: <?php echo htmlspecialchars($room_details['room_name']); ?></h3>
             <p><a href="javascript:history.back()" class="button-link">Go Back</a></p>
        </div>

        <div id="seating-grid" class="seating-grid">
            <p>Loading seating plan...</p>
        </div>
        
        <div id="seat-details" class="seat-details">
            <p>Click on a seat to see details.</p>
        </div>
    </div>
    
    <script>
        const EXAM_ID = <?php echo $exam_id; ?>;
        const ROOM_ID = <?php echo $room_id; ?>;
    </script>
    <script src="assets/script.js"></script>
</body>
</html>
