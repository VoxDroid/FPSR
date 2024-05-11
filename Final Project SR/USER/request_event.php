<?php
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

// Check if user is logged in and is a regular user
if (!(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'user')) {
    // Redirect to index.php if not logged in or not a regular user
    header("Location: ../index.php");
    exit();
}

// Database connection settings
$host = 'localhost';
$dbname = 'event_management_system';
$username22 = 'root';
$password = '';

// Function to redirect to index.php
function redirectToIndex() {
    header("Location: ../index.php");
    exit();
}

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username22, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate and sanitize user inputs
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $facility = filter_input(INPUT_POST, 'facility', FILTER_SANITIZE_STRING);
        $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
        $eventStart = $_POST['event_start'];
        $eventEnd = $_POST['event_end'];

        // Additional validation
        if (!$title || !$description || !$facility || !$duration || !$eventStart || !$eventEnd) {
            // If any required field is missing, redirect back to the form with an error message
            header("Location: request_event.php?error=missing_fields");
            exit();
        }

        // Validate event start and end dates
        $startDateTime = new DateTime($eventStart);
        $endDateTime = new DateTime($eventEnd);

        if ($startDateTime >= $endDateTime) {
            // If event end date is invalid, redirect back to the form with an error message
            header("Location: request_event.php?error=invalid_dates");
            exit();
        }

        // Insert the event into the database
        $stmt = $pdo->prepare("INSERT INTO events (user_id, title, description, facility, duration, status, event_start, event_end) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $description, $facility, $duration, $eventStart, $eventEnd]);

        // Redirect to index.php after successfully submitting the event
        header("Location: ../index.php?success=event_submitted");
        exit();
    }
} catch(PDOException $e) {
    // Redirect to index.php if there's an error
    redirectToIndex();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Event</title>
    <!-- CSS.PHP -->
    <?php
    require_once '../PARTS/CSS.php';
    ?>
</head>
<body>
<!-- Header -->
<?php
require_once '../PARTS/header_EMS.php';
?>
<!-- End Header -->
<div class="container mt-5">
    <h2>Request Event</h2>
    <!-- Display error message if there's any -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php if ($_GET['error'] === 'missing_fields'): ?>
                Please fill in all required fields.
            <?php elseif ($_GET['error'] === 'invalid_dates'): ?>
                Invalid event dates. Please make sure the end date is after the start date.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <!-- Event request form -->
    <form action="request_event.php" method="POST">
        <div class="form-group">
            <label for="title">Event Title *</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Event Description *</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="facility">Facility *</label>
            <input type="text" class="form-control" id="facility" name="facility" required>
        </div>
        <div class="form-group">
            <label for="duration">Duration (in hours) *</label>
            <input type="number" class="form-control" id="duration" name="duration" min="1" required>
        </div>
        <div class="form-group">
            <label for="event_start">Event Start Date and Time *</label>
            <input type="datetime-local" class="form-control" id="event_start" name="event_start" required>
        </div>
        <div class="form-group">
            <label for="event_end">Event End Date and Time *</label>
            <input type="datetime-local" class="form-control" id="event_end" name="event_end" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Submit</button>
    </form>
</div>
<!-- JS.PHP -->
<?php
require_once '../PARTS/js.php';
?>
</body>
</html>
