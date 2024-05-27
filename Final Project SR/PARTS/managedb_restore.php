<?php
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$restoreMessage = "";

// Function to handle database restoration
if (isset($_POST['restore_database'])) {
    require '../PARTS/db_connection_settings.php';

    // Ensure a file was uploaded
    if (!empty($_FILES['backup_file']['tmp_name']) && is_uploaded_file($_FILES['backup_file']['tmp_name'])) {
        $backupFile = $_FILES['backup_file']['tmp_name'];

        // Command to execute mysql to restore database
        $command = "\"mysql\" --host={$host} --user={$username} --password={$password} {$dbname} < {$backupFile}";

        // Execute the command to restore database
        exec($command . ' 2>&1', $output, $returnValue);

        // If mysql restore fails, use the executable path as a fallback (e.g., for Windows)
        if ($returnValue !== 0) {
            // Full path to mysql.exe
            $mysqlPath = 'C:\xampp\mysql\bin\mysql.exe'; // Adjust this path to match your environment

            // Command to execute using mysql.exe
            $command = "\"{$mysqlPath}\" --host={$host} --user={$username} --password={$password} {$dbname} < {$backupFile}";

            // Execute the command to restore database using mysql.exe
            exec($command . ' 2>&1', $output, $returnValue);
        }

        // Check if restore was successful
        if ($returnValue === 0) {
            $restoreMessage = '<div class="alert alert-success" role="alert">Database restore successful.</div>';
            $_SESSION['success_message'] = "Database restore successful.";
            header("Location: manage_database.php");
            exit();
        } else {
            // Prepare error message handling
            $errorMessage = "Database restore failed. Command output:\n" . implode("\n", $output);
            $restoreMessage = '<div class="alert alert-danger" role="alert">' . htmlspecialchars($errorMessage) . '</div>';
            $_SESSION['error_messages'][] = $errorMessage;
            header("Location: manage_database.php");
            exit();
        }
    } else {
        // Handle case where no file was uploaded
        $restoreMessage = '<div class="alert alert-danger" role="alert">No backup file uploaded.</div>';
        $_SESSION['error_messages'][] = "No backup file uploaded.";
        header("Location: manage_database.php");
        exit();
    }
}
?>