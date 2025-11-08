<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'faculty') {
    header("location: index.php");
    exit;
}
$facultyId = $_SESSION['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="faculty-dashboard">
    <div class="sidebar">
        <div class="sidebar-header"><h3>Faculty Portal</h3></div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active">My Duties</a></li>
            <li><a href="view_hall_plans.php">View All Hall Plans</a></li>
        </ul>
        <div class="sidebar-logout"><a href="php/logout.php" class="logout-button">Logout</a></div>
    </div>
    <div class="main-content">
        <div class="header"><h1>FACULTY DASHBOARD</h1></div>
        <div class="content-wrapper">
            <div class="card">
                <h2>Your Invigilation Duties</h2>
                <table>
                    <thead>
                        <tr><th>Exam</th><th>Date & Time</th><th>Room</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    // Updated query to get the hall plan ID
                    $sql = "
                        SELECT es.exam_name, es.exam_date, es.start_time, r.room_name, hp.id as plan_id 
                        FROM invigilation_duties id
                        JOIN exam_schedule es ON id.schedule_id = es.id
                        JOIN rooms r ON id.room_id = r.id
                        JOIN hall_plans hp ON id.schedule_id = hp.schedule_id
                        WHERE id.faculty_id = ?
                        ORDER BY es.exam_date, es.start_time";
                        
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("s", $facultyId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['exam_name']) . "</td>";
                                echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['exam_date']))) . " at " . htmlspecialchars(date('h:i A', strtotime($row['start_time']))) . "</td>";
                                echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
                                echo "<td><a href='display_hall_plan.php?plan_id={$row['plan_id']}' class='back-button' target='_blank'>View Hall Plan</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No duties assigned at the moment.</td></tr>";
                        }
                        $stmt->close();
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>