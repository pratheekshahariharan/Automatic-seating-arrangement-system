<?php
require_once 'db_connect.php';
require_once '../vendor/autoload.php'; // Composer's autoloader

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["excelFile"])) {
    $uploadType = $_POST['uploadType'];
    $targetFile = '../uploads/' . basename($_FILES["excelFile"]["name"]);
    
    if (move_uploaded_file($_FILES["excelFile"]["tmp_name"], $targetFile)) {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($targetFile);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        // Skip header row
        $header = array_shift($sheetData);
        $successCount = 0;
        
        if ($uploadType === 'students') {
            $sql = "INSERT INTO students (roll_no, name, class_dept) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), class_dept=VALUES(class_dept)";
            $stmt = $conn->prepare($sql);
            foreach ($sheetData as $row) {
                $stmt->bind_param("sss", $row['A'], $row['B'], $row['C']);
                if ($stmt->execute()) $successCount++;
            }
        } elseif ($uploadType === 'rooms') {
            $sql = "INSERT INTO rooms (room_name, capacity, `rows`, cols) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE capacity=VALUES(capacity), `rows`=VALUES(`rows`), cols=VALUES(cols)";
            $stmt = $conn->prepare($sql);
            foreach ($sheetData as $row) {
                $stmt->bind_param("siii", $row['A'], $row['B'], $row['C'], $row['D']);
                if ($stmt->execute()) $successCount++;
            }
        }
        
        header("location: ../admin_dashboard.php?upload_success=" . $successCount);
    } else {
        header("location: ../admin_dashboard.php?upload_error=File upload failed.");
    }
}
?>