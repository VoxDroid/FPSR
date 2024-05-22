<?php
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

// Redirect to index.php if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Redirect to index.php if user is not an admin
if ($_SESSION['role'] !== 'admin') {
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

// Fetch all users
$queryUsers = "SELECT * FROM users";
$stmtUsers = $pdo->prepare($queryUsers);
$stmtUsers->execute();
// Process form submission
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>

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

    // Check for success message
    if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success'>{$_SESSION['success_message']}</div>";
        unset($_SESSION['success_message']); // Clear message after displaying
    }

    // Check for error message
    if (isset($_SESSION['error_message'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error_message']}</div>";
        unset($_SESSION['error_message']); // Clear message after displaying
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
        $userId = $_POST['user_id'];
        
        if ($userId != 1) {
            try {
                // Begin a transaction
                $pdo->beginTransaction();
        
                // Delete associated comments made by the user
                $deleteCommentsQuery = "DELETE FROM comments WHERE user_id = :id";
                $stmtComments = $pdo->prepare($deleteCommentsQuery);
                $stmtComments->bindParam(':id', $userId);
                $stmtComments->execute();
        
                // Delete associated records from comment_votes table
                $deleteCommentVotesQuery = "DELETE FROM comment_votes WHERE user_id = :id";
                $stmtCommentVotes = $pdo->prepare($deleteCommentVotesQuery);
                $stmtCommentVotes->bindParam(':id', $userId);
                $stmtCommentVotes->execute();
        
                // Delete associated records from event_votes table
                $deleteEventVotesQuery = "DELETE FROM event_votes WHERE user_id = :id";
                $stmtEventVotes = $pdo->prepare($deleteEventVotesQuery);
                $stmtEventVotes->bindParam(':id', $userId);
                $stmtEventVotes->execute();
        
                // Delete associated records from events table
                $deleteEventsQuery = "DELETE FROM events WHERE user_id = :id";
                $stmtEvents = $pdo->prepare($deleteEventsQuery);
                $stmtEvents->bindParam(':id', $userId);
                $stmtEvents->execute();
        
                // Finally, delete the user record
                $deleteUserQuery = "DELETE FROM users WHERE id = :id";
                $stmtUser = $pdo->prepare($deleteUserQuery);
                $stmtUser->bindParam(':id', $userId);
                $stmtUser->execute();
                // Commit the transaction
                $pdo->commit();
                $_SESSION['success_message'] = "User deleted successfully.";
                // Deletion successful, redirect to admin page
                header("Location: admin_page_settings.php");
                exit();
            } catch(PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                header("Location: admin_page_settings.php");
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>Cannot delete the default admin account.</div>";
        }
    }
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $isActive = $_POST['is_active'];
    
        // Check if the user selected to upload a new profile picture
        $profileAction = $_POST['profile_action'];
        if ($profileAction === 'Upload' && isset($_FILES['profile_picture_upload']) && $_FILES['profile_picture_upload']['error'] === UPLOAD_ERR_OK) {
            // Handle profile picture upload
            $uploadDir = '../UPLOADS/img/USERS/'; // Change this to your desired upload directory
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadFile = $uploadDir . basename($_FILES['profile_picture_upload']['name']);
    
            // Check if the file is an image
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($imageFileType, $allowedExtensions)) {
                echo "Only JPG, JPEG, and PNG files are allowed.";
                exit();
            }
    
            // Move the uploaded file to the upload directory
            if (move_uploaded_file($_FILES['profile_picture_upload']['tmp_name'], $uploadFile)) {
                // Resize and crop the image to 100x100 square
                $image = imagecreatefromstring(file_get_contents($uploadFile));
                $width = imagesx($image);
                $height = imagesy($image);
                $size = min($width, $height);
                $croppedImage = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
                $resizedImage = imagescale($croppedImage, 200, 200);
                
                // Overwrite the original uploaded file with the resized image
                imagepng($resizedImage, $uploadFile);
                imagedestroy($image);
                imagedestroy($croppedImage);
                imagedestroy($resizedImage);
                
                // Update profile picture path in the database
                $profilePicture = $uploadFile;
                $updateProfilePictureQuery = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
                $stmtProfilePicture = $pdo->prepare($updateProfilePictureQuery);
                $stmtProfilePicture->bindParam(':profile_picture', $profilePicture);
                $stmtProfilePicture->bindParam(':id', $userId);
                $stmtProfilePicture->execute();
            } else {
                echo "Error uploading file.";
                exit();
            }
        } elseif ($profileAction === 'Default') {
            // Fetch the user's data from the database
            $queryUser = "SELECT gender FROM users WHERE id = :id";
            $stmtUser = $pdo->prepare($queryUser);
            $stmtUser->bindParam(':id', $userId);
            $stmtUser->execute();
            $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
            // Check if user data is fetched successfully
            if ($userData) {
                // Get the user's gender
                $gender = strtolower($userData['gender']);
        
                // Set the default profile picture based on gender
                if ($gender === 'male') {
                    $defaultProfilePicture = '../ASSETS/IMG/DPFP/male.png';
                } elseif ($gender === 'female') {
                    $defaultProfilePicture = '../ASSETS/IMG/DPFP/female.png';
                } else {
                    // Default to male profile picture if gender is not specified or invalid
                    $defaultProfilePicture = '../ASSETS/IMG/DPFP/male.png';
                }
        
                // Update profile picture path in the database
                $updateProfilePictureQuery = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
                $stmtUpdateProfilePicture = $pdo->prepare($updateProfilePictureQuery);
                $stmtUpdateProfilePicture->bindParam(':profile_picture', $defaultProfilePicture);
                $stmtUpdateProfilePicture->bindParam(':id', $userId);
                $stmtUpdateProfilePicture->execute();
            } else {
                // Handle the case where user data is not found
                echo "Error: User data not found.";
                exit();
            }
        }
        
        
    
    
        // Update user details in the database
        $updateQuery = "UPDATE users SET username = :username, email = :email, role = :role, is_active = :is_active WHERE id = :id";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->bindParam(':id', $userId);
        
        if ($stmt->execute()) {
            header("Location: admin_page_settings.php");
            exit();
        } else {
            echo "Error updating user.";
        }
    
        // Handle profile picture removal
    if (isset($_POST['remove_picture'])) {
        // Set default profile picture based on gender
        $defaultProfilePicture = '';
        if ($user['gender'] === 'Male') {
            $defaultProfilePicture = '../ASSETS/IMG/DPFP/male.png';
        } elseif ($user['gender'] === 'female') {
            $defaultProfilePicture = '../ASSETS/IMG/DPFP/female.png';
        }
        
        // Update profile picture path in the database
        $updateProfilePictureQuery = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
        $stmtUpdateProfilePicture = $pdo->prepare($updateProfilePictureQuery);
        $stmtUpdateProfilePicture->bindParam(':profile_picture', $defaultProfilePicture);
        $stmtUpdateProfilePicture->bindParam(':id', $userId);
        $stmtUpdateProfilePicture->execute();
    }
    
    
    }
    
    
    ?>
    <h2>Manage Users</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $stmtUsers->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewUserModal<?php echo $user['id']; ?>">View</button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#manageUserModal<?php echo $user['id']; ?>">Manage</button>
                </td>
            </tr>

            <!-- View User Modal -->
            <div class="modal fade" id="viewUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="viewUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewUserModalLabel<?php echo $user['id']; ?>">View User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($user['profile_picture'])) { ?>
                                <p><strong>Profile Picture:</strong></p>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-fluid">
                            <?php } ?>
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($user['id']);?></p>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($user['is_active'] ? 'Active' : 'Suspended'); ?></p>
                            <p><strong>Date Created:</strong> <?php echo htmlspecialchars($user['date_created']); ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Manage User Modal -->
            <div class="modal fade" id="manageUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="manageUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="manageUserModalLabel<?php echo $user['id']; ?>">Manage User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <div class="input-group">
                                        <?php 
                                            if (!empty($user['profile_picture'])) {
                                                $profilePictureFileName = basename($user['profile_picture']);
                                                echo '<input type="text" class="form-control mb-2" value="'.$profilePictureFileName.'" readonly>';
                                            }
                                        ?>
                                    </div>
                                    <div class="input-group">
                                    <select class="form-control" id="profile_action" name="profile_action" required>
                                    <option value="Upload" <?php if (!isset($_POST['profile_action']) || (isset($_POST['profile_action']) && $_POST['profile_action'] == 'Upload')) echo 'selected'; ?>>Upload</option>
                                    <option value="Default" <?php if (isset($_POST['profile_action']) && $_POST['profile_action'] == 'Default') echo 'selected'; ?>>Default</option>
                                </select>
                                <?php
                                    // Initially show the upload button and hide the default button
                                    $uploadButtonStyle = '';
                                    $defaultButtonStyle = 'display: none;';

                                    if (isset($_POST['profile_action']) && $_POST['profile_action'] == 'Default') {
                                        $uploadButtonStyle = 'display: none;';
                                        $defaultButtonStyle = '';
                                    }

                                    echo '<input type="file" class="form-control btn btn-primary" id="profile_picture_upload" name="profile_picture_upload" accept=".jpg, .jpeg, .png" style="' . $uploadButtonStyle . '">';
                                    // Output the default button with inline style based on selection
                                    $defaultProfilePicture = ($user['gender'] == 'male') ? '../ASSETS/IMG/DPFP/male.png' : '../ASSETS/IMG/DPFP/female.png';
                                    echo '<input type="text" class="form-control" id="profile_picture_default" name="profile_picture_default" value="'.$defaultProfilePicture.'" readonly style="' . $defaultButtonStyle . '">';
                                ?>

                                    </div>
                                </div>

