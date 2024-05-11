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

function logout_EMS() {
    session_destroy();
    // Refresh the page after logout
    echo "<script>window.location.href = '../index.php';</script>";
    exit();
}

// Check if logout button is clicked
if (isset($_POST['logout'])) {
    logout();
}

if (isset($_POST['logout_EMS'])) {
    logout_EMS();
}
?>