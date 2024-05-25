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
require_once '../PARTS/header.php';
?>

<!-- Main Content -->
<div class="container mt-5 flex-grow-1">
<input type="text" id="searchInput" class="form-control mb-3" placeholder="Search...">
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
    <div class="table-container pending-section" >
        <div class="table-title" >Pending Events</div>
        <div class="table-wrapper">
            <?php
                // Pagination
                $perPagePending = 10; // Number of items per page for pending events
                $currentPagePending = isset($_GET['page_pending']) ? $_GET['page_pending'] : 1; // Current page number for pending events
                $offsetPending = ($currentPagePending - 1) * $perPagePending; // Offset for SQL query for pending events

                // Sort parameters
                $sortColumn = isset($_GET['pending_sort']) ? $_GET['pending_sort'] : 'date_requested';
                $sortOrder = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

                // Fetch pending events with pagination and sorting
                $pendingEventsQuery = "SELECT * FROM events WHERE status = 'pending' ORDER BY $sortColumn $sortOrder LIMIT $perPagePending OFFSET $offsetPending";
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
                            <th>Title 
                                <a href="?pending_sort=title&order=<?= $sortColumn === 'title' && strtolower($sortOrder) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumn === 'title' && strtolower($sortOrder) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrder === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Description 
                                <a href="?pending_sort=description&order=<?= $sortColumn === 'description' && strtolower($sortOrder) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumn === 'description' && strtolower($sortOrder) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrder === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Facility 
                                <a href="?pending_sort=facility&order=<?= $sortColumn === 'facility' && strtolower($sortOrder) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumn === 'facility' && strtolower($sortOrder) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrder === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Duration 
                                <a href="?pending_sort=duration&order=<?= $sortColumn === 'duration' && strtolower($sortOrder) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumn === 'duration' && strtolower($sortOrder) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrder === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Date Requested 
                                <a href="?pending_sort=date_requested&order=<?= $sortColumn === 'date_requested' && strtolower($sortOrder) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumn === 'date_requested' && strtolower($sortOrder) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrder === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody>
                    <!-- Loop through pending events -->
                    <?php foreach ($pendingEvents as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title'] )?></td>
                            <td><?= htmlspecialchars($event['description'])?></td>
                            <td><?= htmlspecialchars($event['facility'])?></td>
                            <td><?= htmlspecialchars($event['duration'])?></td>
                            <td><?= htmlspecialchars($event['date_requested']) ?></td>
                            <td>
                                <a class="btn btn-primary btn-sm mr-1 view-button" href="../EMS/event_details.php?event_id=<?= $event['id'] ?>">View</a>
                                <button class="btn btn-success btn-sm mr-1 approve-button" data-bs-toggle="modal" data-bs-target="#eventDetailsModal<?= $event['id'] ?>">Check</button>
                                <button type="button" class="btn btn-danger btn-sm delete-button" data-bs-toggle="modal" data-bs-target="#withdrawModal<?= $event['id'] ?>">Delete</button>

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
                    <p><strong>User:</strong> <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></p>
                    <!-- Display event details -->
                    <p><strong>Title:</strong> <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Facility:</strong> <?= htmlspecialchars($event['facility'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Duration:</strong> <?= htmlspecialchars($event['duration'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Date Requested:</strong> <?= htmlspecialchars($event['date_requested'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Event Start:</strong> <?= htmlspecialchars($event['event_start'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Event End:</strong> <?= htmlspecialchars($event['event_end'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Likes:</strong> <?= htmlspecialchars($event['likes'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Dislikes:</strong> <?= htmlspecialchars($event['dislikes'], ENT_QUOTES, 'UTF-8') ?></p>
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
    <div class="table-container mt-5 ongoing-section">
        <div class="table-title">Ongoing Events</div>
        <div class="table-wrapper">
            <?php
            // Pagination
            $perPage = 10; // Number of items per page
            $currentPage = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number
            $offset = ($currentPage - 1) * $perPage; // Offset for SQL query

            // Sort parameters
            $sortColumnOngoing = isset($_GET['ongoing_sort']) ? $_GET['ongoing_sort'] : 'date_requested';
            $sortOrderOngoing = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

            // Fetch ongoing events with pagination and sorting
            $ongoingEventsQuery = "SELECT * FROM events WHERE status = 'ongoing' ORDER BY $sortColumnOngoing $sortOrderOngoing LIMIT $perPage OFFSET $offset";
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
                            <th>Title 
                                <a href="?ongoing_sort=title&order=<?= $sortColumnOngoing === 'title' && strtolower($sortOrderOngoing) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumnOngoing === 'title' && strtolower($sortOrderOngoing) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderOngoing === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Description 
                                <a href="?ongoing_sort=description&order=<?= $sortColumnOngoing === 'description' && strtolower($sortOrderOngoing) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumnOngoing === 'description' && strtolower($sortOrderOngoing) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderOngoing === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Facility 
                                <a href="?ongoing_sort=facility&order=<?= $sortColumnOngoing === 'facility' && strtolower($sortOrderOngoing) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumnOngoing === 'facility' && strtolower($sortOrderOngoing) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderOngoing === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Duration 
                                <a href="?ongoing_sort=duration&order=<?= $sortColumnOngoing === 'duration' && strtolower($sortOrderOngoing) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumnOngoing === 'duration' && strtolower($sortOrderOngoing) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderOngoing === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Date Requested 
                                <a href="?ongoing_sort=date_requested&order=<?= $sortColumnOngoing === 'date_requested' && strtolower($sortOrderOngoing) === 'asc' ? 'desc' : 'asc' ?>">
                                    <img src="../SVG/sort-<?= $sortColumnOngoing === 'date_requested' && strtolower($sortOrderOngoing) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderOngoing === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                                </a>
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody>
                        <!-- Loop through ongoing events -->
                        <?php foreach ($ongoingEvents as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['title'] )?></td>
                                <td><?= htmlspecialchars($event['description'])?></td>
                                <td><?= htmlspecialchars($event['facility'])?></td>
                                <td><?= htmlspecialchars($event['duration'])?></td>
                                <td><?= htmlspecialchars($event['date_requested']) ?></td>
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
    <div class="table-container mt-5 approved-section">
        <div class="table-title">Approved Events</div>
        <div class="table-wrapper">
            <?php
            // Pagination
            $perPageApproved = 10; // Number of items per page for approved events
            $currentPageApproved = isset($_GET['page_approved']) ? $_GET['page_approved'] : 1; // Current page number for approved events
            $offsetApproved = ($currentPageApproved - 1) * $perPageApproved; // Offset for SQL query for approved events

            // Sort parameters
            $sortColumnApproved = isset($_GET['approved_sort']) ? $_GET['approved_sort'] : 'date_requested';
            $sortOrderApproved = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

            // Fetch approved events with pagination and sorting
            $approvedEventsQuery = "SELECT * FROM events WHERE status = 'active' ORDER BY $sortColumnApproved $sortOrderApproved LIMIT $perPageApproved OFFSET $offsetApproved";
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
                        <th>Title 
                            <a href="?approved_sort=title&order=<?= $sortColumnApproved === 'title' && strtolower($sortOrderApproved) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnApproved === 'title' && strtolower($sortOrderApproved) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderApproved === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Description 
                            <a href="?approved_sort=description&order=<?= $sortColumnApproved === 'description' && strtolower($sortOrderApproved) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnApproved === 'description' && strtolower($sortOrderApproved) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderApproved === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Facility 
                            <a href="?approved_sort=facility&order=<?= $sortColumnApproved === 'facility' && strtolower($sortOrderApproved) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnApproved === 'facility' && strtolower($sortOrderApproved) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderApproved === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Duration 
                            <a href="?approved_sort=duration&order=<?= $sortColumnApproved === 'duration' && strtolower($sortOrderApproved) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnApproved === 'duration' && strtolower($sortOrderApproved) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderApproved === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Date Requested 
                            <a href="?approved_sort=date_requested&order=<?= $sortColumnApproved === 'date_requested' && strtolower($sortOrderApproved) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnApproved === 'date_requested' && strtolower($sortOrderApproved) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderApproved === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Action</th>
                    </tr>
                    </thead>
                <!-- Table body -->
                <tbody>
                <!-- Loop through approved events -->
                <?php foreach ($approvedEvents as $event): ?>
                    <tr>
                            <td><?= htmlspecialchars($event['title'] )?></td>
                            <td><?= htmlspecialchars($event['description'])?></td>
                            <td><?= htmlspecialchars($event['facility'])?></td>
                            <td><?= htmlspecialchars($event['duration'])?></td>
                            <td><?= htmlspecialchars($event['date_requested']) ?></td>
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
    <div class="table-container mt-5 completed-section">
        <div class="table-title">Completed Events</div>
        <div class="table-wrapper">
            <?php
            // Pagination
            $perPageCompleted = 10; // Number of items per page for completed events
            $currentPageCompleted = isset($_GET['page_completed']) ? $_GET['page_completed'] : 1; // Current page number for completed events
            $offsetCompleted = ($currentPageCompleted - 1) * $perPageCompleted; // Offset for SQL query for completed events

            // Sort parameters
            $sortColumnCompleted = isset($_GET['completed_sort']) ? $_GET['completed_sort'] : 'date_requested';
            $sortOrderCompleted = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

            // Fetch completed events with pagination and sorting
            $completedEventsQuery = "SELECT * FROM events WHERE status = 'completed' ORDER BY $sortColumnCompleted $sortOrderCompleted LIMIT $perPageCompleted OFFSET $offsetCompleted";
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
                        <th>Title 
                            <a href="?completed_sort=title&order=<?= $sortColumnCompleted === 'title' && strtolower($sortOrderCompleted) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnCompleted === 'title' && strtolower($sortOrderCompleted) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderCompleted === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Description 
                            <a href="?completed_sort=description&order=<?= $sortColumnCompleted === 'description' && strtolower($sortOrderCompleted) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnCompleted === 'description' && strtolower($sortOrderCompleted) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderCompleted === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Facility 
                            <a href="?completed_sort=facility&order=<?= $sortColumnCompleted === 'facility' && strtolower($sortOrderCompleted) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnCompleted === 'facility' && strtolower($sortOrderCompleted) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderCompleted === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Duration 
                            <a href="?completed_sort=duration&order=<?= $sortColumnCompleted === 'duration' && strtolower($sortOrderCompleted) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnCompleted === 'duration' && strtolower($sortOrderCompleted) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderCompleted === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Date Requested 
                            <a href="?completed_sort=date_requested&order=<?= $sortColumnCompleted === 'date_requested' && strtolower($sortOrderCompleted) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnCompleted === 'date_requested' && strtolower($sortOrderCompleted) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderCompleted === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Action</th>
                    </tr>
                    </thead>
                <!-- Table body -->
                <tbody>
                <!-- Loop through completed events -->
                <?php foreach ($completedEvents as $event): ?>
                    <tr>
                            <td><?= htmlspecialchars($event['title'] )?></td>
                            <td><?= htmlspecialchars($event['description'])?></td>
                            <td><?= htmlspecialchars($event['facility'])?></td>
                            <td><?= htmlspecialchars($event['duration'])?></td>
                            <td><?= htmlspecialchars($event['date_requested']) ?></td>
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
    <div class="table-container mt-5 mb-5 denied-section">
        <div class="table-title">Denied Events</div>
        <div class="table-wrapper">
            <?php
            // Pagination
            $perPageDenied = 10; // Number of items per page for denied events
            $currentPageDenied = isset($_GET['page_denied']) ? $_GET['page_denied'] : 1; // Current page number for denied events
            $offsetDenied = ($currentPageDenied - 1) * $perPageDenied; // Offset for SQL query for denied events

            // Sort parameters
            $sortColumnDenied = isset($_GET['denied_sort']) ? $_GET['denied_sort'] : 'date_requested';
            $sortOrderDenied = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

            // Fetch denied events with pagination and sorting
            $deniedEventsQuery = "SELECT * FROM events WHERE status = 'denied' ORDER BY $sortColumnDenied $sortOrderDenied LIMIT $perPageDenied OFFSET $offsetDenied";
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
                        <th>Title 
                            <a href="?denied_sort=title&order=<?= $sortColumnDenied === 'title' && strtolower($sortOrderDenied) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnDenied === 'title' && strtolower($sortOrderDenied) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderDenied === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Description 
                            <a href="?denied_sort=description&order=<?= $sortColumnDenied === 'description' && strtolower($sortOrderDenied) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnDenied === 'description' && strtolower($sortOrderDenied) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderDenied === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Facility 
                            <a href="?denied_sort=facility&order=<?= $sortColumnDenied === 'facility' && strtolower($sortOrderDenied) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnDenied === 'facility' && strtolower($sortOrderDenied) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderDenied === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Duration 
                            <a href="?denied_sort=duration&order=<?= $sortColumnDenied === 'duration' && strtolower($sortOrderDenied) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnDenied === 'duration' && strtolower($sortOrderDenied) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderDenied === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Date Requested 
                            <a href="?denied_sort=date_requested&order=<?= $sortColumnDenied === 'date_requested' && strtolower($sortOrderDenied) === 'asc' ? 'desc' : 'asc' ?>">
                                <img src="../SVG/sort-<?= $sortColumnDenied === 'date_requested' && strtolower($sortOrderDenied) === 'asc' ? 'up' : 'down' ?>.svg" alt="<?= $sortOrderDenied === 'asc' ? 'Ascending' : 'Descending' ?>" width="16" height="16">
                            </a>
                        </th>
                        <th>Action</th>
                    </tr>
                    </thead>
                <!-- Table body -->
                <tbody>
                <!-- Loop through denied events -->
                <?php foreach ($deniedEvents as $event): ?>
                    <tr>
                            <td><?= htmlspecialchars($event['title'] )?></td>
                            <td><?= htmlspecialchars($event['description'])?></td>
                            <td><?= htmlspecialchars($event['facility'])?></td>
                            <td><?= htmlspecialchars($event['duration'])?></td>
                            <td><?= htmlspecialchars($event['date_requested']) ?></td>
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
</div>

<!-- Footer -->
<?php require_once '../PARTS/footer.php'; ?>

<!-- JavaScript for real-time filtering -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const sections = document.querySelectorAll('.table-container');
        const allPagination = document.querySelectorAll('.pagination');

        searchInput.addEventListener('input', function () {
            const searchTerm = searchInput.value.trim().toLowerCase();
            let visibleSections = new Set();

            sections.forEach(section => {
                let hasVisibleRows = false;
                const tableRows = section.querySelectorAll('.table-bordered tbody tr');
                tableRows.forEach(row => {
                    const title = row.querySelector('td:nth-child(1)').textContent.trim().toLowerCase();
                    const description = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
                    const facility = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
                    const duration = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
                    const dateRequested = row.querySelector('td:nth-child(5)').textContent.trim().toLowerCase();

                    if (title.includes(searchTerm) || description.includes(searchTerm) || facility.includes(searchTerm) || duration.includes(searchTerm) || dateRequested.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update section visibility
                if (hasVisibleRows) {
                    section.style.display = '';
                    visibleSections.add(section);
                } else {
                    section.style.display = 'none';
                }
            });

            // Toggle pagination visibility for all sections
            if (searchTerm !== '') {
                allPagination.forEach(pagination => {
                    pagination.style.display = 'none';
                });
            } else {
                allPagination.forEach(pagination => {
                    pagination.style.display = '';
                });
            }

            // Hide sections with no visible rows
            sections.forEach(section => {
                if (!visibleSections.has(section)) {
                    section.style.display = 'none';
                }
            });
        });
    });
</script>

<!-- JS.PHP -->
<?php require_once '../PARTS/js.php'; ?>
</body>
</html>
