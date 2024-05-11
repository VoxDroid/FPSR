<?php
function updateEventStatus($pdo) {
    // Get the current date
    $currentDate = date('Y-m-d');

    // Update events that have started but not yet completed to "ongoing"
    $updateOngoingQuery = "UPDATE events SET status = 'ongoing' WHERE status = 'active' AND event_start <= ? AND event_end >= ?";
    $stmtOngoing = $pdo->prepare($updateOngoingQuery);
    $stmtOngoing->execute([$currentDate, $currentDate]);

    // Update events that have ended to "completed"
    $updateCompletedQuery = "UPDATE events SET status = 'completed' WHERE status = 'ongoing' AND event_end < ?";
    $stmtCompleted = $pdo->prepare($updateCompletedQuery);
    $stmtCompleted->execute([$currentDate]);
}

// Database connection settings
$host = 'localhost';
$dbname = 'event_management_system';
$username = 'root';
$password = '';

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Call the function to update event statuses
    updateEventStatus($pdo);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
