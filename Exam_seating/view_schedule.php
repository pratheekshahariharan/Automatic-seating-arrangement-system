<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}
// Fetch all scheduled exams and join with the courses table to get course details
$result = $conn->query("
    SELECT 
        es.id, 
        es.exam_name, 
        es.exam_date, 
        es.start_time, 
        c.name as course_name, 
        c.dept, 
        c.semester 
    FROM 
        exam_schedule es 
    JOIN 
        courses c ON es.course_id = c.course_id 
    ORDER BY 
        es.exam_date, es.start_time
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Exam Schedule</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header">
            <h1>View Exam Schedule</h1>
        </div>
        <div class="content-wrapper">
            <div class="search-and-back">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search schedule...">
                <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
            </div>
            <div class="table-container">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>Exam Name</th>
                            <th>Course Name</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Exam Date</th>
                            <th>Start Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['dept']); ?></td>
                                <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($row['exam_date']))); ?></td>
                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($row['start_time']))); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No schedules found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="assets/search.js"></script>
</body>
</html>