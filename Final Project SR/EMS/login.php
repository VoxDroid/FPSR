<?php
session_start();

// Redirect user to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
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

        // Prepare and execute the SQL query to check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $_POST['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && password_verify($_POST['password'], $user['password'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect user to dashboard
            header("Location: ../index.php");
            exit();
        } else {
            // Invalid username or password
            $error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Management System</title>

    <!-- Bootstrap CSS -->
    <link href="../CSS/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../CSS/custom_style.css" rel="stylesheet">
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mb-4">Log In</h2>
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Log In</button>
                </form>
            </div>
        </div>
    </div>
</main>
<!-- End Main Content -->

<!-- Bootstrap JS -->
<script src="../JS/jquery.slim.min.js"></script>
<script src="../JS/popper.min.js"></script>
<script src="../JS/bootstrap.min.js"></script>

<!-- Custom JS -->
<script src="../JS/custom_script.js"></script>

</body>
</html>
