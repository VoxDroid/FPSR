<?php
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

    // Start session
    session_start();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

// Check if user is an admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Logout function
function logout() {
    session_destroy();
    // Refresh the page after logout
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

// Check if logout button is clicked
if (isset($_POST['logout'])) {
    logout();
}

// Function to calculate total number of pages
function getTotalPages($totalItems, $itemsPerPage) {
    return ceil($totalItems / $itemsPerPage);
}

// Function to fetch events with pagination
function fetchEventsWithPagination($pdo, $query, $currentPage, $itemsPerPage) {
    // Calculate offset based on current page
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Add LIMIT and OFFSET to query
    $query .= " LIMIT $offset, $itemsPerPage";

    // Execute query
    $stmt = $pdo->query($query);

    // Fetch results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
}

// Pagination variables
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page (default: 1)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>

    <!-- Bootstrap CSS -->
    <link href="CSS/bootstrap.min.css" rel="stylesheet">
    <link href="CSS/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="CSS/custom_style.css" rel="stylesheet">
</head>
<body>
<!-- Header -->
<header class="bg-dark py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <?php if ($loggedIn): ?>
                    <span class="text-light">Welcome, <?php echo $username; ?></span>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($loggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $username; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <?php if ($isAdmin): ?>
                                <!-- UI for Admin -->
                                <li><a class="dropdown-item" href="ADMIN/view_requests.php">View Requests</a></li>
                                <li><a class="dropdown-item" href="ADMIN/admin_page_settings.php">Admin Page Settings</a></li>
                            <?php else: ?>
                                <!-- UI for Regular User -->
                                <li><a class="dropdown-item" href="#">Request Event</a></li>
                                <li><a class="dropdown-item" href="#">View My Requests</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="EMS/profile.php">Profile</a></li>
                            <form method="post">
                                <li><button type="submit" name="logout" class="dropdown-item btn btn-link">Logout</button></li>
                            </form>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="EMS/login.php" class="btn btn-light">Log In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<!-- End Header -->

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <!-- Ongoing Events -->
<!-- Ongoing Events -->
<h2>Ongoing Events</h2>
<div id="ongoingEventsCarousel" class="carousel slide mb-5" data-ride="carousel">
    <div class="carousel-inner">
        <?php
        // Fetch ongoing events from the database
        $queryOngoingEvents = "SELECT * FROM events WHERE status = 'ongoing' ORDER BY date_requested ASC LIMIT 20";
        $stmtOngoingEvents = $pdo->query($queryOngoingEvents);
        $ongoingEventCount = $stmtOngoingEvents->rowCount();

        if ($ongoingEventCount > 0) {
            $first = true;
            $slideIndex = 0;
            while ($event = $stmtOngoingEvents->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . $event['title'] . '</h5>';
                echo '<p class="card-text">' . $event['description'] . '</p>';
                echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
                // Likes and dislikes icons and numbers
                echo '<div class="d-flex align-items-center">';
                echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                echo '<span>' . $event['likes'] . '</span>';
                echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger ms-3 me-1">';
                echo '<span>' . $event['dislikes'] . '</span>';
                echo '</div>';
                echo '<a href="#" class="btn btn-primary mt-3">View</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                $first = false;
            }
        } else {
            echo '<p>No ongoing events found.</p>';
        }
        ?>
    </div>
    <?php if ($ongoingEventCount > 1): ?>
        <ol class="carousel-indicators">
            <?php
            // Resetting the statement cursor
            $stmtOngoingEvents->execute();
            $first = true;
            while ($event = $stmtOngoingEvents->fetch(PDO::FETCH_ASSOC)) {
                echo '<li data-target="#ongoingEventsCarousel" data-slide-to="' . $slideIndex . '" class="' . ($first ? 'active' : '') . '"></li>';
                $first = false;
                $slideIndex++;
            }
            ?>
        </ol>
        <a class="carousel-control-prev" href="#ongoingEventsCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next" href="#ongoingEventsCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
    <?php endif; ?>
</div>


        <!-- Approved Events -->
        <h2>Approved Events</h2>
        <div id="approvedEventsCarousel" class="carousel slide mb-5" data-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch approved events from the database
                $queryApprovedEvents = "SELECT * FROM events WHERE status = 'active' ORDER BY date_requested ASC LIMIT 20";
                $stmtApprovedEvents = $pdo->query($queryApprovedEvents);
                $approvedEventCount = $stmtApprovedEvents->rowCount();

                if ($approvedEventCount > 0) {
                    $first = true;
                    $slideIndex = 0;
                    while ($event = $stmtApprovedEvents->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                        echo '<div class="card">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . $event['title'] . '</h5>';
                        echo '<p class="card-text">' . $event['description'] . '</p>';
                        echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
                        // Likes and dislikes icons and numbers
                        echo '<div class="d-flex align-items-center">';
                        echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                        echo '<span>' . $event['likes'] . '</span>';
                        echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger ms-3 me-1">';
                        echo '<span>' . $event['dislikes'] . '</span>';
                        echo '</div>';
                        echo '<a href="#" class="btn btn-primary mt-3">View</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        $first = false;
                    }
                } else {
                    echo '<p>No approved events found.</p>';
                }
                ?>
            </div>
            <?php if ($approvedEventCount > 1): ?>
                <ol class="carousel-indicators">
                    <?php
                    // Resetting the statement cursor
                    $stmtApprovedEvents->execute();
                    $first = true;
                    while ($event = $stmtApprovedEvents->fetch(PDO::FETCH_ASSOC)) {
                        echo '<li data-target="#approvedEventsCarousel" data-slide-to="' . $slideIndex . '" class="' . ($first ? 'active' : '') . '"></li>';
                        $first = false;
                        $slideIndex++;
                    }
                    ?>
                </ol>
                <a class="carousel-control-prev" href="#approvedEventsCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </a>
                <a class="carousel-control-next" href="#approvedEventsCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Pending Events -->
        <h2>Pending Events</h2>
        <div class="row">
            <?php
            // Fetch pending events count
            $queryPendingEventsCount = "SELECT COUNT(*) AS count FROM events WHERE status = 'pending'";
            $stmtPendingEventsCount = $pdo->query($queryPendingEventsCount);
            $pendingEventCount = $stmtPendingEventsCount->fetch(PDO::FETCH_ASSOC)['count'];

            // Define items per page for pending section
            $pendingItemsPerPage = 10;

            // Calculate total pages for pending section
            $pendingTotalPages = ceil($pendingEventCount / $pendingItemsPerPage);

            // Fetch pending events with pagination
            $pendingCurrentPage = isset($_GET['pending_page']) ? max(1, intval($_GET['pending_page'])) : 1;
            $pendingOffset = ($pendingCurrentPage - 1) * $pendingItemsPerPage;
            $queryPendingEvents = "SELECT * FROM events WHERE status = 'pending' ORDER BY date_requested ASC LIMIT $pendingOffset, $pendingItemsPerPage";
            $stmtPendingEvents = $pdo->query($queryPendingEvents);

            if ($stmtPendingEvents->rowCount() > 0) {
                while ($event = $stmtPendingEvents->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="col-md-6 mb-4">';
                    echo '<div class="card">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $event['title'] . '</h5>';
                    echo '<p class="card-text">' . $event['description'] . '</p>';
                    echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
                    echo '<div class="d-flex align-items-center">';
                    echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                    echo '<span>' . $event['likes'] . '</span>';
                    echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger ms-3 me-1">';
                    echo '<span>' . $event['dislikes'] . '</span>';
                    echo '</div>';
                    echo '<a href="#" class="btn btn-primary mt-3">View</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No pending events found.</p>';
            }

            // Display pagination controls for pending section
            if ($pendingEventCount > $pendingItemsPerPage) {
                echo '<div class="col-md-12">';
                echo '<nav aria-label="Page navigation example">';
                echo '<ul class="pagination justify-content-center">';
                echo '<li class="page-item ' . ($pendingCurrentPage == 1 ? 'disabled' : '') . '">';
                echo '<a class="page-link" href="?pending_page=' . max(1, $pendingCurrentPage - 1) . '" aria-label="Previous">';
                echo '<span aria-hidden="true">&laquo;</span>';
                echo '<span class="sr-only">Previous</span>';
                echo '</a>';
                echo '</li>';
                for ($i = 1; $i <= $pendingTotalPages; $i++) {
                    echo '<li class="page-item ' . ($pendingCurrentPage == $i ? 'active' : '') . '"><a class="page-link" href="?pending_page=' . $i . '">' . $i . '</a></li>';
                }
                echo '<li class="page-item ' . ($pendingCurrentPage == $pendingTotalPages ? 'disabled' : '') . '">';
                echo '<a class="page-link" href="?pending_page=' . min($pendingTotalPages, $pendingCurrentPage + 1) . '" aria-label="Next">';
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

        <!-- Archive Section -->
        <h2>Archive</h2>
        <div class="row">
            <?php
            // Fetch archive events count
            $queryArchiveEventsCount = "SELECT COUNT(*) AS count FROM events WHERE status IN ('completed', 'denied')";
            $stmtArchiveEventsCount = $pdo->query($queryArchiveEventsCount);
            $archiveEventCount = $stmtArchiveEventsCount->fetch(PDO::FETCH_ASSOC)['count'];

            // Define items per page
            $archiveItemsPerPage = 10;

            // Calculate total pages for archive section
            $archiveTotalPages = ceil($archiveEventCount / $archiveItemsPerPage);

            // Fetch archive events with pagination
            $archiveCurrentPage = isset($_GET['archive_page']) ? max(1, intval($_GET['archive_page'])) : 1;
            $archiveOffset = ($archiveCurrentPage - 1) * $archiveItemsPerPage;
            $queryArchiveEvents = "SELECT * FROM events WHERE status IN ('completed', 'denied') ORDER BY date_requested ASC LIMIT $archiveOffset, $archiveItemsPerPage";
            $stmtArchiveEvents = $pdo->query($queryArchiveEvents);

            if ($stmtArchiveEvents->rowCount() > 0) {
                while ($event = $stmtArchiveEvents->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="col-md-6 mb-4">';
                    echo '<div class="card">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $event['title'] . '</h5>';
                    echo '<p class="card-text">' . $event['description'] . '</p>';
                    echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
                    echo '<div class="d-flex align-items-center">';
                    echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                    echo '<span>' . $event['likes'] . '</span>';
                    echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger ms-3 me-1">';
                    echo '<span>' . $event['dislikes'] . '</span>';
                    echo '</div>';
                    echo '<a href="#" class="btn btn-primary mt-3">View</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No archived events found.</p>';
            }

            // Display pagination controls for archive section
            if ($archiveEventCount > $archiveItemsPerPage) {
                echo '<div class="col-md-12">';
                echo '<nav aria-label="Page navigation example">';
                echo '<ul class="pagination justify-content-center">';
                echo '<li class="page-item ' . ($archiveCurrentPage == 1 ? 'disabled' : '') . '">';
                echo '<a class="page-link" href="?archive_page=' . max(1, $archiveCurrentPage - 1) . '" aria-label="Previous">';
                echo '<span aria-hidden="true">&laquo;</span>';
                echo '<span class="sr-only">Previous</span>';
                echo '</a>';
                echo '</li>';
                for ($i = 1; $i <= $archiveTotalPages; $i++) {
                    echo '<li class="page-item ' . ($archiveCurrentPage == $i ? 'active' : '') . '"><a class="page-link" href="?archive_page=' . $i . '">' . $i . '</a></li>';
                }
                echo '<li class="page-item ' . ($archiveCurrentPage == $archiveTotalPages ? 'disabled' : '') . '">';
                echo '<a class="page-link" href="?archive_page=' . min($archiveTotalPages, $archiveCurrentPage + 1) . '" aria-label="Next">';
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

<!-- Bootstrap JS -->
<script src="JS/jquery.slim.min.js"></script>
<script src="JS/popper.min.js"></script>
<script src="JS/bootstrap.min.js"></script>

<!-- Custom JS -->
<script src="JS/custom_script.js"></script>

</body>
</html>
