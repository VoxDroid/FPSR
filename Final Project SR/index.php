<?php
require_once 'PARTS/background_worker.php';
require_once 'PARTS/config.php';

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

    <!-- CSS.PHP -->
    <?php require_once 'PARTS/CSS.php'; ?>
</head>
<body>
    
<!-- Header -->
<?php
    require_once 'PARTS/header.php';
?>

<!-- Event Cards and Carousel -->
<style>
.event-card {
    border: none;
    transition: box-shadow 0.3s;
    border-radius: 10px;
}

.event-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-body {
    padding: 20px;
}

.profile-picture {
    border: 2px solid #fff;
}

.event-description {
    color: #555;
    font-size: 0.95rem;
}

.like-dislike img {
    margin-right: 5px;
}
</style>

<?php require 'CSS/pagination_cards.css'; ?>

<!-- Event Header Styling -->
<style>
    hr {
    opacity: 1;
    }
    .custom-heading {
        align-items: center;
        font-size: 2rem;
        text-decoration: none; /* Remove underline */
        display: inline-flex; /* Make the link behave like a flex container */
        position: relative; /* Needed for absolute positioning */
        padding: 10px 15px; /* Adjust padding for better visual */
        transition: color 0.3s ease; /* Smooth transition for text color */
    }
    .custom-heading .bi {
        margin-left: 10px;
        position: relative; /* Needed for absolute positioning */
        transition: transform 0.3s ease; /* Smooth transition for transform */
    }
    .custom-heading.white-background {
        color: #161c27; /* Custom text color */
    }
    .custom-heading.white-background:hover .bi {
        color: #34495e; /* Change icon color on hover */
        transform: translateX(10px); /* Move the icon 5px to the right on hover */
    }
    .custom-heading.white-background:hover {
        color: #34495e; /* Change text color on hover */
    }

    .custom-heading.blue-background {
        color: #ffffff; /* White text color */
    }
    .custom-heading.blue-background:hover .bi {
        color: #c0c0c0; /* Change icon color on hover */
        transform: translateX(10px); /* Move the icon 10px to the right on hover */
    }
    .custom-heading.blue-background:hover {
        color: #c0c0c0; /* Change text color on hover */
    }
    .custom-heading:hover {
        scale: 1.02;
        transition: scale 0.3s;
    }

    /* Carousel Controls */
.carousel-control-prev,
.carousel-control-next {
    color: #fff; /* Text color */
    background-color: rgba(23, 34, 47, 0.8); /* Background color with opacity */
    width: 50px; /* Control width */
    height: 50px; /* Control height */
    border: 2px solid rgba(200, 200, 255, 0.5); /* Lightish dark navy border */
    border-radius: 50%; /* Rounded shape */
    font-size: 24px; /* Font size */
    line-height: 50px; /* Vertical alignment of text */
    text-align: center; /* Center align text */
    position: absolute; /* Positioning */
    top: 50%; /* Center vertically */
    transform: translateY(-50%); /* Adjust vertical position */
    cursor: pointer; /* Cursor style */
    transition: background-color 0.3s ease, transform 0.3s ease, border-color 0.3s ease; /* Smooth transition */
    z-index: 10; /* Ensure controls are above the carousel */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Enhanced shadow for depth */
}

.carousel-control-prev {
    left: -110px; /* Position left control */
}

.carousel-control-next {
    right: -110px; /* Position right control */
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background-color: rgba(30, 46, 62, 0.8); /* Darker background on hover */
    transform: translateY(-50%) scale(1.1); /* Slightly enlarge on hover */
    border-color: rgba(200, 200, 255, 0.7); /* Darken border on hover */
}

