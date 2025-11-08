<?php
require_once 'php/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}
// Fetch courses to populate the dropdown
$courses = $conn->query("SELECT course_id, name, dept, semester FROM courses ORDER BY dept, semester, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Exam Schedule</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="main-content" style="margin-left: 0;">
        <div class="header"><h1>Add Exam Schedule</h1></div>
        <div class="content-wrapper">
            <div class="card-container" style="display: block;"> <div class="form-card">
                    <form action="php/add_schedule_process.php" method="post">
                        
                        <?php if (isset($_GET['success'])): ?>
                            <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
                        <?php endif; ?>
                        <?php if (isset($_GET['error'])): ?>
                            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="course_id">Select Course:</label>
                            <select name="course_id" id="course_id" required>
                                <option value="">-- Choose a Course --</option>
                                <?php while($course = $courses->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                        <?php echo htmlspecialchars($course['name']) . " (" . htmlspecialchars($course['dept']) . " - Sem " . htmlspecialchars($course['semester']) . ")"; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="exam_name">Exam Name:</label>
                            <input type="text" name="exam_name" id="exam_name" placeholder="e.g., Midterm Exam" required>
                        </div>

                        <div class="form-group">
                            <label for="exam_date">Exam Date:</label>
                            <input type="date" name="exam_date" id="exam_date" required>
                            <small>You can only schedule an exam between 2 weeks and 1 month from today.</small>
                        </div>

                        <div class="form-group">
                            <label for="start_time">Start Time:</label>
                            <input type="time" name="start_time" id="start_time" required>
                        </div>
                        
                        <div class="form-actions">
                             <button type="submit" class="form-button">Add Schedule</button>
                             <a href="admin_dashboard.php" class="back-button">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to enforce the date range
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('exam_date');
            
            const today = new Date();
            
            // Calculate minimum date (2 weeks from today)
            const minDate = new Date();
            minDate.setDate(today.getDate() + 14);
            
            // Calculate maximum date (1 month from today)
            const maxDate = new Date();
            maxDate.setMonth(today.getMonth() + 1);

            // Format dates as YYYY-MM-DD for the input attributes
            const minDateString = minDate.toISOString().split('T')[0];
            const maxDateString = maxDate.toISOString().split('T')[0];

            dateInput.setAttribute('min', minDateString);
            dateInput.setAttribute('max', maxDateString);
        });
    </script>
</body>
</html>