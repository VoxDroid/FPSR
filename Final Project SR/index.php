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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-control-prev,
        .carousel-control-next {
            color: black !important;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .carousel-control-prev {
            left: -55px;
        }

        .carousel-control-next {
            right: -55px;
        }
    </style>


</head>
<body>

<!-- Header -->
<header class="bg-dark py-3">
    <div class="container">
        <div class="d-flex justify-content-end">
            <a href="login.php" class="btn btn-light">Log In</a>
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
            <ol class="carousel-indicators">
                <?php
                // Fetch approved events from the database
                $queryApprovedEvents = "SELECT * FROM events WHERE status = 'accepted' ORDER BY date_requested ASC LIMIT 5";
                $stmtApprovedEvents = $pdo->query($queryApprovedEvents);
                $first = true;
                $slideIndex = 0;
                while ($event = $stmtApprovedEvents->fetch(PDO::FETCH_ASSOC)) {
                    echo '<li data-target="#approvedEventsCarousel" data-slide-to="' . $slideIndex . '" class="' . ($first ? 'active' : '') . '"></li>';
                    $first = false;
                    $slideIndex++;
                }
                ?>
            </ol>
            <div class="carousel-inner">
                <?php
                // Resetting the statement cursor
                $stmtApprovedEvents->execute();
                $first = true;
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
            <a class="carousel-control-prev" href="#approvedEventsCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#approvedEventsCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
