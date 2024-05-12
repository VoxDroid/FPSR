<?php
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

// Redirect to index.php if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Redirect to index.php if user is not a regular user
if ($_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Database connection settings
$host = 'localhost';
$dbname = 'event_management_system';
$username22 = 'root';
$password = '';

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username22, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}




// Fetch user's requested events
$userID = $_SESSION['user_id'];

$queryApprovedEvents = "SELECT * FROM events WHERE user_id = :userID AND status = 'active'";
$stmtApprovedEvents = $pdo->prepare($queryApprovedEvents);
$stmtApprovedEvents->execute(['userID' => $userID]);

$queryPendingEvents = "SELECT * FROM events WHERE user_id = :userID AND status = 'pending'";
$stmtPendingEvents = $pdo->prepare($queryPendingEvents);
$stmtPendingEvents->execute(['userID' => $userID]);

$queryCompletedEvents = "SELECT * FROM events WHERE user_id = :userID AND status = 'completed'";
$stmtCompletedEvents = $pdo->prepare($queryCompletedEvents);
$stmtCompletedEvents->execute(['userID' => $userID]);

$queryDeniedEvents = "SELECT * FROM events WHERE user_id = :userID AND status = 'denied'";
$stmtDeniedEvents = $pdo->prepare($queryDeniedEvents);
$stmtDeniedEvents->execute(['userID' => $userID]);

$queryOngoingEvents = "SELECT * FROM events WHERE user_id = :userID AND status = 'ongoing'";
$stmtOngoingEvents = $pdo->prepare($queryOngoingEvents);
$stmtOngoingEvents->execute(['userID' => $userID]);

$itemsPerPage = 10; // Define the number of items per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page (default: 1)

// Fetch user's requested events
$userID = $_SESSION['user_id'];