.carousel-indicators{
    bottom: -50px;
}
/* Carousel Indicators */
.carousel-indicators li {
    position: relative;
    bottom: -20px; /* Adjust positioning */
    background-color: rgba(170, 178, 189, 0.7); /* Indicator background color with opacity */
    border: 2px solid rgba(200, 200, 255, 0.5); /* Lightish dark navy border */
    border-radius: 50%; /* Rounded shape */
    width: 12px; /* Indicator width */
    height: 12px; /* Indicator height */
    margin: 0 5px; /* Spacing between indicators */
    cursor: pointer; /* Cursor style */
    list-style: none; /* Remove list style */
    transition: background-color 0.3s ease, border-color 0.3s ease; /* Smooth transition */
}

.carousel-indicators li.active {
    background-color: rgba(39, 52, 71, 0.8); /* Active indicator color with opacity */
    border-color: rgba(200, 200, 255, 0.7); /* Darken border on active */
}

/* Carousel Control Icons */
.carousel-control-next-icon,
.carousel-control-prev-icon {
    width: 20px; /* Icon width */
    height: 20px; /* Icon height */
}

/* Additional Styling for Control Icons */
.carousel-control-prev-icon::before,
.carousel-control-next-icon::before {
    font-size: 20px; /* Icon font size */
    color: #fff; /* Icon color */
}



</style>

<!-- End Header -->

