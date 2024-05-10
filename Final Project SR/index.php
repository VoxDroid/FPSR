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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>

    <!-- Bootstrap CSS -->
    <link href="CSS/bootstrap.min.css" rel="stylesheet">

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
        <!-- Approved Events -->
        <h2>Approved Events</h2>
        <div id="approvedEventsCarousel" class="carousel slide mb-5" data-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch approved events from the database
                $queryApprovedEvents = "SELECT * FROM events WHERE status = 'accepted' ORDER BY date_requested ASC LIMIT 5";
                $stmtApprovedEvents = $pdo->query($queryApprovedEvents);
                $first = true;
                $slideIndex = 0;
                while ($event = $stmtApprovedEvents->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                    echo '<div class="card">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $event['title'] . '</h5>';
                    echo '<p class="card-text">' . $event['description'] . '</p>';
                    echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
                    echo '<a href="#" class="btn btn-primary">View</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    $first = false;
                }
                ?>
            </div>
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
        </div>
        <!-- Pending Events -->
        <h2>Pending Events</h2>
        <div class="row">
            <?php
            // Fetch pending events from the database
            $queryPendingEvents = "SELECT * FROM events WHERE status = 'pending' ORDER BY date_requested ASC";
            $stmtPendingEvents = $pdo->query($queryPendingEvents);
            while ($event = $stmtPendingEvents->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="col-md-6 mb-4">';
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . $event['title'] . '</h5>';
                echo '<p class="card-text">' . $event['description'] . '</p>';
                echo '<p class="card-text">Date: ' . $event['date_requested'] . '</p>';
                echo '<a href="#" class="btn btn-primary">View</a>';
                echo '</div>';
                echo '</div>';
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
