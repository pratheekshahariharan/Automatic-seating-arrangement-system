<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}
$result = $conn->query("SELECT room_name, capacity, `rows`, cols FROM rooms");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Rooms</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>View Rooms</h1></div>
        <div class="content-wrapper">
            <div class="search-and-back">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for rooms...">
                <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
            </div>
            <div class="table-container">
                <table id="dataTable">
                    <thead><tr><th>Room Name</th><th>Capacity</th><th>Rows</th><th>Columns</th></tr></thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['capacity']); ?></td>
                            <td><?php echo htmlspecialchars($row['rows']); ?></td>
                            <td><?php echo htmlspecialchars($row['cols']); ?></td>
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