<!-- Main Content -->
<main class="py-5 flex-grow-1" style="background-color: #1c2331">
    <div class="container">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']); // Clear message after displaying
        }

        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']); // Clear message after displaying
        }
        ?>
        <h2>
            <a href="EMS/events_ongoing.php" class="custom-heading blue-background">
                Ongoing Events
                <i class="bi bi-chevron-right"></i>
            </a>
        </h2>
        <hr style="border: none; height: 4px; background-color: #FFFFFF;">
        <div id="ongoingEventsCarousel" class="carousel slide mb-5" data-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch ongoing events with user information
                $queryOngoingEvents = "SELECT e.*, u.username, u.profile_picture 
                                    FROM events e
                                    JOIN users u ON e.user_id = u.id
                                    WHERE e.status = 'ongoing' 
                                    ORDER BY e.date_requested ASC 
                                    LIMIT 20";
                $stmtOngoingEvents = $pdo->query($queryOngoingEvents);
                $ongoingEventCount = $stmtOngoingEvents->rowCount();

                if ($ongoingEventCount > 0) {
                    $first = true;
                    $slideIndex = 0;
                    while ($event = $stmtOngoingEvents->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                        echo '<div class="card shadow-sm event-card">';
                        echo '<div class="card-body">';
                        // User profile picture and name
                        echo '<div class="d-flex align-items-center mb-3">';
                        // Adjust profile picture path if it starts with '../'
                        $profilePicture = $event['profile_picture'];
                        if (strpos($profilePicture, '../') === 0) {
                            $profilePicture = substr($profilePicture, 3); // Remove '../'
                        }
                        echo '<img src="' . $profilePicture . '" class="rounded-circle me-3 profile-picture" width="50" height="50" alt="Profile Picture">';
                        echo '<div>';
                        echo '<h5 class="card-title mb-0">' . htmlspecialchars($event['title']) . '</h5>';
                        echo '<p class="card-text text-muted mb-1">Organized by: ' . htmlspecialchars($event['username']) . '</p>';
                        echo '<p class="card-text text-muted mb-0">Date: ' . date('M d, Y', strtotime($event['date_requested'])) . '</p>';
                        echo '</div>';
                        echo '</div>';
                        // Event details
                        echo '<p class="card-text event-description">' . nl2br(htmlspecialchars($event['description'])) . '</p>';
                        // Additional event information
                        echo '<div class="row mb-3">';
                        echo '<div class="col-md-6">';
                        echo '<p class="card-text"><strong>Duration:</strong> ' . $event['duration'] . ' hours</p>';
                        echo '<p class="card-text"><strong>Location:</strong> ' . htmlspecialchars($event['facility']) . '</p>';
                        echo '</div>';
                        echo '<div class="col-md-6">';
                        echo '<p class="card-text"><strong>Status:</strong> ' . ucfirst($event['status']) . '</p>';
                        echo '<p class="card-text"><strong>Remarks:</strong> ' . ($event['remarks'] ? htmlspecialchars($event['remarks']) : 'None') . '</p>';
                        echo '</div>';
                        echo '</div>';
                        // Likes and dislikes icons and numbers
                        echo '<div class="d-flex align-items-center mb-3">';
                        echo '<div class="like-dislike me-4">';
                        echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                        echo '<span class="like-count">' . $event['likes'] . '</span>';
                        echo '</div>';
                        echo '<div class="like-dislike">';
                        echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger me-1">';
                        echo '<span class="dislike-count">' . $event['dislikes'] . '</span>';
                        echo '</div>';
                        echo '</div>';
                        // View button
                        echo '<a href="EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary btn-sm custom-button-ind">View Details</a>';
                        echo '</div>'; // .card-body
                        echo '</div>'; // .card
                        echo '</div>'; // .carousel-item
                        $first = false;
                    }
                } else {
                    echo '<p class="text-white">No ongoing events found.</p>';
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
                <a class="carousel-control-prev custom-carousel-control" href="#ongoingEventsCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon custom-carousel-control" aria-hidden="true"></span>
                </a>
                <a class="carousel-control-next custom-carousel-control" href="#ongoingEventsCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon custom-carousel-control" aria-hidden="true"></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>
<!-- End Main Content -->
<!-- Approved events -->
<div class="py-5">
    <div class="container">
        <h2>
            <a href="EMS/events_approved.php" class="custom-heading white-background">
                Approved Events
                <i class="bi bi-chevron-right"></i>
            </a>
        </h2>
        <hr style="border: none; height: 4px; background-color: #1c2331;">
        <div id="approvedEventsCarousel" class="carousel slide mb-5" data-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch approved events with user information
                $queryApprovedEvents = "SELECT e.*, u.username, u.profile_picture 
                                    FROM events e
                                    JOIN users u ON e.user_id = u.id
                                    WHERE e.status = 'active' 
                                    ORDER BY e.date_requested ASC 
                                    LIMIT 20";
                $stmtApprovedEvents = $pdo->query($queryApprovedEvents);
                $approvedEventCount = $stmtApprovedEvents->rowCount();

                if ($approvedEventCount > 0) {
                    $first = true;
                    $slideIndex = 0;
                    while ($event = $stmtApprovedEvents->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                        echo '<div class="card shadow-sm event-card">';
                        echo '<div class="card-body">';
                        // User profile picture and name
                        echo '<div class="d-flex align-items-center mb-3">';
                        // Adjust profile picture path if it starts with '../'
                        $profilePicture = $event['profile_picture'];
                        if (strpos($profilePicture, '../') === 0) {
                            $profilePicture = substr($profilePicture, 3); // Remove '../'
                        }
                        echo '<img src="' . $profilePicture . '" class="rounded-circle me-3 profile-picture" width="50" height="50" alt="Profile Picture">';
                        echo '<div>';
                        echo '<h5 class="card-title mb-0">' . htmlspecialchars($event['title']) . '</h5>';
                        echo '<p class="card-text text-muted mb-1">Organized by: ' . htmlspecialchars($event['username']) . '</p>';
                        echo '<p class="card-text text-muted mb-0">Date: ' . date('M d, Y', strtotime($event['date_requested'])) . '</p>';
                        echo '</div>';
                        echo '</div>';
                        // Event details
                        echo '<p class="card-text event-description">' . nl2br(htmlspecialchars($event['description'])) . '</p>';
                        // Additional event information
                        echo '<div class="row mb-3">';
                        echo '<div class="col-md-6">';
                        echo '<p class="card-text"><strong>Duration:</strong> ' . $event['duration'] . ' hours</p>';
                        echo '<p class="card-text"><strong>Location:</strong> ' . htmlspecialchars($event['facility']) . '</p>';
                        echo '</div>';
                        echo '<div class="col-md-6">';
                        echo '<p class="card-text"><strong>Status:</strong> ' . ucfirst($event['status']) . '</p>';
                        echo '<p class="card-text"><strong>Remarks:</strong> ' . ($event['remarks'] ? htmlspecialchars($event['remarks']) : 'None') . '</p>';
                        echo '</div>';
                        echo '</div>';
                        // Likes and dislikes icons and numbers
                        echo '<div class="d-flex align-items-center mb-3">';
                        echo '<div class="like-dislike me-4">';
                        echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                        echo '<span class="like-count">' . $event['likes'] . '</span>';
                        echo '</div>';
                        echo '<div class="like-dislike">';
                        echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger me-1">';
                        echo '<span class="dislike-count">' . $event['dislikes'] . '</span>';
                        echo '</div>';
                        echo '</div>';
                        // View button
                        echo '<a href="EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary btn-sm custom-button-ind">View Details</a>';
                        echo '</div>'; // .card-body
                        echo '</div>'; // .card
                        echo '</div>'; // .carousel-item
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
    </div>
</div>
<!-- End of Approved events -->

<!-- Pending Events -->
<div class="py-5" style="background-color: #1c2331">
    <div class="container">
        <h2>
            <a href="EMS/events_pending.php" class="custom-heading blue-background">
                Pending Events
                <i class="bi bi-chevron-right"></i>
            </a>
        </h2>
        <hr style="border: none; height: 4px; background-color: #FFFFFF;">
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
            $queryPendingEvents = "SELECT e.*, u.username, u.profile_picture 
                                FROM events e
                                JOIN users u ON e.user_id = u.id
                                WHERE e.status = 'pending' 
                                ORDER BY e.date_requested ASC 
                                LIMIT $pendingOffset, $pendingItemsPerPage";
            $stmtPendingEvents = $pdo->query($queryPendingEvents);

            if ($stmtPendingEvents->rowCount() > 0) {
                while ($event = $stmtPendingEvents->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="col-md-6 mb-4">';
                    echo '<div class="card shadow-sm event-card">';
                    echo '<div class="card-body">';
                    // User profile picture and name
                    echo '<div class="d-flex align-items-center mb-3">';
                    // Adjust profile picture path if it starts with '../'
                    $profilePicture = $event['profile_picture'];
                    if (strpos($profilePicture, '../') === 0) {
                        $profilePicture = substr($profilePicture, 3); // Remove '../'
                    }
                    echo '<img src="' . $profilePicture . '" class="rounded-circle me-3 profile-picture" width="50" height="50" alt="Profile Picture">';
                    echo '<div>';
                    echo '<h5 class="card-title mb-0">' . htmlspecialchars($event['title']) . '</h5>';
                    echo '<p class="card-text text-muted mb-1">Organized by: ' . htmlspecialchars($event['username']) . '</p>';
                    echo '<p class="card-text text-muted mb-0">Date: ' . date('M d, Y', strtotime($event['date_requested'])) . '</p>';
                    echo '</div>';
                    echo '</div>';
                    // Event details
                    echo '<p class="card-text event-description">' . nl2br(htmlspecialchars($event['description'])) . '</p>';
                    // Additional event information
                    echo '<div class="row mb-3">';
                    echo '<div class="col-md-6">';
                    echo '<p class="card-text"><strong>Duration:</strong> ' . htmlspecialchars($event['duration']) . ' hours</p>';
                    echo '<p class="card-text"><strong>Location:</strong> ' . htmlspecialchars($event['facility']) . '</p>';
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo '<p class="card-text"><strong>Status:</strong> ' . ucfirst($event['status']) . '</p>';
                    echo '<p class="card-text"><strong>Remarks:</strong> ' . ($event['remarks'] ? htmlspecialchars($event['remarks']) : 'None') . '</p>';
                    echo '</div>';
                    echo '</div>';
                    // Likes and dislikes icons and numbers
                    echo '<div class="d-flex align-items-center mb-3">';
                    echo '<div class="like-dislike me-4">';
                    echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                    echo '<span class="like-count">' . $event['likes'] . '</span>';
                    echo '</div>';
                    echo '<div class="like-dislike">';
                    echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger me-1">';
                    echo '<span class="dislike-count">' . $event['dislikes'] . '</span>';
                    echo '</div>';
                    echo '</div>';
                    // View button
                    echo '<a href="EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary btn-sm custom-button-ind">View Details</a>';
                    echo '</div>'; // .card-body
                    echo '</div>'; // .card
                    echo '</div>'; // .col-md-6
                }
            } else {
                echo '<div class="col-md-12">';
                echo '<p class="text-white">No pending events found.</p>';
                echo '</div>';
            }

            // Display pagination controls for pending section
            if ($pendingEventCount > $pendingItemsPerPage) {
                echo '<div class="col-md-12">';
                echo '<nav aria-label="Page navigation example">';
                echo '<ul class="pagination justify-content-center">';
                echo '<li class="page-item ' . ($pendingCurrentPage == 1 ? 'disabled' : '') . '">';
                echo '<a class="page-link custom-page-link" href="?pending_page=' . max(1, $pendingCurrentPage - 1) . '" aria-label="Previous">';
                echo '<span aria-hidden="true">&laquo;</span>';
                echo '<span class="sr-only">Previous</span>';
                echo '</a>';
                echo '</li>';
                for ($i = 1; $i <= $pendingTotalPages; $i++) {
                    echo '<li class="page-item ' . ($pendingCurrentPage == $i ? 'active' : '') . '"><a class="page-link custom-page-link" href="?pending_page=' . $i . '">' . $i . '</a></li>';
                }
                echo '<li class="page-item ' . ($pendingCurrentPage == $pendingTotalPages ? 'disabled' : '') . '">';
                echo '<a class="page-link custom-page-link" href="?pending_page=' . min($pendingTotalPages, $pendingCurrentPage + 1) . '" aria-label="Next">';
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
</div>
<!-- End of Pending Events -->

<!-- Archive Section -->
<div class="py-5">
    <div class="container">
    <h2>
            <a href="EMS/events_archived.php" class="custom-heading white-background">
                Archived Events
                <i class="bi bi-chevron-right"></i>
            </a>
        </h2>
    <hr style="border: none; height: 4px; background-color: #1c2331;">
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
            $queryArchiveEvents = "SELECT e.*, u.username, u.profile_picture 
                                FROM events e
                                JOIN users u ON e.user_id = u.id
                                WHERE e.status IN ('completed', 'denied') 
                                ORDER BY e.date_requested ASC 
                                LIMIT $archiveOffset, $archiveItemsPerPage";
            $stmtArchiveEvents = $pdo->query($queryArchiveEvents);

            if ($stmtArchiveEvents->rowCount() > 0) {
                while ($event = $stmtArchiveEvents->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="col-md-6 mb-4">';
                    echo '<div class="card shadow-sm event-card">';
                    echo '<div class="card-body">';
                    // User profile picture and name
                    echo '<div class="d-flex align-items-center mb-3">';
                    // Adjust profile picture path if it starts with '../'
                    $profilePicture = $event['profile_picture'];
                    if (strpos($profilePicture, '../') === 0) {
                        $profilePicture = substr($profilePicture, 3); // Remove '../'
                    }
                    echo '<img src="' . $profilePicture . '" class="rounded-circle me-3 profile-picture" width="50" height="50" alt="Profile Picture">';
                    echo '<div>';
                    echo '<h5 class="card-title mb-0">' . htmlspecialchars($event['title']) . '</h5>';
                    echo '<p class="card-text text-muted mb-1">Organized by: ' . htmlspecialchars($event['username']) . '</p>';
                    echo '<p class="card-text text-muted mb-0">Date: ' . date('M d, Y', strtotime($event['date_requested'])) . '</p>';
                    echo '</div>';
                    echo '</div>';
                    // Event details
                    echo '<p class="card-text event-description">' . nl2br(htmlspecialchars($event['description'])) . '</p>';
                    // Additional event information
                    echo '<div class="row mb-3">';
                    echo '<div class="col-md-6">';
                    echo '<p class="card-text"><strong>Duration:</strong> ' . $event['duration'] . ' hours</p>';
                    echo '<p class="card-text"><strong>Location:</strong> ' . htmlspecialchars($event['facility']) . '</p>';
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo '<p class="card-text"><strong>Status:</strong> ' . ucfirst($event['status']) . '</p>';
                    echo '<p class="card-text"><strong>Remarks:</strong> ' . ($event['remarks'] ? htmlspecialchars($event['remarks']) : 'None') . '</p>';
                    echo '</div>';
                    echo '</div>';
                    // Likes and dislikes icons and numbers
                    echo '<div class="d-flex align-items-center mb-3">';
                    echo '<div class="like-dislike me-4">';
                    echo '<img src="SVG/hand-thumbs-up-fill.svg" alt="Likes" width="16" height="16" class="text-success me-1">';
                    echo '<span class="like-count">' . $event['likes'] . '</span>';
                    echo '</div>';
                    echo '<div class="like-dislike">';
                    echo '<img src="SVG/hand-thumbs-down-fill.svg" alt="Dislikes" width="16" height="16" class="text-danger me-1">';
                    echo '<span class="dislike-count">' . $event['dislikes'] . '</span>';
                    echo '</div>';
                    echo '</div>';
                    // View button
                    echo '<a href="EMS/event_details.php?event_id=' . $event['id'] . '" class="btn btn-primary btn-sm custom-button-ind">View Details</a>';
                    echo '</div>'; // .card-body
                    echo '</div>'; // .card
                    echo '</div>'; // .col-md-6
                }
            } else {
                echo '<div class="col-md-12">';
                echo '<p>No archived events found.</p>';
                echo '</div>';
            }

            // Display pagination controls for archive section
            if ($archiveEventCount > $archiveItemsPerPage) {
                echo '<div class="col-md-12">';
                echo '<nav aria-label="Page navigation example">';
                echo '<ul class="pagination justify-content-center">';
                echo '<li class="page-item ' . ($archiveCurrentPage == 1 ? 'disabled' : '') . '">';
                echo '<a class="page-link custom-page-link" href="?archive_page=' . max(1, $archiveCurrentPage - 1) . '" aria-label="Previous">';
                echo '<span aria-hidden="true">&laquo;</span>';
                echo '<span class="sr-only">Previous</span>';
                echo '</a>';
                echo '</li>';
                for ($i = 1; $i <= $archiveTotalPages; $i++) {
                    echo '<li class="page-item ' . ($archiveCurrentPage == $i ? 'active' : '') . '"><a class="page-link custom-page-link" href="?archive_page=' . $i . '">' . $i . '</a></li>';
                }
                echo '<li class="page-item ' . ($archiveCurrentPage == $archiveTotalPages ? 'disabled' : '') . '">';
                echo '<a class="page-link custom-page-link" href="?archive_page=' . min($archiveTotalPages, $archiveCurrentPage + 1) . '" aria-label="Next">';
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
</div>
<!-- End of Archive Section -->

<!-- Footer -->
<?php require_once 'PARTS/footer.php'; ?>

<!-- JS.PHP -->
<?php
require_once 'PARTS/JS.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customHeadings = document.querySelectorAll('.custom-heading');

        customHeadings.forEach(function(customHeading) {
            const chevronIcon = customHeading.querySelector('.bi');

            customHeading.addEventListener('mouseenter', function () {
                chevronIcon.style.animation = 'moveLeftRight 0.5s ease infinite alternate';
            });

            customHeading.addEventListener('mouseleave', function () {
                chevronIcon.style.animation = 'none';
            });
        });
    });
</script>
</body>
</html>