// Fetch ongoing events with pagination
$queryOngoingEvents = "SELECT * FROM events WHERE user_id = :userID AND status = 'ongoing'";
$stmtOngoingEvents = $pdo->prepare($queryOngoingEvents);
$stmtOngoingEvents->execute(['userID' => $userID]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View My Requests</title>

    <!-- CSS.PHP -->
    <?php require_once '../PARTS/CSS.php'; ?>
</head>
<body>
<!-- Header -->
<?php require_once '../PARTS/header_EMS.php'; ?>
<!-- End Header -->

<!-- Main Content -->
<main class="py-5">
<div class="container mt-5">
<?php
    // Withdraw request if withdraw button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw'])) {
    $eventID = $_POST['event_id'];

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete associated records in event_votes table
        $queryDeleteEventVotes = "DELETE FROM event_votes WHERE event_id = :eventID";
        $stmtDeleteEventVotes = $pdo->prepare($queryDeleteEventVotes);
        $stmtDeleteEventVotes->execute(['eventID' => $eventID]);

        // Delete associated records in comment_votes table
        $queryDeleteCommentVotes = "DELETE FROM comment_votes WHERE event_id = :eventID";
        $stmtDeleteCommentVotes = $pdo->prepare($queryDeleteCommentVotes);
        $stmtDeleteCommentVotes->execute(['eventID' => $eventID]);

        // Delete associated records in comments table
        $queryDeleteComments = "DELETE FROM comments WHERE event_id = :eventID";
        $stmtDeleteComments = $pdo->prepare($queryDeleteComments);
        $stmtDeleteComments->execute(['eventID' => $eventID]);

        // Now delete the event record
        $queryWithdraw = "DELETE FROM events WHERE id = :eventID";
        $stmtWithdraw = $pdo->prepare($queryWithdraw);
        $stmtWithdraw->execute(['eventID' => $eventID]);

        // Commit the transaction
        $pdo->commit();

        // Add green success notification here if needed
        echo '<div class="alert alert-success" role="alert">Withdrawal successful!</div>';
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
        <h5>
            <a href="../index.php" class="btn btn-primary">
                <img src="../SVG/house-fill.svg" alt="" class="me-2" width="16" height="16">Dashboard</a>
        </h5>
    </div>
    <div class="container">
<!-- Ongoing Events -->
<h2>Ongoing Events</h2>
<div class="row">
    <?php
    // Pagination variables
    $ongoingItemsPerPage = 6; // Define the number of ongoing events per page
    $ongoingTotalItems = $stmtOngoingEvents->rowCount(); // Total number of ongoing events
    $ongoingTotalPages = ceil($ongoingTotalItems / $ongoingItemsPerPage); // Total number of pages

    // Calculate the offset
    $ongoingOffset = ($currentPage - 1) * $ongoingItemsPerPage;

    // Fetch ongoing events with pagination
    $queryOngoingEvents .= " LIMIT $ongoingOffset, $ongoingItemsPerPage";
    $stmtOngoingEvents = $pdo->prepare($queryOngoingEvents);
    $stmtOngoingEvents->execute(['userID' => $userID]);

    // Display ongoing events
    if ($stmtOngoingEvents->rowCount() > 0) {
        while ($event = $stmtOngoingEvents->fetch(PDO::FETCH_ASSOC)) {
            // Output ongoing event card
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $event['title'] . '</h5>';
            echo '<p class="card-text">' . $event['description'] . '</p>';
            echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
            echo '<a href="../EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary mt-3">View</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        // No ongoing events to show
        echo '<p>No ongoing events to show.</p>';
    }

    // Display pagination controls
if ($ongoingTotalPages > 1) {
    echo '<div class="col-md-12">';
    echo '<nav aria-label="Page navigation example">';
    echo '<ul class="pagination justify-content-center">';
    
    // Previous button
    $prevPage = $currentPage - 1;
    echo '<li class="page-item ' . ($currentPage == 1 ? 'disabled' : '') . '">';
    echo '<a class="page-link" href="?page=' . $prevPage . '" aria-label="Previous">';
    echo '<span aria-hidden="true">&laquo;</span>';
    echo '<span class="sr-only">Previous</span>';
    echo '</a>';
    echo '</li>';
    
    // Page numbers
    for ($i = 1; $i <= $ongoingTotalPages; $i++) {
        echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
        echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
        echo '</li>';
    }
    
    // Next button
    $nextPage = $currentPage + 1;
    echo '<li class="page-item ' . ($currentPage == $ongoingTotalPages ? 'disabled' : '') . '">';
    echo '<a class="page-link" href="?page=' . $nextPage . '" aria-label="Next">';
    echo '<span aria-hidden="true">&raquo;</span>';
    echo '<span class="sr-only">Next</span>';
    echo '</a>';
    echo '</li>';
    
    echo '</ul>';
    echo '</nav>';
    echo '</div>';
}
    ?>
</div>

<h2>Approved Events</h2>
<div class="row">
    <?php
    // Pagination variables
    $approvedItemsPerPage = 6; // Define the number of approved events per page
    $approvedTotalItems = $stmtApprovedEvents->rowCount(); // Total number of approved events
    $approvedTotalPages = ceil($approvedTotalItems / $approvedItemsPerPage); // Total number of pages

    // Calculate the offset
    $approvedOffset = ($currentPage - 1) * $approvedItemsPerPage;

    // Fetch approved events with pagination
    $queryApprovedEvents .= " LIMIT $approvedOffset, $approvedItemsPerPage";
    $stmtApprovedEvents = $pdo->prepare($queryApprovedEvents);
    $stmtApprovedEvents->execute(['userID' => $userID]);

    // Display approved events
    if ($stmtApprovedEvents->rowCount() > 0) {
        while ($event = $stmtApprovedEvents->fetch(PDO::FETCH_ASSOC)) {
            // Output approved event card
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $event['title'] . '</h5>';
            echo '<p class="card-text">' . $event['description'] . '</p>';
            echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
            echo '<a href="../EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary mt-3">View</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        // No approved events to show
        echo '<p>No approved events to show.</p>';
    }

    // Display pagination controls
    if ($approvedTotalPages > 1) {
        echo '<div class="col-md-12">';
        echo '<nav aria-label="Page navigation example">';
        echo '<ul class="pagination justify-content-center">';
        
        // Previous button
        $prevPage = $currentPage - 1;
        echo '<li class="page-item ' . ($currentPage == 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $prevPage . '" aria-label="Previous">';
        echo '<span aria-hidden="true">&laquo;</span>';
        echo '<span class="sr-only">Previous</span>';
        echo '</a>';
        echo '</li>';
        
        // Page numbers
        for ($i = 1; $i <= $approvedTotalPages; $i++) {
            echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        
        // Next button
        $nextPage = $currentPage + 1;
        echo '<li class="page-item ' . ($currentPage == $approvedTotalPages ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $nextPage . '" aria-label="Next">';
        echo '<span aria-hidden="true">&raquo;</span>';
        echo '<span class="sr-only">Next</span>';
        echo '</a>';
        echo '</li>';
        
        echo '</ul>';
        echo '</nav>';
        echo '</div>';
    }
    ?>
</div>


<!-- Pending Events -->
<h2>Pending Events</h2>
<div class="row">
    <?php
    // Pagination variables
    $pendingItemsPerPage = 6; // Define the number of pending events per page
    $pendingTotalItems = $stmtPendingEvents->rowCount(); // Total number of pending events
    $pendingTotalPages = ceil($pendingTotalItems / $pendingItemsPerPage); // Total number of pages

    // Calculate the offset
    $pendingOffset = ($currentPage - 1) * $pendingItemsPerPage;

    // Fetch pending events with pagination
    $queryPendingEvents .= " LIMIT $pendingOffset, $pendingItemsPerPage";
    $stmtPendingEvents = $pdo->prepare($queryPendingEvents);
    $stmtPendingEvents->execute(['userID' => $userID]);

    // Display pending events
    if ($stmtPendingEvents->rowCount() > 0) {
        while ($event = $stmtPendingEvents->fetch(PDO::FETCH_ASSOC)) {
            // Output pending event card
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $event['title'] . '</h5>';
            echo '<p class="card-text">' . $event['description'] . '</p>';
            echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
            echo '<a href="../EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary mt-3 me-2">View</a>';
            echo '<button type="button" class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#withdrawModal' . $event['id'] . '">Withdraw Request</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            // Withdraw Modal
            echo '<div class="modal fade" id="withdrawModal' . $event['id'] . '" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">';
            echo '<div class="modal-dialog">';
            echo '<div class="modal-content">';
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title" id="withdrawModalLabel">Withdraw Request</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">';
            echo 'Are you sure you want to withdraw this request?';
            echo '</div>';
            echo '<div class="modal-footer">';
            echo '<form method="post">';
            echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
            echo '<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>';
            echo '<button type="submit" name="withdraw" class="btn btn-danger">Withdraw</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            // End Withdraw Modal
        }
    } else {
        // No pending events to show
        echo '<p>No pending events to show.</p>';
    }

    // Display pagination controls
    if ($pendingTotalPages > 1) {
        echo '<div class="col-md-12">';
        echo '<nav aria-label="Page navigation example">';
        echo '<ul class="pagination justify-content-center">';
        
        // Previous button
        $prevPage = $currentPage - 1;
        echo '<li class="page-item ' . ($currentPage == 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $prevPage . '" aria-label="Previous">';
        echo '<span aria-hidden="true">&laquo;</span>';
        echo '<span class="sr-only">Previous</span>';
        echo '</a>';
        echo '</li>';
        
        // Page numbers
        for ($i = 1; $i <= $pendingTotalPages; $i++) {
            echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        
        // Next button
        $nextPage = $currentPage + 1;
        echo '<li class="page-item ' . ($currentPage == $pendingTotalPages ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $nextPage . '" aria-label="Next">';
        echo '<span aria-hidden="true">&raquo;</span>';
        echo '<span class="sr-only">Next</span>';
        echo '</a>';
        echo '</li>';
        
        echo '</ul>';
        echo '</nav>';
        echo '</div>';
    }
    ?>
</div>


<h2>Completed Events</h2>
<div class="row">
    <?php
    // Pagination variables
    $completedItemsPerPage = 6; // Define the number of completed events per page
    $completedTotalItems = $stmtCompletedEvents->rowCount(); // Total number of completed events
    $completedTotalPages = ceil($completedTotalItems / $completedItemsPerPage); // Total number of pages

    // Calculate the offset
    $completedOffset = ($currentPage - 1) * $completedItemsPerPage;

    // Fetch completed events with pagination
    $queryCompletedEvents .= " LIMIT $completedOffset, $completedItemsPerPage";
    $stmtCompletedEvents = $pdo->prepare($queryCompletedEvents);
    $stmtCompletedEvents->execute(['userID' => $userID]);

    // Display completed events
    if ($stmtCompletedEvents->rowCount() > 0) {
        while ($event = $stmtCompletedEvents->fetch(PDO::FETCH_ASSOC)) {
            // Output completed event card
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $event['title'] . '</h5>';
            echo '<p class="card-text">' . $event['description'] . '</p>';
            echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
            echo '<a href="../EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary mt-3">View</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        // No completed events to show
        echo '<p>No completed events to show.</p>';
    }

    // Display pagination controls
    if ($completedTotalPages > 1) {
        echo '<div class="col-md-12">';
        echo '<nav aria-label="Page navigation example">';
        echo '<ul class="pagination justify-content-center">';
        
        // Previous button
        $prevPage = $currentPage - 1;
        echo '<li class="page-item ' . ($currentPage == 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $prevPage . '" aria-label="Previous">';
        echo '<span aria-hidden="true">&laquo;</span>';
        echo '<span class="sr-only">Previous</span>';
        echo '</a>';
        echo '</li>';
        
        // Page numbers
        for ($i = 1; $i <= $completedTotalPages; $i++) {
            echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        
        // Next button
        $nextPage = $currentPage + 1;
        echo '<li class="page-item ' . ($currentPage == $completedTotalPages ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $nextPage . '" aria-label="Next">';
        echo '<span aria-hidden="true">&raquo;</span>';
        echo '<span class="sr-only">Next</span>';
        echo '</a>';
        echo '</li>';
        
        echo '</ul>';
        echo '</nav>';
        echo '</div>';
    }
    ?>
</div>


<h2>Denied Events</h2>
<div class="row">
    <?php
    // Pagination variables
    $deniedItemsPerPage = 6; // Define the number of denied events per page
    $deniedTotalItems = $stmtDeniedEvents->rowCount(); // Total number of denied events
    $deniedTotalPages = ceil($deniedTotalItems / $deniedItemsPerPage); // Total number of pages

    // Calculate the offset
    $deniedOffset = ($currentPage - 1) * $deniedItemsPerPage;

    // Fetch denied events with pagination
    $queryDeniedEvents .= " LIMIT $deniedOffset, $deniedItemsPerPage";
    $stmtDeniedEvents = $pdo->prepare($queryDeniedEvents);
    $stmtDeniedEvents->execute(['userID' => $userID]);

    // Display denied events
    if ($stmtDeniedEvents->rowCount() > 0) {
        while ($event = $stmtDeniedEvents->fetch(PDO::FETCH_ASSOC)) {
            // Output denied event card
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $event['title'] . '</h5>';
            echo '<p class="card-text">' . $event['description'] . '</p>';
            echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
            echo '<a href="../EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary mt-3">View</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        // No denied events to show
        echo '<p>No denied events to show.</p>';
    }

    // Display pagination controls
    if ($deniedTotalPages > 1) {
        echo '<div class="col-md-12">';
        echo '<nav aria-label="Page navigation example">';
        echo '<ul class="pagination justify-content-center">';
        
        // Previous button
        $prevPage = $currentPage - 1;
        echo '<li class="page-item ' . ($currentPage == 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $prevPage . '" aria-label="Previous">';
        echo '<span aria-hidden="true">&laquo;</span>';
        echo '<span class="sr-only">Previous</span>';
        echo '</a>';
        echo '</li>';
        
        // Page numbers
        for ($i = 1; $i <= $deniedTotalPages; $i++) {
            echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        
        // Next button
        $nextPage = $currentPage + 1;
        echo '<li class="page-item ' . ($currentPage == $deniedTotalPages ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?page=' . $nextPage . '" aria-label="Next">';
        echo '<span aria-hidden="true">&raquo;</span>';
        echo '<span class="sr-only">Next</span>';
        echo '</a>';
        echo '</li>';
        
        echo '</ul>';
        echo '</nav>';
        echo '</div>';
    }
    ?>
</div>

    </div>
</main>
<!-- End Main Content -->

<!-- JS.PHP -->
<?php require_once '../PARTS/js.php'; ?>

</body>
</html>
