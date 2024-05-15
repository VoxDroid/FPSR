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
        gender ENUM('male', 'female') NOT NULL,
        email VARCHAR(100),
        profile_picture VARCHAR(255),
        role ENUM('user', 'admin') NOT NULL,
        can_request_event BOOLEAN DEFAULT TRUE,
        can_review_request BOOLEAN DEFAULT FALSE,
        can_delete_user BOOLEAN DEFAULT FALSE,
        date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE
    )";
    $pdo->exec($createUserTableQuery);

    // Create events table if it doesn't exist
    $createEventTableQuery = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        facility VARCHAR(100) NOT NULL,
        duration INT NOT NULL,
        status ENUM('pending', 'active', 'denied', 'ongoing', 'completed') NOT NULL,
        date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        event_start DATETIME,
        event_end DATETIME,
        likes INT DEFAULT 0,
        dislikes INT DEFAULT 0,
        remarks VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($createEventTableQuery);

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

    // Create comment_votes table if it doesn't exist
    $createCommentVotesTableQuery = "CREATE TABLE IF NOT EXISTS comment_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        comment_id INT NOT NULL,
        vote_type ENUM('like', 'dislike') NOT NULL,
        UNIQUE KEY user_comment_unique (user_id, event_id, comment_id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (comment_id) REFERENCES comments(id)
    )";
    $pdo->exec($createCommentVotesTableQuery);

    // Create event_votes table if it doesn't exist
    $createEventVotesTableQuery = "CREATE TABLE IF NOT EXISTS event_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        vote_type ENUM('like', 'dislike') NOT NULL,
        UNIQUE KEY user_event_unique (user_id, event_id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (event_id) REFERENCES events(id)
    )";
    $pdo->exec($createEventVotesTableQuery);

    echo "Tables created successfully.<br>";

    // Check if admin exists, if not create default admin
    $queryAdmin = "SELECT * FROM users WHERE role = 'admin' LIMIT 1";
    $stmtAdmin = $pdo->query($queryAdmin);
    $adminExists = $stmtAdmin->fetch();

    if (!$adminExists) {
        // Create a default admin account if none exists
        $hashedAdminPassword = password_hash("admin_password", PASSWORD_DEFAULT); // Change "admin_password" to desired default admin password
        $createAdminQuery = "INSERT INTO users (username, password, role, can_request_event, can_review_request, can_delete_user) VALUES ('admin', '$hashedAdminPassword', 'admin', TRUE, TRUE, TRUE)";
        $pdo->exec($createAdminQuery);
        echo "Default admin account created successfully.<br>";
    }

    // Check if default user exists, if not create default user
    $queryDefaultUser = "SELECT * FROM users WHERE username = 'user' LIMIT 1";
    $stmtDefaultUser = $pdo->query($queryDefaultUser);
    $userExists = $stmtDefaultUser->fetch();

    if (!$userExists) {
        // Create a default user account if none exists
        $hashedUserPassword = password_hash("user_password", PASSWORD_DEFAULT); // Change "user_password" to desired default user password
        $createUserQuery = "INSERT INTO users (username, password, role) VALUES ('user', '$hashedUserPassword', 'user')";
        $pdo->exec($createUserQuery);
        echo "Default user account created successfully.<br>";
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
