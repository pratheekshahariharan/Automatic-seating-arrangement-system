<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}
// Updated query to fetch dept and specialization
$result = $conn->query("SELECT id, name, dept, specialization FROM faculty");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Faculty</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>View Faculty</h1></div>
        <div class="content-wrapper">
            <div class="search-and-back">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for faculty...">
                <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
            </div>
            <div class="table-container">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>Faculty ID</th>
                            <th>Name</th>
                            <th>Dept</th>
                            <th>Specialization</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['dept']); ?></td>
                            <td><?php echo strtoupper(htmlspecialchars($row['specialization'])); ?></td>
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