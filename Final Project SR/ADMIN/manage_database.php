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

// PHP Code For Backup
require '../PARTS/managedb_backup.php';

// PHP Code for Restoration
require '../PARTS/managedb_restore.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Database</title>

    <!-- CSS.PHP -->
    <?php require_once '../PARTS/CSS.php'; ?>
    <!-- Internal CSS -->
    <style>
        .admin-navigation {
            background-color: #161c27;
            display: flex;
            flex-wrap: wrap; /* Allow items to wrap on smaller screens */
            justify-content: center;
            padding: 10px 0;
        }

        .nav-button {
            color: #ffffff;
            text-decoration: none;
            padding: 15px;
            margin: 5px; /* Adjusted margin for better spacing */
            border-radius: 8px;
            transition: background-color 0.3s ease;
            display: inline-flex; /* Ensure buttons are in a row */
            align-items: center; /* Center content vertically */
        }

        .nav-button:hover {
            background-color: #273447;
        }

        .nav-icon {
            margin-right: 10px;
        }

        .active {
            background-color: #273447;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once '../PARTS/header.php'; ?>
    
    <!-- Navigation Buttons Section -->
    <div class="admin-navigation">
        <a class="nav-button" href="administrator.php"><i class="fas fa-tachometer-alt nav-icon"></i> Dashboard</a>
        <a class="nav-button" href="manage_users.php"><i class="fas fa-users nav-icon"></i> Manage Users</a>
        <a class="nav-button" href="manage_comments.php"><i class="fas fa-comments nav-icon"></i> Manage Comments</a>
        <a class="nav-button" href="manage_events.php"><i class="fas fa-calendar-alt nav-icon"></i> Manage Events</a>
        <a class="nav-button active" href="#"><i class="fas fa-database nav-icon"></i> Database Management</a>
    </div>
    <!-- End Navigation Buttons Section -->

    <!-- Main Content Section -->
    <div class="container py-5 flex-grow-1">
        <?php 
        if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success'>{$_SESSION['success_message']}</div>";
        unset($_SESSION['success_message']); // Clear message after displaying
        }
        if (isset($_SESSION['error_messages'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error_messages']}</div>";
        unset($_SESSION['error_messages']); // Clear message after displaying
        }
        ?>
        <h2 class="mb-4">Manage Database</h2>
        <hr style="border: none; height: 4px; background-color: #1c2331;">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Backup Database</h5>
                        <p class="card-text">Create a backup of the database for security purposes.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#backupModal">Backup Now</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Restore Database</h5>
                        <p class="card-text">Restore the database from a previous backup.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restoreModal">Restore Now</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Export Data (Coming Soon)</h5>
                        <p class="card-text">Export data from selected tables to a file.</p>
                        <a href="#" class="btn btn-primary">Export Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Import Data (Coming Soon)</h5>
                        <p class="card-text">Import data from a file into the database.</p>
                        <a href="#" class="btn btn-primary">Import Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Optimize Tables (Coming Soon)</h5>
                        <p class="card-text">Optimize all database tables for better performance.</p>
                        <a href="#" class="btn btn-primary">Optimize Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">View Database Structure (Coming Soon)</h5>
                        <p class="card-text">View the structure of the database tables.</p>
                        <a href="#" class="btn btn-primary">View Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content Section -->

    <!-- Backup Modal -->
    <div class="modal fade" id="backupModal" tabindex="-1" aria-labelledby="backupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="backupModalLabel">Confirm Database Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="backupDir" class="form-label">Backup Directory</label>
                            <input type="text" class="form-control" id="backupDir" name="backup_directory" value="../db_backups/" required>
                        </div>
                        <div class="mb-3">
                            <label for="backupFilename" class="form-label">Backup Filename</label>
                            <input type="text" class="form-control" id="backupFilename" name="backup_filename" value="backup_<?php echo date('Ymd_His'); ?>.sql" required>
                        </div>
                        <?php if (!empty($backupMessage)) echo $backupMessage; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="backup_database" class="btn btn-primary">Backup Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Backup Modal -->

    <!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">Confirm Database Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="backupFile" class="form-label">Backup File</label>
                        <input type="file" class="form-control" id="backupFile" name="backup_file" required>
                    </div>
                    <?php if (!empty($restoreMessage)) echo $restoreMessage; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="restore_database" class="btn btn-primary">Restore Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Restore Modal -->

    <!-- Footer -->
    <?php require_once '../PARTS/footer.php'; ?>

    <!-- JS.PHP -->
    <?php require_once '../PARTS/JS.php'; ?>
</body>
</html>