<script>
    document.getElementById('profile_action').addEventListener('change', function() {
        var uploadButton = document.getElementById('profile_picture_upload');
        var defaultButton = document.getElementById('profile_picture_default');

        if (this.value === 'Upload') {
            uploadButton.style.display = 'block';
            defaultButton.style.display = 'none';
        } else if (this.value === 'Default') {
            uploadButton.style.display = 'none';
            defaultButton.style.display = 'block';
        }
    });
</script>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">                                    
                        </div>
                        <?php if ($user['id'] != 1): ?>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active" required>
                                <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                        <?php else: ?>
                        <!-- Disable the role and status fields for the admin user -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" disabled>
                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active" disabled>
                                <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary" name="update_account">Save Changes</button>
                            <?php if ($user['id'] != 1): ?>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal<?php echo $user['id']; ?>">Delete Account</button>
                            <?php endif; ?>
                        </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel<?php echo $user['id']; ?>">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Display username for confirmation -->
                <p>Are you sure you want to delete the account of <?php echo htmlspecialchars($user['username']); ?>?</p>
            </div>
            <div class="modal-footer">
                <!-- Form submission for account deletion -->
                <form method="post">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" class="btn btn-danger" name="delete_account">Delete Account</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

            
            <?php } ?>
        </tbody>
    </table>
</div>
</main>
<!-- End Main Content -->

<!-- JS.PHP -->
<?php require_once '../PARTS/js.php'; ?>

</body>
</html>

