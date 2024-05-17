<?php
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

// Redirect to index.php if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

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

// Fetch user details
$queryUser = "SELECT * FROM users WHERE id = :id";
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->bindParam(':id', $userId, PDO::PARAM_INT);
$stmtUser->execute();
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_account'])) {
    $newUsername = !empty($_POST['username']) && strlen($_POST['username']) >= 3 ? $_POST['username'] : $user['username'];
    $newPassword = !empty($_POST['password']) && strlen($_POST['password']) >= 8 ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $newGender = !empty($_POST['gender']) ? $_POST['gender'] : $user['gender'];
    $newEmail = !empty($_POST['email']) ? $_POST['email'] : $user['email'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../UPLOADS/img/USERS/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadFile = $uploadDir . basename($_FILES['profile_picture']['name']);
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array($imageFileType, $allowedExtensions)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
                // Resize and crop the image to 200x200 square
                $image = imagecreatefromstring(file_get_contents($uploadFile));
                $width = imagesx($image);
                $height = imagesy($image);
                $size = min($width, $height);
                $croppedImage = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
                $resizedImage = imagescale($croppedImage, 200, 200);

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
            }
        }
    } elseif (isset($_POST['set_default_picture'])) {
        // Set default profile picture based on gender
        $defaultProfilePicture = ($user['gender'] === 'female') ? '../ASSETS/IMG/DPFP/female.png' : '../ASSETS/IMG/DPFP/male.png';

        // Update profile picture path in the database
        $updateProfilePictureQuery = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
        $stmtProfilePicture = $pdo->prepare($updateProfilePictureQuery);
        $stmtProfilePicture->bindParam(':profile_picture', $defaultProfilePicture);
        $stmtProfilePicture->bindParam(':id', $userId);
        $stmtProfilePicture->execute();
    } elseif (isset($_POST['remove_picture'])) {
        // Set default profile picture based on gender
        $defaultProfilePicture = ($user['gender'] === 'female') ? '../ASSETS/IMG/DPFP/female.png' : '../ASSETS/IMG/DPFP/male.png';

        // Update profile picture path in the database
        $updateProfilePictureQuery = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
        $stmtProfilePicture = $pdo->prepare($updateProfilePictureQuery);
        $stmtProfilePicture->bindParam(':profile_picture', $defaultProfilePicture);
        $stmtProfilePicture->bindParam(':id', $userId);
        $stmtProfilePicture->execute();
    }

    // Update user details in the database
    $updateQuery = "UPDATE users SET username = :username, password = :password, gender = :gender, email = :email WHERE id = :id";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':username', $newUsername);
    $stmt->bindParam(':password', $newPassword);
    $stmt->bindParam(':gender', $newGender);
    $stmt->bindParam(':email', $newEmail);
    $stmt->bindParam(':id', $userId);

    if ($stmt->execute()) {
        // Refresh user data
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>

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
        <h2>My Profile</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3 text-center">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-thumbnail" style="width: 150px; height: 150px;">
                <?php else: ?>
                    <p>N/A</p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" minlength="3">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" minlength="8">
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-control" id="gender" name="gender">
                    <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remove_picture" name="remove_picture" onchange="handleRemovePicture()">
                <label class="form-check-label" for="remove_picture">Remove Profile Picture</label>
            </div>
            <button type="submit" class="btn btn-primary" name="update_account">Save Changes</button>
        </form>
    </div>
</main>
<!-- End Main Content -->

<!-- JS.PHP -->
<?php require_once '../PARTS/js.php'; ?>
<script>
    function handleRemovePicture() {
        var removeCheckbox = document.getElementById('remove_picture');
        var uploadInput = document.getElementById('profile_picture');

        if (removeCheckbox.checked) {
            // Disable the upload input and clear any selected files
            uploadInput.disabled = true;
            uploadInput.value = '';
        } else {
            // Enable the upload input
            uploadInput.disabled = false;
        }
    }
</script>


</body>
</html>
