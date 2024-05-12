<?php

require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}


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

// Fetch all events
$query = "SELECT * FROM events";
$stmt = $pdo->query($query);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests - Admin Control Panel</title>
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

<!-- Main Content -->
<div class="container mt-5">

<?php

if (isset($_POST['submit_approval'])) {
    $eventID = $_POST['event_id'];
    $adminRemark = $_POST['admin_remark'];
    $approvalStatus = $_POST['approval_status'];

    // Update event status and remarks
    $updateEventQuery = "UPDATE events SET status = :status, remarks = :remarks WHERE id = :event_id";
    $updateStmt = $pdo->prepare($updateEventQuery);
    $updateStmt->execute(['status' => $approvalStatus, 'remarks' => $adminRemark, 'event_id' => $eventID]);
    echo '<div class="alert alert-success" role="alert">Event submission successful!</div>';
}

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
        echo '<div class="alert alert-success" role="alert">Deletion successful!</div>';
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
    <!-- Pending Events Section -->
    <div class="table-container">
        <div class="table-title">Pending Events</div>
        <div class="table-wrapper">
            <?php
            // Pagination
            $perPagePending = 10; // Number of items per page for pending events
            $currentPagePending = isset($_GET['page_pending']) ? $_GET['page_pending'] : 1; // Current page number for pending events
            $offsetPending = ($currentPagePending - 1) * $perPagePending; // Offset for SQL query for pending events

            // Fetch pending events with pagination
            $pendingEventsQuery = "SELECT * FROM events WHERE status = 'pending' LIMIT $perPagePending OFFSET $offsetPending";
            $pendingStmt = $pdo->query($pendingEventsQuery);
            $pendingEvents = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

            // Count total pending events
            $totalPendingEventsQuery = "SELECT COUNT(*) AS total FROM events WHERE status = 'pending'";
            $totalPendingStmt = $pdo->query($totalPendingEventsQuery);
            $totalPendingEvents = $totalPendingStmt->fetch(PDO::FETCH_ASSOC)['total'];

            if ($totalPendingEvents > 0) {
                ?>
                <table class="table table-bordered table-striped mb-0">
                    <!-- Table header -->
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Facility</th>
                        <th>Duration</th>
                        <th>Date Requested</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody>
                    <!-- Loop through pending events -->
                    <?php foreach ($pendingEvents as $event): ?>
                        <tr>
                            <td><?= $event['title'] ?></td>
                            <td><?= $event['description'] ?></td>
                            <td><?= $event['facility'] ?></td>
                            <td><?= $event['duration'] ?></td>
                            <td><?= $event['date_requested'] ?></td>
                            <td>
                                <a class="btn btn-primary btn-sm mr-1 view-button" href="../EMS/event_details.php?event_id=<?= $event['id'] ?>">View</a>
                                <button class="btn btn-success btn-sm mr-1 approve-button" data-bs-toggle="modal" data-bs-target="#eventDetailsModal<?= $event['id'] ?>">Check</button>
                                <button type="button" class="btn btn-danger btn-sm delete-button" data-bs-toggle="modal" data-bs-target="#withdrawModal<?= $event['id'] ?>">Delete Request</button>

                                <?php
                                // Deletion Modal
                                echo '<div class="modal fade" id="withdrawModal' . $event['id'] . '" tabindex="-1" aria-labelledby="withdrawModalLabel' . $event['id'] . '" aria-hidden="true">';
                                echo '<div class="modal-dialog">';
                                echo '<div class="modal-content">';
                                echo '<div class="modal-header">';
                                echo '<h5 class="modal-title" id="withdrawModalLabel' . $event['id'] . '">Delete Request</h5>';
                                echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                                echo '</div>';
                                echo '<div class="modal-body">';
                                echo 'Are you sure you want to delete this request?';
                                echo '</div>';
                                echo '<div class="modal-footer">';
                                echo '<form method="post">';
                                echo '<input type="hidden" name="event_id" value="' . $event['id'] . '">';
                                echo '<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>';
                                echo '<button type="submit" name="withdraw" class="btn btn-danger">Delete</button>';
                                echo '</form>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                // End Withdraw Modal
                                ?>

                                <!-- Modals for event details -->
<?php foreach ($pendingEvents as $event): ?>
    <?php
    // Fetch user details for the event
    $userQuery = "SELECT * FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute(['user_id' => $event['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="modal fade" id="eventDetailsModal<?= $event['id'] ?>" tabindex="-1" aria-labelledby="eventDetailsModalLabel<?= $event['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailsModalLabel<?= $event['id'] ?>">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Display user details -->
                    <p><strong>User:</strong> <?= $user['username'] ?></p>
                    <!-- Display event details -->
                    <p><strong>Title:</strong> <?= $event['title'] ?></p>
                    <p><strong>Description:</strong> <?= $event['description'] ?></p>
                    <p><strong>Facility:</strong> <?= $event['facility'] ?></p>
                    <p><strong>Duration:</strong> <?= $event['duration'] ?></p>
                    <p><strong>Date Requested:</strong> <?= $event['date_requested'] ?></p>
                    <p><strong>Event Start:</strong> <?= $event['event_start'] ?></p>
                    <p><strong>Event End:</strong> <?= $event['event_end'] ?></p>
                    <p><strong>Status:</strong> <?= $event['status'] ?></p>
                    <p><strong>Likes:</strong> <?= $event['likes'] ?></p>
                    <p><strong>Dislikes:</strong> <?= $event['dislikes'] ?></p>
                    <!-- Form for admin's comment and approval/denial -->
                    <form method="post">
                        <div class="mb-3">
                            <label for="adminRemark" class="form-label">Admin's Comment:</label>
                            <textarea class="form-control" id="adminRemark" name="admin_remark" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="approvalStatus" class="form-label">Approval Status:</label>
                            <select class="form-select" id="approvalStatus" name="approval_status">
                                <option value="active">Approve</option>
                                <option value="denied">Deny</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" name="submit_approval">Submit</button>
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>


                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                // Pagination links for pending events
                $totalPagesPending = ceil($totalPendingEvents / $perPagePending);
                ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-3">
                        <?php if ($currentPagePending > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_pending=<?= $currentPagePending - 1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php
                        $startPagePending = max(1, $currentPagePending - 2);
                        $endPagePending = min($totalPagesPending, $startPagePending + 4);
                        for ($i = $startPagePending; $i <= $endPagePending; $i++): ?>
                            <li class="page-item <?= $currentPagePending == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page_pending=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($currentPagePending < $totalPagesPending): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_pending=<?= $currentPagePending + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php } else {
                // No pending events
                echo "<p class='mt-3'>No pending events found.</p>";
            }
            ?>
        </div>
    </div>


    <!-- Ongoing Events Section -->
    <div class="table-container mt-5">
        <div class="table-title">Ongoing Events</div>
        <div class="table-wrapper">
            <?php
            // Pagination
            $perPage = 10; // Number of items per page
            $currentPage = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number
            $offset = ($currentPage - 1) * $perPage; // Offset for SQL query

            // Fetch ongoing events with pagination
            $ongoingEventsQuery = "SELECT * FROM events WHERE status = 'ongoing' LIMIT $perPage OFFSET $offset";
            $ongoingStmt = $pdo->query($ongoingEventsQuery);
            $ongoingEvents = $ongoingStmt->fetchAll(PDO::FETCH_ASSOC);

            // Count total ongoing events
            $totalOngoingEventsQuery = "SELECT COUNT(*) AS total FROM events WHERE status = 'ongoing'";
            $totalOngoingStmt = $pdo->query($totalOngoingEventsQuery);
            $totalOngoingEvents = $totalOngoingStmt->fetch(PDO::FETCH_ASSOC)['total'];

            if ($totalOngoingEvents > 0) {
                ?>
                <table class="table table-bordered table-striped mb-0">
                    <!-- Table header -->
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Facility</th>
                            <th>Duration</th>
                            <th>Date Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody>
                        <!-- Loop through ongoing events -->
                        <?php foreach ($ongoingEvents as $event): ?>
                            <tr>
                                <td><?= $event['title'] ?></td>
                                <td><?= $event['description'] ?></td>
                                <td><?= $event['facility'] ?></td>
                                <td><?= $event['duration'] ?></td>
                                <td><?= $event['date_requested'] ?></td>
                                <td>
                                    <a class="btn btn-primary btn-sm mr-1 view-button" href="../EMS/event_details.php?event_id=<?= $event['id'] ?>">View</a>
                                    <button class="btn btn-danger btn-sm delete-button">Delete</button>
                                    <!-- No action buttons for ongoing events -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                // Pagination links
                $totalPages = ceil($totalOngoingEvents / $perPage);
                ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-3">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php } else {
                // No ongoing events
                echo "<p class='mt-3'>No ongoing events found.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Approved Events Section -->
<div class="table-container mt-5">
    <div class="table-title">Approved Events</div>
    <div class="table-wrapper">
        <?php
        // Pagination
        $perPageApproved = 10; // Number of items per page for approved events
        $currentPageApproved = isset($_GET['page_approved']) ? $_GET['page_approved'] : 1; // Current page number for approved events
        $offsetApproved = ($currentPageApproved - 1) * $perPageApproved; // Offset for SQL query for approved events

        // Fetch approved events with pagination
        $approvedEventsQuery = "SELECT * FROM events WHERE status = 'active' LIMIT $perPageApproved OFFSET $offsetApproved";
        $approvedStmt = $pdo->query($approvedEventsQuery);
        $approvedEvents = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total approved events
        $totalApprovedEventsQuery = "SELECT COUNT(*) AS total FROM events WHERE status = 'active'";
        $totalApprovedStmt = $pdo->query($totalApprovedEventsQuery);
        $totalApprovedEvents = $totalApprovedStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalApprovedEvents > 0) {
            ?>
            <table class="table table-bordered table-striped mb-0">
                <!-- Table header -->
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Facility</th>
                    <th>Duration</th>
                    <th>Date Requested</th>
                    <th>Action</th>
                </tr>
                </thead>
                <!-- Table body -->
                <tbody>
                <!-- Loop through approved events -->
                <?php foreach ($approvedEvents as $event): ?>
                    <tr>
                        <td><?= $event['title'] ?></td>
                        <td><?= $event['description'] ?></td>
                        <td><?= $event['facility'] ?></td>
                        <td><?= $event['duration'] ?></td>
                        <td><?= $event['date_requested'] ?></td>
                        <td>
                            <a class="btn btn-primary btn-sm mr-1 view-button" href="../EMS/event_details.php?event_id=<?= $event['id'] ?>">View</a>
                            <button class="btn btn-danger btn-sm delete-button">Delete</button>
                            <!-- No action buttons for approved events -->
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Pagination links for approved events
            $totalPagesApproved = ceil($totalApprovedEvents / $perPageApproved);
            ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-3">
                    <?php if ($currentPageApproved > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page_approved=<?= $currentPageApproved - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                    $startPageApproved = max(1, $currentPageApproved - 2);
                    $endPageApproved = min($totalPagesApproved, $startPageApproved + 4);
                    for ($i = $startPageApproved; $i <= $endPageApproved; $i++): ?>
                        <li class="page-item <?= $currentPageApproved == $i ? 'active' : '' ?>">
                            <a class="page-link" href="?page_approved=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($currentPageApproved < $totalPagesApproved): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page_approved=<?= $currentPageApproved + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php } else {
            // No approved events
            echo "<p class='mt-3'>No approved events found.</p>";
        }
        ?>
    </div>
</div>

    <!-- Completed Events Section -->
<div class="table-container mt-5">
    <div class="table-title">Completed Events</div>
    <div class="table-wrapper">
        <?php
        // Pagination
        $perPageCompleted = 10; // Number of items per page for completed events
        $currentPageCompleted = isset($_GET['page_completed']) ? $_GET['page_completed'] : 1; // Current page number for completed events
        $offsetCompleted = ($currentPageCompleted - 1) * $perPageCompleted; // Offset for SQL query for completed events

        // Fetch completed events with pagination
        $completedEventsQuery = "SELECT * FROM events WHERE status = 'completed' LIMIT $perPageCompleted OFFSET $offsetCompleted";
        $completedStmt = $pdo->query($completedEventsQuery);
        $completedEvents = $completedStmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total completed events
        $totalCompletedEventsQuery = "SELECT COUNT(*) AS total FROM events WHERE status = 'completed'";
        $totalCompletedStmt = $pdo->query($totalCompletedEventsQuery);
        $totalCompletedEvents = $totalCompletedStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalCompletedEvents > 0) {
            ?>
            <table class="table table-bordered table-striped mb-0">
                <!-- Table header -->
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Facility</th>
                    <th>Duration</th>
                    <th>Date Requested</th>
                    <th>Action</th>
                </tr>
                </thead>
                <!-- Table body -->
                <tbody>
                <!-- Loop through completed events -->
                <?php foreach ($completedEvents as $event): ?>
                    <tr>
                        <td><?= $event['title'] ?></td>
                        <td><?= $event['description'] ?></td>
                        <td><?= $event['facility'] ?></td>
                        <td><?= $event['duration'] ?></td>
                        <td><?= $event['date_requested'] ?></td>
                        <td>
                            <a class="btn btn-primary btn-sm mr-1 view-button" href="../EMS/event_details.php?event_id=<?= $event['id'] ?>">View</a>
                            <button class="btn btn-danger btn-sm delete-button">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Pagination links for completed events
            $totalPagesCompleted = ceil($totalCompletedEvents / $perPageCompleted);
            ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-3">
                    <?php if ($currentPageCompleted > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page_completed=<?= $currentPageCompleted - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                    $startPageCompleted = max(1, $currentPageCompleted - 2);
                    $endPageCompleted = min($totalPagesCompleted, $startPageCompleted + 4);
                    for ($i = $startPageCompleted; $i <= $endPageCompleted; $i++): ?>
                        <li class="page-item <?= $currentPageCompleted == $i ? 'active' : '' ?>">
                            <a class="page-link" href="?page_completed=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($currentPageCompleted < $totalPagesCompleted): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page_completed=<?= $currentPageCompleted + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php } else {
            // No completed events
            echo "<p class='mt-3'>No completed events found.</p>";
        }
        ?>
    </div>
</div>


    <!-- Denied Events Section -->
<div class="table-container mt-5">
    <div class="table-title">Denied Events</div>
    <div class="table-wrapper">
        <?php
        // Pagination
        $perPageDenied = 10; // Number of items per page for denied events
        $currentPageDenied = isset($_GET['page_denied']) ? $_GET['page_denied'] : 1; // Current page number for denied events
        $offsetDenied = ($currentPageDenied - 1) * $perPageDenied; // Offset for SQL query for denied events

        // Fetch denied events with pagination
        $deniedEventsQuery = "SELECT * FROM events WHERE status = 'denied' LIMIT $perPageDenied OFFSET $offsetDenied";
        $deniedStmt = $pdo->query($deniedEventsQuery);
        $deniedEvents = $deniedStmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total denied events
        $totalDeniedEventsQuery = "SELECT COUNT(*) AS total FROM events WHERE status = 'denied'";
        $totalDeniedStmt = $pdo->query($totalDeniedEventsQuery);
        $totalDeniedEvents = $totalDeniedStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalDeniedEvents > 0) {
            ?>
            <table class="table table-bordered table-striped mb-0">
                <!-- Table header -->
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Facility</th>
                    <th>Duration</th>
                    <th>Date Requested</th>
                    <th>Action</th>
                </tr>
                </thead>
                <!-- Table body -->
                <tbody>
                <!-- Loop through denied events -->
                <?php foreach ($deniedEvents as $event): ?>
                    <tr>
                        <td><?= $event['title'] ?></td>
                        <td><?= $event['description'] ?></td>
                        <td><?= $event['facility'] ?></td>
                        <td><?= $event['duration'] ?></td>
                        <td><?= $event['date_requested'] ?></td>
                        <td>
                            <a class="btn btn-primary btn-sm mr-1 view-button" href="../EMS/event_details.php?event_id=<?= $event['id'] ?>">View</a>
                            <button class="btn btn-danger btn-sm delete-button">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Pagination links for denied events
            $totalPagesDenied = ceil($totalDeniedEvents / $perPageDenied);
            ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-3">
                    <?php if ($currentPageDenied > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page_denied=<?= $currentPageDenied - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                    $startPageDenied = max(1, $currentPageDenied - 2);
                    $endPageDenied = min($totalPagesDenied, $startPageDenied + 4);
                    for ($i = $startPageDenied; $i <= $endPageDenied; $i++): ?>
                        <li class="page-item <?= $currentPageDenied == $i ? 'active' : '' ?>">
                            <a class="page-link" href="?page_denied=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($currentPageDenied < $totalPagesDenied): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page_denied=<?= $currentPageDenied + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php } else {
            // No denied events
            echo "<p class='mt-3'>No denied events found.</p>";
        }
        ?>
    </div>
</div>
<!-- JS.PHP -->
<?php require_once '../PARTS/js.php'; ?>
</body>
</html>
