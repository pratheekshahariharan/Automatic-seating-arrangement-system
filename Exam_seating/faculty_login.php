<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-card">
        <h2>LOGIN</h2>
        <form action="php/login_process.php" method="post">
            <?php if (isset($_GET['error'])) echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>'; ?>
            <input type="hidden" name="login_type" value="faculty">
            <div class="form-group">
                <label for="faculty-id">Faculty ID</label>
                <input type="text" name="id" id="faculty-id" required>
            </div>
            <div class="form-group">
                <label for="faculty-password">Password</label>
                <input type="password" name="password" id="faculty-password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
            <a href="index.php" class="back-link">Back to main portal</a>
        </form>
    </div>
</body>
</html>
