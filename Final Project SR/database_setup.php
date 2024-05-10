<?php
session_start();

// Database connection settings
$host = 'localhost';
$dbname = 'event_management_system';
$username = 'root';
$password = '';

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO("mysql:host=$host", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the database already exists
    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$dbname]);
    $databaseExists = $stmt->fetch();

    // If the database doesn't exist, create it
    if (!$databaseExists) {
        $createDatabaseQuery = "CREATE DATABASE $dbname";
        $pdo->exec($createDatabaseQuery);
        echo "Database created successfully.<br>";
    } else {
        echo "Database already exists.<br>";
    }

    // Switch to the created database
    $pdo->exec("USE $dbname");

    // Create users table if it doesn't exist
    $createUserTableQuery = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') NOT NULL,
        can_request_event BOOLEAN DEFAULT TRUE,
        can_review_request BOOLEAN DEFAULT FALSE,
        can_delete_user BOOLEAN DEFAULT FALSE,
        date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createUserTableQuery);

    // Alter users table to add missing columns or modify structure
    $alterUserTableQuery = "ALTER TABLE users
        ADD COLUMN IF NOT EXISTS can_request_event BOOLEAN DEFAULT TRUE,
        ADD COLUMN IF NOT EXISTS can_review_request BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS can_delete_user BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $pdo->exec($alterUserTableQuery);

    // Create events table if it doesn't exist
    $createEventTableQuery = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        facility VARCHAR(100) NOT NULL,
        duration INT NOT NULL,
        status ENUM('pending', 'accepted', 'declined') NOT NULL,
        date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($createEventTableQuery);

    // Alter events table to add missing columns or modify structure
    $alterEventTableQuery = "ALTER TABLE events
        ADD COLUMN IF NOT EXISTS date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $pdo->exec($alterEventTableQuery);

    // Create comments table if it doesn't exist
    $createCommentTableQuery = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT,
        likes INT DEFAULT 0,
        dislikes INT DEFAULT 0,
        date_commented TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($createCommentTableQuery);

    // Alter comments table to add missing columns or modify structure
    $alterCommentTableQuery = "ALTER TABLE comments
        ADD COLUMN IF NOT EXISTS date_commented TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    $pdo->exec($alterCommentTableQuery);

    echo "Tables created successfully.<br>";

    // Check if admin exists, if not create default admin
    $queryAdmin = "SELECT * FROM users WHERE role = 'admin' LIMIT 1";
    $stmtAdmin = $pdo->prepare($queryAdmin);
    $stmtAdmin->execute();
    $adminExists = $stmtAdmin->fetch();

    if (!$adminExists) {
        // Create a default admin account if none exists
        $hashedPassword = password_hash("admin_password", PASSWORD_DEFAULT); // Change "admin_password" to desired default admin password
        $createAdminQuery = "INSERT INTO users (username, password, role, can_request_event, can_review_request, can_delete_user) VALUES ('admin', '$hashedPassword', 'admin', TRUE, TRUE, TRUE)";
        $pdo->exec($createAdminQuery);
        echo "Default admin account created successfully.<br>";
    }

    // If everything is done, redirect to index.php
    echo '<a href="index.php"><button>Go to Index</button></a>';

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is an admin
function isAdmin() {
    if (isLoggedIn()) {
        global $pdo;
        $userId = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        $admin = $stmt->fetch();
        return ($admin) ? true : false;
    }
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header("Location: EMS/login.php"); // Redirect to login page
    exit();
}
?>
