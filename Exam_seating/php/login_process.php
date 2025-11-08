<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type'];
    $table = '';
    $redirect_page = '';

    // Determine which table to query
    if ($login_type === 'admin') {
        $table = 'admins';
        $redirect_page = '../admin_dashboard.php';
    } elseif ($login_type === 'faculty') {
        $table = 'faculty';
        $redirect_page = '../faculty_dashboard.php';
    } elseif ($login_type === 'student') {
        $table = 'students';
        $redirect_page = '../student_dashboard.php';
    }

    if ($table) {
        $id_column = ($table === 'students') ? 'roll_no' : 'id';
        
        $sql = "SELECT $id_column, name, password FROM $table WHERE $id_column = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $id);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($db_id, $db_name, $db_password);
                    
                    // --- THIS IS THE CHANGED LINE ---
                    // It now directly compares the plain text passwords.
                    if ($stmt->fetch() && $password === $db_password) {
                        
                        // Passwords match, start the session
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $db_id;
                        $_SESSION["name"] = $db_name;
                        $_SESSION["role"] = $login_type;
                        header("location: " . $redirect_page);
                        exit();
                    }
                }
            }
            $stmt->close();
        }
    }

    // If login fails for any reason, redirect back to the correct login page.
    $login_page = '../' . $login_type . '_login.php';
    header("location: " . $login_page . "?error=Invalid credentials.");
    $conn->close();
}
?>