<?php
ob_start();
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

// Function to check if the user has exceeded the comment limit per hour
function hasExceededCommentLimit($pdo, $user_id, $comment_limit, $hour_limit) {
    try {
        // Calculate the timestamp for one hour ago
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        // Count the number of comments by the user within the last hour
        $query = "SELECT COUNT(*) AS comment_count FROM comments WHERE user_id = :user_id AND date_commented >= :one_hour_ago";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['user_id' => $user_id, 'one_hour_ago' => $one_hour_ago]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if the comment count exceeds the limit
        return $result['comment_count'] >= $comment_limit;
    } catch(PDOException $e) {
        // Handle database error
        return false;
    }
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
            // Existing code to display event details

            // Comment form
            if(isset($_SESSION['user_id'])) {
                // User is logged in
                $user_id = $_SESSION['user_id'];
                $comment_limit = 5; // Maximum number of comments per hour
                $hour_limit = 1; // Hour limit
            } else {
                // User is not logged in
                echo '<p class="alert alert-warning">Please log in to post a comment.</p>';
            }

            // Existing code to display existing comments
        } else {
            echo '<p class="alert alert-danger">Event not found.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">Event ID not provided.</p>';
    }

    // Existing code for handling like/dislike button actions

} catch(PDOException $e) {
    echo '<p class="alert alert-danger">Error: ' . $e->getMessage() . '</p>';
}
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
        <h5>
            <a href="../index.php" class="btn btn-primary">
                <img src="../SVG/house-fill.svg" alt="" class="me-2" width="16" height="16">Dashboard</a>
        </h5>
    </div>
    <div class="container mt-5">
        <?php

        try {
            // Connect to MySQL database using PDO
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username22, $password);
            
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
                    ob_end_flush();
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

<!-- Display event details (existing code) -->

<!-- Comment Section -->
<div class="container mt-5">
    <h5>Comments</h5>
    <!-- Comment Form -->
    <?php if(isset($_SESSION['user_id'])): ?>
        <?php
        // Check if the user has exceeded the comment limit per hour
        if(!hasExceededCommentLimit($pdo, $user_id, $comment_limit, $hour_limit)) {
        ?>
            <form method="post">
                <div class="mb-3">
                    <label for="comment" class="form-label">Your Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                </div>
                <button type="submit" name="submit_comment" class="btn btn-primary">Post Comment</button>
            </form>
        <?php } else { ?>
            <p class="alert alert-warning">You have reached the maximum comment limit per hour.</p>
        <?php } ?>
    <?php endif; ?>
    <!-- End Comment Form -->

    <!-- Display Existing Comments -->
    <div class="mt-4">
        <?php
        // Handle comment submission
        if(isset($_POST['submit_comment'])) {
            if(isset($_SESSION['user_id'])) {
                try {
                    // Get the user ID and event ID
                    $user_id = $_SESSION['user_id'];
                    $comment = $_POST['comment'];

                    // Insert the comment into the database
                    $queryInsertComment = "INSERT INTO comments (event_id, user_id, comment) VALUES (:event_id, :user_id, :comment)";
                    $stmtInsertComment = $pdo->prepare($queryInsertComment);
                    $stmtInsertComment->execute(['event_id' => $event_id, 'user_id' => $user_id, 'comment' => $comment]);

                    // Redirect to prevent form resubmission
                    header("Location: {$_SERVER['REQUEST_URI']}");
                    exit();
                } catch(PDOException $e) {
                    echo '<p class="alert alert-danger">Error: ' . $e->getMessage() . '</p>';
                }
            } else {
                echo '<p class="alert alert-warning">Please log in to post a comment.</p>';
            }
        }

        // Edit comment form
        if (isset($_POST['edit_comment'])) {
            $edit_comment_id = $_POST['comment_id'];
            $queryGetComment = "SELECT * FROM comments WHERE id = :comment_id";
            $stmtGetComment = $pdo->prepare($queryGetComment);
            $stmtGetComment->execute(['comment_id' => $edit_comment_id]);
            $edit_comment = $stmtGetComment->fetch(PDO::FETCH_ASSOC);
            if ($edit_comment) {
                // Display edit form
                echo '<form id="edit_comment_form" method="post">';
                echo '<div class="mb-3">';
                echo '<label for="edited_comment" class="form-label">Edit Your Comment</label>';
                echo '<textarea class="form-control" id="edited_comment" name="edited_comment" rows="3" required>' . $edit_comment['comment'] . '</textarea>';
                echo '</div>';
                echo '<div class="mb-3">';
                echo '<button type="submit" name="submit_edit_comment" class="btn btn-primary me-3">Submit</button>';
                echo '<button type="button" class="btn btn-secondary" id="cancel_edit">Cancel</button>';
                echo '<input type="hidden" name="edit_comment_id" value="' . $edit_comment_id . '">';
                echo '</div>';
                echo '</form>';
            } else {
                echo '<p class="alert alert-danger">Comment not found.</p>';
            }
        }
        
        // JavaScript to hide the edit comment form when cancel is clicked
        echo '<script>
        document.getElementById("cancel_edit").addEventListener("click", function() {
            document.getElementById("edit_comment_form").style.display = "none";
        });
        </script>';


