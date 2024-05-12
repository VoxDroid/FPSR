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
        $eventStart = $_POST['event_start'];
        $eventEnd = $_POST['event_end'];

        // Additional validation
        if (!$title || !$description || !$facility || !$eventStart || !$eventEnd) {
            // If any required field is missing, redirect back to the form with an error message
            header("Location: request_event.php?error=missing_fields");
            exit();
        }

        // Validate event start and end dates
        $startDateTime = new DateTime($eventStart);
        $endDateTime = new DateTime($eventEnd);

        // Check if event start date is past the current time
        if ($startDateTime <= new DateTime()) {
            // If event start date is invalid, redirect back to the form with an error message
            header("Location: request_event.php?error=start_date_past_current_time");
            exit();
        }

        // Check if event end date is before the start date
        if ($startDateTime >= $endDateTime) {
            // If event end date is invalid, redirect back to the form with an error message
            header("Location: request_event.php?error=end_date_before_start");
            exit();
        }

        // Calculate duration based on event start and end dates
        $duration = $endDateTime->diff($startDateTime)->format('%h');

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
    <?php require_once '../PARTS/CSS.php'; ?>
</head>
<body>
<!-- Header -->
<?php require_once '../PARTS/header_EMS.php'; ?>
<!-- End Header -->
<div class="container mt-5">
<div class="container mt-5">
        <h5>
            <a href="../index.php" class="btn btn-primary">
                <img src="../SVG/house-fill.svg" alt="" class="me-2" width="16" height="16">Dashboard</a>
        </h5>
    </div>
    <h2>Request Event</h2>
    <!-- Display error message if there's any -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php if ($_GET['error'] === 'missing_fields'): ?>
                Please fill in all required fields.
            <?php elseif ($_GET['error'] === 'start_date_past_current_time'): ?>
                Event start date cannot be past the current time.
            <?php elseif ($_GET['error'] === 'end_date_before_start'): ?>
                Event end date cannot be before the start date.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <!-- Event request form -->
<form action="request_event.php" method="POST" id="eventForm">
    <div class="form-group">
        <label for="title">Event Title *</label>
        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Event Description *</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
    </div>
    <div class="form-group">
        <label for="facility">Facility *</label>
        <input type="text" class="form-control" id="facility" name="facility" value="<?php echo isset($_POST['facility']) ? htmlspecialchars($_POST['facility']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="event_start">Event Start Date and Time *</label>
        <input type="datetime-local" class="form-control" id="event_start" name="event_start" value="<?php echo isset($_POST['event_start']) ? htmlspecialchars($_POST['event_start']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="event_end">Event End Date and Time *</label>
        <input type="datetime-local" class="form-control" id="event_end" name="event_end" value="<?php echo isset($_POST['event_end']) ? htmlspecialchars($_POST['event_end']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="duration">Duration (in hours)</label>
        <input type="number" class="form-control" id="duration" name="duration" min="1" readonly value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>">
    </div>
    <button type="submit" class="btn btn-primary mt-3">Submit</button>
</form>

</div>
<!-- JS.PHP -->
<?php require_once '../PARTS/js.php'; ?>
<script>
    // Function to set min attribute of event_start input to tomorrow's date
    function setMinStartDate() {
        var currentDate = new Date();
        var tomorrowDate = new Date(currentDate.getTime() + (24 * 60 * 60 * 1000));
        // Set min attribute of event start input to tomorrow's date
        document.getElementById("event_start").min = tomorrowDate.toISOString().slice(0, 16);
    }

    // Function to set min attribute of event_end input based on event start date
    function setMinEndDate() {
        var eventStartInput = document.getElementById("event_start");
        var eventEndInput = document.getElementById("event_end");
        // Ensure event end date cannot be before event start date
        if (eventStartInput.value) {
            var startDate = new Date(eventStartInput.value);
            // Set min date for event end to event start date
            eventEndInput.min = startDate.toISOString().slice(0, 16);
        }
        // Calculate duration and fill in the input
        if (eventStartInput.value && eventEndInput.value) {
            var startDateTime = new Date(eventStartInput.value);
            var endDateTime = new Date(eventEndInput.value);
            var durationHours = Math.abs(endDateTime - startDateTime) / 36e5; // Milliseconds to hours
            document.getElementById("duration").value = Math.ceil(durationHours);
        }
    }

    // Event listener to call setMinEndDate function when event start date changes
    document.getElementById("event_start").addEventListener("change", function() {
        setMinEndDate();
    });

    // Event listener to call setMinEndDate function when event end date changes
    document.getElementById("event_end").addEventListener("change", function() {
        setMinEndDate();
    });

    // Call setMinStartDate function to set min attribute of event_start input
    setMinStartDate();
    // Call setMinEndDate function initially to set min attribute of event_end input
    setMinEndDate();
</script>

</body>
</html>
