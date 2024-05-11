<?php
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
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

            // Check if event_id is provided in the URL
            if(isset($_GET['event_id'])) {
                // Retrieve event details based on event_id
                $event_id = $_GET['event_id'];
                $queryEventDetails = "SELECT e.*, u.username AS requester_username FROM events e JOIN users u ON e.user_id = u.id WHERE e.id = :event_id";
                $stmtEventDetails = $pdo->prepare($queryEventDetails);
                $stmtEventDetails->execute(['event_id' => $event_id]);
                $eventDetails = $stmtEventDetails->fetch(PDO::FETCH_ASSOC);
        
                // Display event details
                if($eventDetails) {
                    echo '<div class="card">';
                    echo '<div class="card-header">';
                    echo '<h5 class="card-title">Event Details</h5>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    echo '<p class="card-text"><strong>User:</strong> ' . $eventDetails['requester_username'] . '</p>';
                    echo '<p class="card-text"><strong>Title:</strong> ' . $eventDetails['title'] . '</p>';
                    echo '<p class="card-text"><strong>Description:</strong> ' . $eventDetails['description'] . '</p>';
                    echo '<p class="card-text"><strong>Facility:</strong> ' . $eventDetails['facility'] . '</p>';
                    echo '<p class="card-text"><strong>Duration:</strong> ' . $eventDetails['duration'] . ' hrs</p>';
                    echo '<p class="card-text"><strong>Status:</strong> ' . $eventDetails['status'] . '</p>';
                    echo '<p class="card-text"><strong>Date Requested:</strong> ' . $eventDetails['date_requested'] . '</p>';
                    echo '<p class="card-text"><strong>Event Start:</strong> ' . $eventDetails['event_start'] . '</p>';
                    echo '<p class="card-text"><strong>Event End:</strong> ' . $eventDetails['event_end'] . '</p>';
                    echo '<p class="card-text"><strong>Likes:</strong> ' . $eventDetails['likes'] . '</p>';
                    echo '<p class="card-text"><strong>Dislikes:</strong> ' . $eventDetails['dislikes'] . '</p>';
                    
                    // Display like and dislike buttons only for logged-in users
                    if(isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        // Check if the user has voted for this event and what their vote type is
                        $queryCheckVote = "SELECT vote_type FROM event_votes WHERE user_id = :user_id AND event_id = :event_id";
                        $stmtCheckVote = $pdo->prepare($queryCheckVote);
                        $stmtCheckVote->execute(['user_id' => $user_id, 'event_id' => $event_id]);
                        $vote = $stmtCheckVote->fetch(PDO::FETCH_ASSOC);
                        $voteType = $vote ? $vote['vote_type'] : '';

                        // Set button classes based on vote type
                        $likeClass = $voteType === 'like' ? 'btn-liked' : '';
                        $dislikeClass = $voteType === 'dislike' ? 'btn-disliked' : '';

                        // Display like and dislike buttons
                        echo '<div class="btn-group" role="group">';
                        echo '<form method="post" style="display: inline-block; margin-right: 10px;">'; // Style added here
                        echo '<input type="hidden" name="event_id" value="' . $event_id . '">';
                        if ($voteType === 'like') {
                            echo '<button type="submit" name="unlike" class="btn btn-success ' . $likeClass . '" style="width: 100px;">Unlike</button>'; // Fixed width added here
                        } else {
                            echo '<button type="submit" name="like" class="btn btn-success ' . $likeClass . '" style="width: 100px;">Like</button>'; // Fixed width added here
                        }
                        echo '</form>';

                        echo '<form method="post" style="display: inline-block;">'; // Style added here
                        echo '<input type="hidden" name="event_id" value="' . $event_id . '">';
                        if ($voteType === 'dislike') {
                            echo '<button type="submit" name="undislike" class="btn btn-danger ' . $dislikeClass . '" style="width: 100px;">Undislike</button>'; // Fixed width added here
                        } else {
                            echo '<button type="submit" name="dislike" class="btn btn-danger ' . $dislikeClass . '" style="width: 100px;">Dislike</button>'; // Fixed width added here
                        }
                        echo '</form>';
                        echo '</div>';

                    }
                    
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p class="alert alert-danger">Event not found.</p>';
                }
            } else {
                echo '<p class="alert alert-danger">Event ID not provided.</p>';
            }
        
            // Handle like/dislike button actions
            if(isset($_POST['like']) || isset($_POST['unlike']) || isset($_POST['dislike']) || isset($_POST['undislike'])) {
                if(isset($_SESSION['user_id'])) {
                    // User is logged in
                    $user_id = $_SESSION['user_id'];

                    // Check if the user has already voted for this event
                    $queryCheckVote = "SELECT * FROM event_votes WHERE user_id = :user_id AND event_id = :event_id";
                    $stmtCheckVote = $pdo->prepare($queryCheckVote);
                    $stmtCheckVote->execute(['user_id' => $user_id, 'event_id' => $event_id]);
                    $existingVote = $stmtCheckVote->fetch(PDO::FETCH_ASSOC);
            
                    if(!$existingVote) {
                        // User has not voted yet, proceed to update like/dislike count
                        if(isset($_POST['like'])) {
                            // Increment likes count
                            $queryUpdateLikes = "UPDATE events SET likes = likes + 1 WHERE id = :event_id";
                            $stmtUpdateLikes = $pdo->prepare($queryUpdateLikes);
                            $stmtUpdateLikes->execute(['event_id' => $event_id]);
                            $voteType = 'like';
                        } elseif(isset($_POST['dislike'])) {
                            // Increment dislikes count
                            $queryUpdateDislikes = "UPDATE events SET dislikes = dislikes + 1 WHERE id = :event_id";
                            $stmtUpdateDislikes = $pdo->prepare($queryUpdateDislikes);
                            $stmtUpdateDislikes->execute(['event_id' => $event_id]);
                            $voteType = 'dislike';
                        }
            
                        // Record user's vote
                        $queryRecordVote = "INSERT INTO event_votes (user_id, event_id, vote_type) VALUES (:user_id, :event_id, :vote_type)";
                        $stmtRecordVote = $pdo->prepare($queryRecordVote);
                        $stmtRecordVote->execute(['user_id' => $user_id, 'event_id' => $event_id, 'vote_type' => $voteType]);
                    } else {
                        // User has already voted for this event, toggle the vote
                        $voteType = $existingVote['vote_type'];

                        if(isset($_POST['like']) && $voteType === 'dislike') {
                            // Toggle dislike to like
                            $queryUpdateLikes = "UPDATE events SET likes = likes + 1, dislikes = dislikes - 1 WHERE id = :event_id";
                            $stmtUpdateLikes = $pdo->prepare($queryUpdateLikes);
                            $stmtUpdateLikes->execute(['event_id' => $event_id]);
                            $voteType = 'like';
                        } elseif(isset($_POST['unlike']) && $voteType === 'like') {
                            // Toggle like to unlike
                            $queryDeleteVote = "DELETE FROM event_votes WHERE user_id = :user_id AND event_id = :event_id";
                            $stmtDeleteVote = $pdo->prepare($queryDeleteVote);
                            $stmtDeleteVote->execute(['user_id' => $user_id, 'event_id' => $event_id]);
                            $queryUpdateLikes = "UPDATE events SET likes = likes - 1 WHERE id = :event_id";
                            $stmtUpdateLikes = $pdo->prepare($queryUpdateLikes);
                            $stmtUpdateLikes->execute(['event_id' => $event_id]);
                            $voteType = '';
                        } elseif(isset($_POST['dislike']) && $voteType === 'like') {
                            // Toggle like to dislike
                            $queryUpdateDislikes = "UPDATE events SET dislikes = dislikes + 1, likes = likes - 1 WHERE id = :event_id";
                            $stmtUpdateDislikes = $pdo->prepare($queryUpdateDislikes);
                            $stmtUpdateDislikes->execute(['event_id' => $event_id]);
                            $voteType = 'dislike';
                        } elseif(isset($_POST['undislike']) && $voteType === 'dislike') {
                            // Toggle dislike to undislike
                            $queryDeleteVote = "DELETE FROM event_votes WHERE user_id = :user_id AND event_id = :event_id";
                            $stmtDeleteVote = $pdo->prepare($queryDeleteVote);
                            $stmtDeleteVote->execute(['user_id' => $user_id, 'event_id' => $event_id]);
                            $queryUpdateDislikes = "UPDATE events SET dislikes = dislikes - 1 WHERE id = :event_id";
                            $stmtUpdateDislikes = $pdo->prepare($queryUpdateDislikes);
                            $stmtUpdateDislikes->execute(['event_id' => $event_id]);
                            $voteType = '';
                        }

                        // Update user's vote type
                        $queryUpdateVote = "UPDATE event_votes SET vote_type = :vote_type WHERE user_id = :user_id AND event_id = :event_id";
                        $stmtUpdateVote = $pdo->prepare($queryUpdateVote);
                        $stmtUpdateVote->execute(['user_id' => $user_id, 'event_id' => $event_id, 'vote_type' => $voteType]);
                    }
                    
                    // Refresh the page to reflect updated like/dislike counts
                    header("Refresh:0");
                } else {
                    // User is not logged in, display a message or redirect to login page
                    echo '<p class="alert alert-warning">Please log in to vote for this event.</p>';
                }
            }
        } catch(PDOException $e) {
            echo '<p class="alert alert-danger">Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    <!-- JS.PHP -->
    <?php
    require_once '../PARTS/js.php';
    ?>
</body>
</html>
