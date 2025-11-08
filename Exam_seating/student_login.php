<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-card">
        <h2>LOGIN</h2>
        <form action="php/login_process.php" method="post">
            <?php if (isset($_GET['error'])) echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>'; ?>
            <input type="hidden" name="login_type" value="student">
            <div class="form-group">
                <label for="student-id">Roll No:</label>
                <input type="text" name="id" id="student-id" required>
            </div>
            <div class="form-group">
                <label for="student-password">Password:</label>
                <input type="password" name="password" id="student-password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
            <a href="index.php" class="back-link">Back to main portal</a>
        </form>
    </div>
</body>
</html>