// Handle comment edit submission
if (isset($_POST['submit_edit_comment'])) {
    $edit_comment_id = $_POST['edit_comment_id'];
    $edited_comment = $_POST['edited_comment'];
    try {
        $queryUpdateComment = "UPDATE comments SET comment = :edited_comment WHERE id = :comment_id";
        $stmtUpdateComment = $pdo->prepare($queryUpdateComment);
        $stmtUpdateComment->execute(['edited_comment' => $edited_comment, 'comment_id' => $edit_comment_id]);
        // Redirect to prevent form resubmission
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit();
    } catch (PDOException $e) {
        echo '<p class="alert alert-danger">Error updating comment: ' . $e->getMessage() . '</p>';
    }
}

        // Handle comment deletion
        if(isset($_POST['delete_comment'])) {
            if(isset($_SESSION['user_id'])) {
                // User is logged in
                $user_id = $_SESSION['user_id'];
                $comment_id = $_POST['comment_id'];

                try {
                    // Check if the user owns the comment
                    $queryCheckOwnership = "SELECT * FROM comments WHERE id = :comment_id AND user_id = :user_id";
                    $stmtCheckOwnership = $pdo->prepare($queryCheckOwnership);
                    $stmtCheckOwnership->execute(['comment_id' => $comment_id, 'user_id' => $user_id]);
                    $comment = $stmtCheckOwnership->fetch(PDO::FETCH_ASSOC);

                    if($comment) {
                        // User owns the comment, proceed with deletion of associated votes
                        $queryDeleteVotes = "DELETE FROM comment_votes WHERE comment_id = :comment_id";
                        $stmtDeleteVotes = $pdo->prepare($queryDeleteVotes);
                        $stmtDeleteVotes->execute(['comment_id' => $comment_id]);

                        // Then delete the comment itself
                        $queryDeleteComment = "DELETE FROM comments WHERE id = :comment_id";
                        $stmtDeleteComment = $pdo->prepare($queryDeleteComment);
                        $stmtDeleteComment->execute(['comment_id' => $comment_id]);

                        // Redirect to prevent form resubmission
                        header("Location: {$_SERVER['REQUEST_URI']}");
                        exit();
                    } else {
                        // User does not own the comment
                        echo '<p class="alert alert-danger">You do not have permission to delete this comment.</p>';
                    }
                } catch(PDOException $e) {
                    echo '<p class="alert alert-danger">Error: ' . $e->getMessage() . '</p>';
                }
            } else {
                // User is not logged in
                echo '<p class="alert alert-warning">Please log in to delete this comment.</p>';
            }
        }


        // Handle like/dislike button actions for comments
        if(isset($_POST['like_comment']) || isset($_POST['dislike_comment'])) {
            if(isset($_SESSION['user_id'])) {
                // User is logged in
                $user_id = $_SESSION['user_id'];
                $comment_id = $_POST['comment_id'];

                // Determine the vote type based on the button clicked
                $voteType = isset($_POST['like_comment']) ? 'like' : 'dislike';

                try {
                    // Check if the user has already voted for this comment
                    $queryCheckVote = "SELECT * FROM comment_votes WHERE user_id = :user_id AND comment_id = :comment_id";
                    $stmtCheckVote = $pdo->prepare($queryCheckVote);
                    $stmtCheckVote->execute(['user_id' => $user_id, 'comment_id' => $comment_id]);
                    $existingVote = $stmtCheckVote->fetch(PDO::FETCH_ASSOC);

                    if(!$existingVote) {
                        // User has not voted yet, insert the new vote
                        $queryInsertVote = "INSERT INTO comment_votes (user_id, event_id, comment_id, vote_type) VALUES (:user_id, :event_id, :comment_id, :vote_type)";
                        $stmtInsertVote = $pdo->prepare($queryInsertVote);
                        $stmtInsertVote->execute(['user_id' => $user_id, 'event_id' => $event_id, 'comment_id' => $comment_id, 'vote_type' => $voteType]);
                    } else {
                        // User has already voted, check if the vote type is the same
                        $existingVoteType = $existingVote['vote_type'];
                        if($existingVoteType === $voteType) {
                            // User clicked on the same vote type button, remove the vote
                            $queryDeleteVote = "DELETE FROM comment_votes WHERE user_id = :user_id AND comment_id = :comment_id";
                            $stmtDeleteVote = $pdo->prepare($queryDeleteVote);
                            $stmtDeleteVote->execute(['user_id' => $user_id, 'comment_id' => $comment_id]);
                        } else {
                            // User clicked on a different vote type button, update the existing vote
                            $queryUpdateVote = "UPDATE comment_votes SET vote_type = :vote_type WHERE user_id = :user_id AND comment_id = :comment_id";
                            $stmtUpdateVote = $pdo->prepare($queryUpdateVote);
                            $stmtUpdateVote->execute(['user_id' => $user_id, 'comment_id' => $comment_id, 'vote_type' => $voteType]);
                        }
                    }
                    // Redirect to prevent form resubmission
                    header("Location: {$_SERVER['REQUEST_URI']}");
                    exit();
                } catch(PDOException $e) {
                    echo '<p class="alert alert-danger">Error: ' . $e->getMessage() . '</p>';
                }
            } else {
                // User is not logged in
                echo '<p class="alert alert-warning">Please log in to vote for this comment.</p>';
            }
        }

        // Retrieve comments for this event ordered by date in descending order
        $queryComments = "SELECT c.*, u.username AS commenter_username, 
        (SELECT COUNT(*) FROM comment_votes WHERE comment_id = c.id AND vote_type = 'like') AS likes,
        (SELECT COUNT(*) FROM comment_votes WHERE comment_id = c.id AND vote_type = 'dislike') AS dislikes
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.event_id = :event_id
        ORDER BY c.date_commented DESC"; // Order by date in descending order
        $stmtComments = $pdo->prepare($queryComments);
        $stmtComments->execute(['event_id' => $event_id]);
        $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

        // Display comments
        foreach ($comments as $comment) {
            echo '<div class="card mb-3">';
            echo '<div class="card-body">';
            echo '<h6 class="card-subtitle mb-2 text-muted">Commented by: ' . $comment['commenter_username'] . ' on ' . $comment['date_commented'] . '</h6>';
            echo '<p class="card-text">' . $comment['comment'] . '</p>';
            echo '<div class="d-flex justify-content-between align-items-center">';

            // Like button
            echo '<form method="post" style="display: inline-block;">';
            echo '<input type="hidden" name="comment_id" value="' . $comment['id'] . '">';
            echo '<button type="submit" name="like_comment" class="btn btn-outline-primary me-2">Like (' . $comment['likes'] . ')</button>';
            echo '</form>';

            // Dislike button
            echo '<form method="post" style="display: inline-block;">';
            echo '<input type="hidden" name="comment_id" value="' . $comment['id'] . '">';
            echo '<button type="submit" name="dislike_comment" class="btn btn-outline-danger">Dislike (' . $comment['dislikes'] . ')</button>';
            echo '</form>';

            // Edit and delete buttons
            if(isset($_SESSION['user_id']) && $comment['user_id'] === $_SESSION['user_id']) {
                echo '<div class="ms-auto">';
                echo '<form method="post" style="display: inline-block;">';
                echo '<button type="submit" name="delete_comment" class="btn btn-outline-danger me-2">Delete</button>';
                echo '<input type="hidden" name="comment_id" value="' . $comment['id'] . '">';
                echo '</form>';
                echo '<form method="post" style="display: inline-block;">';
                echo '<button type="submit" name="edit_comment" class="btn btn-outline-secondary">Edit</button>';
                echo '<input type="hidden" name="comment_id" value="' . $comment['id'] . '">';
                echo '</form>';
                echo '</div>';
            }

            echo '</div>';

            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>
<!-- End Comment Section -->





    <!-- JS.PHP -->
    <?php
    require_once '../PARTS/js.php';
    ?>
</body>
</html>
