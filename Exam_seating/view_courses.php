<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}
// Corrected query to use the 'courses' table and its new columns
$result = $conn->query("SELECT course_id, name, dept, semester FROM courses");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Courses</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>View Courses</h1></div>
        <div class="content-wrapper">
            <div class="search-and-back">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for courses...">
                <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
            </div>
            <div class="table-container">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>Course ID</th>
                            <th>Course Name</th>
                            <th>Department</th>
                            <th>Semester</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['dept']); ?></td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="assets/search.js"></script>
</body>
</html>