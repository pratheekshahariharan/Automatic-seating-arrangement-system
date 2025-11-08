<?php
require_once 'php/db_connect.php';
// Redirect if not logged in or not an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

// Fetch counts for the dashboard cards
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_faculty = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
// Corrected table name from 'exams' to 'courses'
$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="view_students.php">View Students</a></li>
            <li><a href="view_faculty.php">View Faculty</a></li>
            <li><a href="view_rooms.php">View Rooms</a></li>
            <li><a href="view_courses.php">View Courses</a></li>
            <li><a href="add_schedule.php">Add Schedule</a></li>
            <li><a href="view_schedule.php">View Schedule</a></li>
            <li><a href="hall_plan.php">Hall Plan</a></li>
            <li><a href="view_hall_plans.php">View Hall Plans</a></li>
        </ul>
        <div class="sidebar-logout">
            <a href="php/logout.php" class="logout-button">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>ADMIN PANEL</h1>
        </div>
        
        <div class="card-container">
            <div class="dashboard-card">
                <div class="card-header"><h4>Total Students</h4></div>
                <div class="card-body"><p>You have <?php echo $total_students; ?> students enrolled.</p></div>
            </div>
            <div class="dashboard-card">
                <div class="card-header"><h4>Total Faculty</h4></div>
                <div class="card-body"><p>You have <?php echo $total_faculty; ?> faculty members.</p></div>
            </div>
            <div class="dashboard-card">
                <div class="card-header"><h4>Total Courses</h4></div>
                <div class="card-body"><p>You have <?php echo $total_courses; ?> courses available.</p></div>
            </div>
            <div class="dashboard-card">
                <div class="card-header"><h4>Total Rooms</h4></div>
                <div class="card-body"><p>You have <?php echo $total_rooms; ?> rooms available.</p></div>
            </div>
        </div>
    </div>
</body>
</html>